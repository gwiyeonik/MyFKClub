<?php
// admin_dashboard.php
session_start();


// 1. SECURITY CONTROL GATE: Protect page from unauthenticated sessions or invalid roles
// Security check
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    strtolower(trim($_SESSION['role'])) !== 'admin'
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
  <title>Admin Dashboard | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .chart-card canvas {
      display: block;
      width: 100% !important;
      height: 320px !important;
      max-height: 320px;
    }

    .charts-row.charts-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 18px;
      margin-bottom: 30px;
    }

    .chart-card.chart-large {
      min-height: 360px;
    }

    .chart-card .chart-title {
      margin-bottom: 16px;
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
        <a href="admin_dashboard.php" class="sidebar-link active">Home</a>
        <a href="admin_manage_users.php" class="sidebar-link">Manage Users</a>
        <a href="admin_student_clubs.php" class="sidebar-link">Student Clubs</a>
        <a href="admin_events.php" class="sidebar-link">Events</a>
        <a href="admin_participation_reports.php" class="sidebar-link">Participation Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">FK Club Admin</div>
         </div>
        <a href="myProfile.php" class="topbar-button">My Profile</a>
      </div>

      <div class="content-area">
        
        <section style="width: calc(100% + 60px); margin-left: 0px; margin-right: 0px; padding: 0; margin-bottom: 30px;">
          <div class="chart-card" style="width: 90%; padding: 30px; border-radius: 0; margin: 0;">
            <div class="chart-title" style="margin-bottom: 20px;">System Overview</div>
            <canvas id="metricsChart" style="height: 350px; max-height: 350px;"></canvas>
          </div>
        </section>

        <section class="charts-row charts-grid">
          <div class="chart-card chart-large">
            <div class="chart-title">Club Participation Summary</div>
            <canvas id="clubMetricsChart"></canvas>
          </div>
          <div class="chart-card chart-large">
            <div class="chart-title">Student Distribution Across Clubs</div>
            <canvas id="studentsPerClubChart"></canvas>
          </div>
        </section>

        <section class="table-section">
          <div class="table-panel">
            <div class="table-heading">Recent Registrations (Latest 5)</div>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>User ID</th>
                    <th>User Name</th>
                    <th>User Email</th>
                    <th>Role</th>
                  </tr>
                </thead>
                <tbody id="user-table-body">
                  <tr><td colspan="4" class="empty-cell">Loading registrations...</td></tr>
                </tbody>
              </table>
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
        // Fetch data from backend api
        const apiUrl = window.location.origin + window.location.pathname.replace(/\/admin_dashboard\.php$/, '/admin_dashboard_api.php') + '?action=get_dashboard_data';
        fetch(apiUrl, { credentials: 'same-origin' })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ' ' + response.statusText);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    // 1. Create Bar Chart with metrics
                    createMetricsChart(data.metrics);

                    // 2. Create club participation charts
                    createClubMetricsChart(data.metrics);
                    createStudentsPerClubChart(data.studentsPerClub || []);

                    // Set additional metrics safely if the elements exist
                    safeSetText('metric-attendance', data.metrics.avgAttendance);
                    safeSetText('aside-clubs', data.metrics.totalClubs);
                    safeSetText('aside-joined-students', data.metrics.totalStudentsInClubs || data.metrics.totalStudents);

                    // 3. Populate Recent Users Data Table Row
                    const tableBody = document.getElementById('user-table-body');
                    tableBody.innerHTML = ''; 

                    if (data.recentUsers && data.recentUsers.length > 0) {
                        data.recentUsers.forEach(user => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${escapeHTML(user.userID)}</td>
                                <td>${escapeHTML(user.userName)}</td>
                                <td>${escapeHTML(user.userEmail)}</td>
                                <td>${escapeHTML(getRoleLabel(user.roleID))}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="4" class="empty-cell">No recent registrations yet.</td></tr>';
                    }
                } else {
                    console.error("API Processing Error: " + data.message);
                    const tableBody = document.getElementById('user-table-body');
                    tableBody.innerHTML = `<tr><td colspan="4" class="empty-cell">Unable to load registrations: ${escapeHTML(data.message || 'Unknown error')}</td></tr>`;
                }
            })
            .catch(error => {
                console.error("Fetch API Connection Error:", error);
                const tableBody = document.getElementById('user-table-body');
                tableBody.innerHTML = `<tr><td colspan="4" class="empty-cell">Unable to load registrations: ${escapeHTML(error.message)}</td></tr>`;
            });
    });

    function safeSetText(id, text) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = text;
        }
    }

    // Create bar chart for metrics
    function createMetricsChart(metrics) {
        const ctx = document.getElementById('metricsChart').getContext('2d');
        
        if (metricsChart) {
            metricsChart.destroy();
        }

        metricsChart = new Chart(ctx, {
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
                    hoverBackgroundColor: [
                        'rgba(219, 39, 119, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(193, 156, 0, 1)'
                    ],
                    barPercentage: 0.3,  // Change 0.6 to make bars thicker (0.1-1.0)
                    categoryPercentage: 0.8  // Space between bars (0.1-1.0)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'x',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                            callback: function(value) {
                                return value;
                            }
                        },
                        grid: {
                            color: 'rgba(200, 200, 200, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });
    }

    function createClubMetricsChart(metrics) {
        const ctx = document.getElementById('clubMetricsChart').getContext('2d');
        if (clubMetricsChart) {
            clubMetricsChart.destroy();
        }

        clubMetricsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total Clubs', 'Active Clubs', 'Students in Clubs'],
                datasets: [{
                    label: 'Count',
                    data: [
                        metrics.totalClubs || 0,
                        metrics.totalActiveClubs || 0,
                        metrics.totalStudentsInClubs || 0
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.5,
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

    function createStudentsPerClubChart(studentsPerClub) {
        const ctx = document.getElementById('studentsPerClubChart').getContext('2d');
        if (studentsPerClubChart) {
            studentsPerClubChart.destroy();
        }

        const labels = studentsPerClub.length > 0 ? studentsPerClub.map(item => item.clubName) : ['No clubs yet'];
        const data = studentsPerClub.length > 0 ? studentsPerClub.map(item => item.members) : [1];
        const colors = [
            '#007bff', '#6610f2', '#6f42c1', '#e83e8c', '#fd7e14', '#ffc107', '#20c997', '#17a2b8'
        ];

        studentsPerClubChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: labels.map((_, index) => colors[index % colors.length]),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return `${label}: ${value} student${value === 1 ? '' : 's'}`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Simple XSS sanitizer to protect output rendering
    function escapeHTML(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function getRoleLabel(roleID) {
        switch (String(roleID)) {
            case '1': return 'Admin';
            case '2': return 'Committee';
            case '3': return 'Student';
            default: return 'Unknown';
        }
    }
  </script>
</body>
</html>