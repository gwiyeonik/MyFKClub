<?php
// committee_participation_report.php
session_start();

// SECURITY CONTROL GATE: Protect page for committee role
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    strtolower(trim($_SESSION['role'])) !== 'committee'
) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Committee Participation Report | MyFKClub Committee</title>

  <link rel="stylesheet" href="../CSS/dashboard.css">
  <link rel="stylesheet" href="../CSS/committee_participation_report.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

  <div class="dashboard-shell">

    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>

      <nav class="sidebar-nav">
        <a href="committee_dashboard.php" class="sidebar-link">Home</a>
        <a href="committee_view_clubs.php" class="sidebar-link">View Clubs</a>
        <a href="committee_manage_events.php" class="sidebar-link">Manage Events</a>
        <a href="committee_attendance_report.php" class="sidebar-link">Attendance</a>
        <a href="committee_participation_report.php" class="sidebar-link active">Reports</a>
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="dashboard-main">

      <!-- TOPBAR -->
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Participation Reports</div>
        </div>
       
      </div>

      <!-- CONTENT -->
      <div class="content-area">

        <!-- STAT CARDS (3 columns) -->
        <section style="display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-bottom:24px;">
          <div style="background:#fff; padding:18px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06)">
            <div style="color:#556; font-size:13px; margin-bottom:8px">Total Club Participation</div>
            <div style="font-size:12px; color:#999; margin-bottom:6px">Registered | Present | Late | Absent</div>
            <div id="stat-club-participation" style="font-size:18px; font-weight:700; color:#174f86">—</div>
          </div>
          <div style="background:#fff; padding:18px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06)">
            <div style="color:#556; font-size:13px; margin-bottom:8px">Avg Attendance Rate Per Club Events</div>
            <div id="stat-attendance-rate" style="font-size:18px; font-weight:700; color:#174f86">—</div>
          </div>
          <div style="background:#fff; padding:18px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06)">
            <div style="color:#556; font-size:13px; margin-bottom:8px">Most Active Members & Top Members by Points</div>
            <div style="font-size:18px; font-weight:700; color:#174f86">—</div>
          </div>
        </section>

        <!-- CHARTS (2 columns) -->
        <section style="display:grid; grid-template-columns:1.5fr 1fr; gap:18px; margin-bottom:24px;">
          <div style="background:#fff; padding:18px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06); display:flex; flex-direction:column">
            <div style="color:#174f86; font-weight:600; margin-bottom:12px">Club Attendance Trend</div>
            <div style="height:300px; position:relative; flex:1">
              <canvas id="metricsChart"></canvas>
            </div>
          </div>
          <div style="background:#fff; padding:18px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06); display:flex; flex-direction:column">
            <div style="color:#174f86; font-weight:600; margin-bottom:12px">Points Distribution</div>
            <div style="height:300px; position:relative; flex:1">
              <canvas id="clubMetricsChart"></canvas>
            </div>
          </div>
        </section>

        <!-- CLUB MEMBER PARTICIPATION -->
        <section style="background:#fff; padding:18px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:24px">
          <div style="color:#174f86; font-weight:600; margin-bottom:12px">Club Member Participation</div>
          <div style="overflow-x:auto">
            <table style="width:100%; border-collapse:collapse">
              <thead>
                <tr style="background:#f8f9fa; border-bottom:1px solid #e0e0e0">
                  <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Student</th>
                  <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Event</th>
                  <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Attendance Status</th>
                  <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Volunteer</th>
                  <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Points</th>
                  <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Recognition</th>
                </tr>
              </thead>
              <tbody id="member-participation-body">
                <tr><td colspan="6" style="text-align:center; color:#999; padding:24px">Loading participation data...</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- BOTTOM GRID (2 columns) -->
        <section style="display:grid; grid-template-columns:1.6fr 1fr; gap:18px;">
          <div style="background:#fff; padding:18px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06)">
            <div style="color:#174f86; font-weight:600; margin-bottom:12px">Most Active Members</div>
            <div style="overflow-x:auto">
              <table style="width:100%; border-collapse:collapse">
                <thead>
                  <tr style="background:#f8f9fa; border-bottom:1px solid #e0e0e0">
                    <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Rank</th>
                    <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Student</th>
                    <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Event Attended</th>
                    <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Total Points</th>
                    <th style="padding:10px 12px; text-align:left; color:#556; font-weight:600; font-size:13px">Recognition Status</th>
                  </tr>
                </thead>
                <tbody id="active-members-body">
                  <tr><td colspan="5" style="text-align:center; color:#999; padding:24px">Loading active members...</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div style="background:#fff; padding:18px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06)">
            <div style="color:#174f86; font-weight:600; margin-bottom:12px">Engagement Summary</div>
            <div id="engagement-stats" style="padding:12px 0">
              <p style="color:#999; margin:0">Loading statistics...</p>
            </div>
          </div>
        </section>


      </div>
    </main>
  </div>

