<?php
// committee_participation_report.php
session_start();
// Add authentication logic as needed.
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
  <style>
    .chart-card canvas {
      display: block;
      width: 100% !important;
      height: 320px !important;
      max-height: 320px;
    }

    .committee-charts-row {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 18px;
      margin-bottom: 30px;
    }

    .chart-card {
      min-height: 360px;
    }

    .chart-card .chart-title {
      margin-bottom: 16px;
    }

    .overview-section {
      width: calc(100% + 60px);
      margin-left: 0px;
      margin-right: 0px;
      padding: 0;
      margin-bottom: 30px;
    }

    .overview-card {
      width: 90%;
      padding: 30px;
      border-radius: 0;
      margin: 0;
    }
  </style>
</head>
<body>

  <div class="dashboard-shell">

    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>

      <nav class="sidebar-nav">
        <a href="committee_view_clubs.php" class="sidebar-link">View Clubs</a>
        <a href="committee_manage_events.php" class="sidebar-link">Manage Events</a>
        <a href="committee_members.php" class="sidebar-link">Members</a>
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

        <!-- SYSTEM OVERVIEW CHART -->
        <section class="overview-section">
          <div class="chart-card overview-card">
            <div class="chart-title" style="margin-bottom: 20px;">System Overview</div>
            <canvas id="metricsChart" style="height: 350px; max-height: 350px;"></canvas>
          </div>
        </section>

        <!-- SEARCH AND FILTERS -->
        <div class="search-bar-wrap">
          <input class="search-input" type="search" id="search-input" placeholder="Search Events/EventID">
        </div>

        <div class="filter-row">
          <select class="filter-select" name="event_filter" id="event-filter">
            <option value="">Event</option>
          </select>

          <select class="filter-select" name="semester_filter">
            <option value="">Semester</option>
            <option value="sem1">1st Semester</option>
            <option value="sem2">2nd Semester</option>
            <option value="sem3">3rd Semester</option>
          </select>

          <button class="secondary-pill" type="button" onclick="exportReport()">Export Report</button>
        </div>

        <!-- CLUB PARTICIPATION CHARTS -->
        <section class="committee-charts-row">
          <div class="chart-card">
            <div class="chart-title">Club Participation Summary</div>
            <canvas id="clubMetricsChart"></canvas>
          </div>

          <div class="chart-card">
            <div class="chart-title">Student Distribution Across Clubs</div>
            <canvas id="studentsPerClubChart"></canvas>
          </div>
        </section>

        <!-- CLUB MEMBER PARTICIPATION -->
        <section class="table-section">
          <div class="table-panel large-table-panel">
            <div class="table-heading">Club Member Participation</div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Club</th>
                    <th>Event</th>
                    <th>Attendance Status</th>
                    <th>Volunteer</th>
                    <th>Points</th>
                  </tr>
                </thead>
                <tbody id="member-participation-body">
                  <tr><td colspan="7" class="empty-cell">Loading participation data...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- BOTTOM SECTION -->
        <section class="committee-bottom-grid">

          <div class="table-panel small-table-panel">
            <div class="table-heading">Most Active Members (By Points)</div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Rank</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Events</th>
                    <th>Total Points</th>
                  </tr>
                </thead>
                <tbody id="active-members-body">
                  <tr><td colspan="5" class="empty-cell">Loading active members...</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="engagement-card">
            <div class="engagement-title">Participation Statistics</div>
            <div class="engagement-content" id="engagement-stats">
              <p>Loading statistics...</p>
            </div>
          </div>

        </section>

      </div>
    </main>
  </div>

<script>
    let metricsChart = null;
    let clubMetricsChart = null;
    let studentsPerClubChart = null;

    document.addEventListener("DOMContentLoaded", function() {
        // Fetch data from backend API
        const apiUrl = window.location.origin + window.location.pathname.replace(/\/committee_participation_report\.php$/, '/committee_participation_api.php') + '?action=get_participation_data';
        
        fetch(apiUrl, { credentials: 'same-origin' })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Create charts
                    createMetricsChart(data.metrics);
                    createClubMetricsChart(data.clubParticipation);
                    createStudentsPerClubChart(data.studentsPerClub);

                    // Populate tables
                    populateMemberParticipation(data.memberParticipation);
                    populateActiveMembers(data.activeMembers);
                    populateEngagementStats(data.metrics);
                } else {
                    console.error("API Error: " + data.message);
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
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

    // Create Student Distribution Doughnut Chart
    function createStudentsPerClubChart(clubDistribution) {
        const ctx = document.getElementById('studentsPerClubChart');
        if (!ctx) return;

        if (studentsPerClubChart) {
            studentsPerClubChart.destroy();
        }

        const colors = [
            'rgba(54, 162, 235, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 50, 192, 0.8)',
            'rgba(255, 99, 132, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)',
            'rgba(83, 102, 255, 0.8)',
            'rgba(255, 99, 192, 0.8)'
        ];

        studentsPerClubChart = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: clubDistribution.labels,
                datasets: [{
                    data: clubDistribution.data,
                    backgroundColor: colors.slice(0, clubDistribution.data.length),
                    borderColor: colors.slice(0, clubDistribution.data.length).map(c => c.replace('0.8', '1')),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }

    // Populate Member Participation Table
    function populateMemberParticipation(data) {
        const tbody = document.getElementById('member-participation-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (data && data.length > 0) {
            data.forEach(record => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHTML(record.userID)}</td>
                    <td>${escapeHTML(record.userName)}</td>
                    <td>${escapeHTML(record.clubName)}</td>
                    <td>${escapeHTML(record.eventTitle)}</td>
                    <td>
                        <span class="status-badge status-${record.attendanceStatus.toLowerCase().replace(' ', '-')}">
                            ${escapeHTML(record.attendanceStatus)}
                        </span>
                    </td>
                    <td>${escapeHTML(record.volunteer)}</td>
                    <td>${record.points}</td>
                `;
                tbody.appendChild(row);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="empty-cell">No participation records found.</td></tr>';
        }
    }

    // Populate Active Members Table
    function populateActiveMembers(data) {
        const tbody = document.getElementById('active-members-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (data && data.length > 0) {
            data.forEach((member, index) => {
                const row = document.createElement('tr');
                const rank = index + 1;
                row.innerHTML = `
                    <td>${rank}</td>
                    <td>${escapeHTML(member.userID)}</td>
                    <td>${escapeHTML(member.userName)}</td>
                    <td>${member.events_attended}</td>
                    <td><strong>${member.total_points}</strong></td>
                `;
                tbody.appendChild(row);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-cell">No active members found.</td></tr>';
        }
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