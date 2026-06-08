<?php
// admin_participation_reports.php
session_start();

// Authentication Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Participation Reports | MyFKClub Admin</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <link rel="stylesheet" href="../CSS/participationreport.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .chart-card canvas {
      display: block;
      width: 100% !important;
      height: 300px !important;
      max-height: 300px;
    }

    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: #ffffff;
      border: 1px solid rgba(17, 53, 111, 0.08);
      border-radius: 8px;
      padding: 20px 24px;
      box-shadow: 0 2px 8px rgba(15, 63, 141, 0.06);
    }

    .stat-label {
      color: #2f3f66;
      font-size: 0.9rem;
      font-weight: 500;
      margin-bottom: 12px;
    }

    .stat-value {
      color: #2f2f2f;
      font-size: 1.8rem;
      font-weight: 700;
    }

    .charts-row {
      display: grid;
      grid-template-columns: 1.6fr 1fr;
      gap: 18px;
      margin-bottom: 24px;
    }

    .chart-card {
      background: #ffffff;
      border: 1px solid rgba(17, 53, 111, 0.08);
      border-radius: 8px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(15, 63, 141, 0.06);
    }

    .chart-title {
      color: #2f3f66;
      font-size: 1.05rem;
      font-weight: 600;
      margin-bottom: 16px;
    }

    .table-wrapper tbody td {
      text-align: left;
      border-bottom: 1px solid rgba(17, 53, 111, 0.08);
      padding: 12px 22px;
    }

    .table-wrapper thead th {
      display: table-cell;
      padding: 12px 22px;
      color: #2f3f66;
      font-weight: 600;
      font-size: 0.9rem;
      text-align: left;
      background-color: #f8f9fa;
      border-bottom: 1px solid rgba(17, 53, 111, 0.12);
    }

    .empty-cell {
      text-align: center;
      color: #999999;
      font-style: italic;
      padding: 24px !important;
    }

    .recognition-gold {
      color: #ffc107;
      font-weight: 600;
    }

    .recognition-silver {
      color: #c0c0c0;
      font-weight: 600;
    }

    .recognition-bronze {
      color: #cd7f32;
      font-weight: 600;
    }

    @media (max-width: 1200px) {
      .stats-row {
        grid-template-columns: repeat(2, 1fr);
      }

      .charts-row {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 720px) {
      .stats-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>
      <nav class="sidebar-nav">
        <a href="admin_dashboard.php" class="sidebar-link">Home</a>
        <a href="admin_manage_users.php" class="sidebar-link">Manage Users</a>
        <a href="admin_student_clubs.php" class="sidebar-link">Student Clubs</a>
        <a href="admin_events.php" class="sidebar-link">Events</a>
        <a href="admin_participation_reports.php" class="sidebar-link active">Participation Reports</a>
      </nav>
    </aside>
    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Participation Reports</div>
        </div>
      </div>
      <div class="content-area">
 
        <section class="stats-row">
          <div class="stat-card">
            <div class="stat-label">Total Events</div>
            <div class="stat-value" id="stat-events">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Total Participation</div>
            <div class="stat-value" id="stat-participation">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Avg Attendance Rate</div>
            <div class="stat-value" id="stat-attendance">0%</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Outstanding Students</div>
            <div class="stat-value" id="stat-outstanding">0</div>
          </div>
        </section>

        <section class="charts-row">
          <div class="chart-card">
            <div class="chart-title">Monthly Participation Trend</div>
            <canvas id="monthlyTrendChart"></canvas>
          </div>
          <div class="chart-card">
            <div class="chart-title">Recognition Level Distribution</div>
            <canvas id="recognitionChart"></canvas>
          </div>
        </section>
        <section class="table-section">
          <div class="table-panel">
            <div class="table-heading">Attendance Rate per Event</div>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Events</th>
                    <th>Club</th>
                    <th>Registered</th>
                    <th>Present</th>
                    <th>Late</th>
                    <th>Absent</th>
                    <th>Rate</th>
                  </tr>
                </thead>
                <tbody id="event-attendance-body">
                  <tr><td colspan="7" class="empty-cell">Loading event data...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section class="table-section">
          <div class="table-panel">
            <div class="table-heading">Top Students by Points Rank</div>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Student ID</th>
                    <th>Student</th>
                    <th>Club</th>
                    <th>Events</th>
                    <th>Total Points</th>
                    <th>Recognition</th>
                  </tr>
                </thead>
                <tbody id="top-students-body">
                  <tr><td colspan="6" class="empty-cell">Loading student data...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

<script>
  let monthlyTrendChart = null;
  let recognitionChart = null;

  document.addEventListener("DOMContentLoaded", function() {
    // Fetch data from backend API
    const apiUrl = './admin_participation_reports_api.php?action=get_participation_data';

    fetch(apiUrl, { credentials: 'same-origin' })
      .then(response => {
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (data && data.success) {
          // Populate stat cards
          document.getElementById('stat-events').textContent = data.metrics.totalEvents ?? 0;
          document.getElementById('stat-participation').textContent = data.metrics.totalParticipation ?? 0;
          document.getElementById('stat-attendance').textContent = (data.metrics.avgAttendance ?? 0) + '%';
          document.getElementById('stat-outstanding').textContent = data.metrics.outstandingStudents ?? 0;

          // Create charts
          createMonthlyTrendChart(data.monthlyTrend || {labels: [], data: []});
          createRecognitionChart(data.recognitionDistribution || {labels: [], data: []});

          // Populate tables
          populateEventAttendance(data.eventAttendance || []);
          populateTopStudents(data.topStudents || []);
        } else {
          console.error("API Error:", data && data.message ? data.message : data);
          document.getElementById('event-attendance-body').innerHTML = '<tr><td colspan="7" class="empty-cell">Unable to load event data.</td></tr>';
          document.getElementById('top-students-body').innerHTML = '<tr><td colspan="6" class="empty-cell">Unable to load student data.</td></tr>';
        }
      })
      .catch(error => {
        console.error("Fetch Error:", error);
        document.getElementById('event-attendance-body').innerHTML = '<tr><td colspan="7" class="empty-cell">Error fetching event data.</td></tr>';
        document.getElementById('top-students-body').innerHTML = '<tr><td colspan="6" class="empty-cell">Error fetching student data.</td></tr>';
      });
  });

  // Create Monthly Trend Line Chart
  function createMonthlyTrendChart(monthlyData) {
    const ctx = document.getElementById('monthlyTrendChart');
    if (!ctx) return;

    if (monthlyTrendChart) {
      monthlyTrendChart.destroy();
    }

    monthlyTrendChart = new Chart(ctx.getContext('2d'), {
      type: 'line',
      data: {
        labels: monthlyData.labels,
        datasets: [{
          label: 'Participation Count',
          data: monthlyData.data,
          borderColor: 'rgba(23, 162, 184, 1)',
          backgroundColor: 'rgba(23, 162, 184, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: 'rgba(23, 162, 184, 1)',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 5
            },
            grid: {
              color: 'rgba(200, 200, 200, 0.1)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // Create Recognition Distribution Bar Chart
  function createRecognitionChart(recognitionData) {
    const ctx = document.getElementById('recognitionChart');
    if (!ctx) return;

    if (recognitionChart) {
      recognitionChart.destroy();
    }

    const colors = [
      'rgba(255, 193, 7, 0.8)',   // Gold
      'rgba(192, 192, 192, 0.8)', // Silver
      'rgba(205, 127, 50, 0.8)',  // Bronze
      'rgba(54, 162, 235, 0.8)',  // Participant
      'rgba(200, 200, 200, 0.8)'  // No Points
    ];

    recognitionChart = new Chart(ctx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: recognitionData.labels,
        datasets: [{
          label: 'Student Count',
          data: recognitionData.data,
          backgroundColor: colors.slice(0, recognitionData.data.length),
          borderColor: colors.slice(0, recognitionData.data.length).map(c => c.replace('0.8', '1')),
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 5
            },
            grid: {
              color: 'rgba(200, 200, 200, 0.1)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // Populate Event Attendance Table
  function populateEventAttendance(data) {
    const tbody = document.getElementById('event-attendance-body');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (data && data.length > 0) {
      data.forEach(event => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${escapeHTML(event.eventTitle)}</td>
          <td>${escapeHTML(event.clubName)}</td>
          <td>${event.registered}</td>
          <td>${event.present}</td>
          <td>${event.late}</td>
          <td>${event.absent}</td>
          <td><strong>${event.attendance_rate}</strong></td>
        `;
        tbody.appendChild(row);
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="7" class="empty-cell">No event data available.</td></tr>';
    }
  }

  // Populate Top Students Table
  function populateTopStudents(data) {
    const tbody = document.getElementById('top-students-body');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (data && data.length > 0) {
      data.forEach(student => {
        const row = document.createElement('tr');
        let recognitionClass = '';
        if (student.recognition === 'Gold') recognitionClass = 'recognition-gold';
        else if (student.recognition === 'Silver') recognitionClass = 'recognition-silver';
        else if (student.recognition === 'Bronze') recognitionClass = 'recognition-bronze';

        row.innerHTML = `
          <td>${escapeHTML(student.userID)}</td>
          <td>${escapeHTML(student.userName)}</td>
          <td>${escapeHTML(student.clubName)}</td>
          <td>${student.events_attended}</td>
          <td><strong>${student.total_points}</strong></td>
          <td><span class="${recognitionClass}">${escapeHTML(student.recognition)}</span></td>
        `;
        tbody.appendChild(row);
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="6" class="empty-cell">No student data available.</td></tr>';
    }
  }

  // Utility function to escape HTML
  function escapeHTML(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
</script>

</body>
</html>