<script>
    let metricsChart = null;
    let clubMetricsChart = null;

    document.addEventListener("DOMContentLoaded", function() {
      // Fetch data from backend API (relative path)
      const apiUrl = './committee_participation_api.php?action=get_participation_data';

      fetch(apiUrl, { credentials: 'same-origin' })
        .then(response => {
          if (!response.ok) {
            throw new Error('HTTP ' + response.status + ' ' + response.statusText);
          }
          return response.json();
        })
        .then(data => {
          if (data && data.success) {
            // Create charts
            createMetricsChart(data.metrics || {});
            createClubMetricsChart(data.clubParticipation || {labels:[], present:[], late:[], absent:[]});

            // Populate stat cards
            try {
              // compute totals from clubParticipation arrays if present
              const cp = data.clubParticipation || {};
              let totalRegistered = 0, totalPresent = 0, totalLate = 0, totalAbsent = 0;
              if (Array.isArray(cp.registered)) {
                cp.registered.forEach(v => totalRegistered += Number(v) || 0);
              }
              if (Array.isArray(cp.present)) { cp.present.forEach(v => totalPresent += Number(v) || 0); }
              if (Array.isArray(cp.late)) { cp.late.forEach(v => totalLate += Number(v) || 0); }
              if (Array.isArray(cp.absent)) { cp.absent.forEach(v => totalAbsent += Number(v) || 0); }

              const statClubEl = document.getElementById('stat-club-participation');
              if (statClubEl) {
                statClubEl.innerHTML = `${totalRegistered} &nbsp;|&nbsp; ${totalPresent} &nbsp;|&nbsp; ${totalLate} &nbsp;|&nbsp; ${totalAbsent}`;
              }

              const statAvgEl = document.getElementById('stat-attendance-rate');
              if (statAvgEl) {
                statAvgEl.textContent = (data.metrics && typeof data.metrics.avgAttendance !== 'undefined') ? data.metrics.avgAttendance + '%' : '-';
              }
            } catch (err) {
              console.warn('Stat card population failed', err);
            }

            // Populate tables
            populateMemberParticipation(data.memberParticipation || []);
            populateActiveMembers(data.activeMembers || []);
            populateEngagementStats(data.metrics || {totalStudents:0,totalEvents:0,totalClubs:0,avgAttendance:0});
          } else {
            console.error('API Error:', data && data.message ? data.message : data);
            document.getElementById('member-participation-body').innerHTML = '<tr><td colspan="6" class="empty-cell">Unable to load participation data.</td></tr>';
            document.getElementById('active-members-body').innerHTML = '<tr><td colspan="5" class="empty-cell">Unable to load active members.</td></tr>';
            document.getElementById('engagement-stats').innerHTML = '<p class="empty-cell">Unable to load statistics.</p>';
          }
        })
        .catch(error => {
          console.error('Fetch Error:', error);
          document.getElementById('member-participation-body').innerHTML = '<tr><td colspan="6" class="empty-cell">Error fetching data.</td></tr>';
          document.getElementById('active-members-body').innerHTML = '<tr><td colspan="5" class="empty-cell">Error fetching data.</td></tr>';
          document.getElementById('engagement-stats').innerHTML = '<p class="empty-cell">Error fetching data.</p>';
        });
    });

    // Create System Overview Bar Chart
    function createMetricsChart(metrics) {
        const ctx = document.getElementById('metricsChart');
        if (!ctx) return;

        if (metricsChart) {
            metricsChart.destroy();
        }

        metricsChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Registered Students', 'Active Clubs', 'Upcoming Events'],
                datasets: [{
                    label: 'Count',
                    data: [
                        metrics.totalStudents,
                        metrics.totalClubs,
                        metrics.totalEvents
                    ],
                    backgroundColor: [
                        'rgba(219, 39, 119, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(193, 156, 0, 0.8)'
                    ],
                    borderColor: [
                        'rgba(219, 39, 119, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(193, 156, 0, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.3,
                    categoryPercentage: 0.8
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

    // Create Club Participation Bar Chart
    function createClubMetricsChart(clubData) {
        const ctx = document.getElementById('clubMetricsChart');
        if (!ctx) return;

        if (clubMetricsChart) {
            clubMetricsChart.destroy();
        }

        clubMetricsChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: clubData.labels,
                datasets: [
                    {
                        label: 'Present',
                        data: clubData.present,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Late',
                        data: clubData.late,
                        backgroundColor: 'rgba(255, 193, 7, 0.8)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Absent',
                        data: clubData.absent,
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        stacked: false
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Populate Member Participation Table (matches current columns)
    function populateMemberParticipation(data) {
      const tbody = document.getElementById('member-participation-body');
      if (!tbody) return;

      tbody.innerHTML = '';

      if (data && data.length > 0) {
        data.forEach(record => {
          const row = document.createElement('tr');
          row.style.cssText = 'border-bottom:1px solid #e0e0e0';
          const studentLabel = `${escapeHTML(record.userName || '')} (${escapeHTML(record.userID || '')})`;
          const attendanceStatus = record.attendanceStatus || 'Not Recorded';
          const volunteer = record.volunteer || 'No';
          const points = Number(record.points) || 0;
          const recognition = computeRecognition(points);

          row.innerHTML = `
            <td style="padding:10px 12px; color:#374151">${studentLabel}</td>
            <td style="padding:10px 12px; color:#374151">${escapeHTML(record.eventTitle || '')}</td>
            <td style="padding:10px 12px; color:#374151"><span class="status-badge status-${String(attendanceStatus).toLowerCase().replace(/\s+/g, '-')}">${escapeHTML(attendanceStatus)}</span></td>
            <td style="padding:10px 12px; color:#374151">${escapeHTML(volunteer)}</td>
            <td style="padding:10px 12px; color:#374151">${points}</td>
            <td style="padding:10px 12px; color:#374151">${recognition}</td>
          `;
          tbody.appendChild(row);
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#999; padding:24px">No participation records found.</td></tr>';
      }
    }

    // Populate Active Members Table (with recognition)
    function populateActiveMembers(data) {
      const tbody = document.getElementById('active-members-body');
      if (!tbody) return;

      tbody.innerHTML = '';

      if (data && data.length > 0) {
        data.forEach((member, index) => {
          const row = document.createElement('tr');
          row.style.cssText = 'border-bottom:1px solid #e0e0e0';
          const rank = index + 1;
          const points = Number(member.total_points) || 0;
          const recognition = computeRecognition(points);
          const studentLabel = `${escapeHTML(member.userName || '')} (${escapeHTML(member.userID || '')})`;

          row.innerHTML = `
            <td style="padding:10px 12px; color:#374151">${rank}</td>
            <td style="padding:10px 12px; color:#374151">${studentLabel}</td>
            <td style="padding:10px 12px; color:#374151">${member.events_attended || 0}</td>
            <td style="padding:10px 12px; color:#374151"><strong>${points}</strong></td>
            <td style="padding:10px 12px; color:#374151">${recognition}</td>
          `;
          tbody.appendChild(row);
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#999; padding:24px">No active members found.</td></tr>';
      }
    }

    // Determine recognition label by points
    function computeRecognition(points) {
      points = Number(points) || 0;
      if (points >= 80) return 'Outstanding participant';
      if (points >= 50) return 'Eligible for active student award';
      if (points >= 20) return 'Eligible for participation certificate';
      return 'Warning/Reminder';
    }

    // Populate Engagement Statistics
    function populateEngagementStats(metrics) {
        const container = document.getElementById('engagement-stats');
        if (!container) return;

        container.innerHTML = `
            <div style="padding: 10px 0;">
                <p><strong>Total Students Participated:</strong> <span style="font-size: 1.3em; color: #db2777;">${metrics.totalStudents}</span></p>
                <p><strong>Total Events Held:</strong> <span style="font-size: 1.3em; color: #17a2b8;">${metrics.totalEvents}</span></p>
                <p><strong>Total Clubs:</strong> <span style="font-size: 1.3em; color: #ffc107;">${metrics.totalClubs}</span></p>
                <p><strong>Average Attendance Rate:</strong> <span style="font-size: 1.3em; color: #28a745;">${metrics.avgAttendance}%</span></p>
            </div>
        `;
    }

    // Utility function to escape HTML
    function escapeHTML(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Export Report Function
    function exportReport() {
        alert('Export functionality to be implemented.');
    }
</script>

</body>
</html>