<?php
// admin_dashboard.php
session_start();

// 1. SECURITY CONTROL GATE: Protect page from unauthenticated sessions or invalid roles
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? null) !== 1) {
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

        <section class="charts-row">
          <div class="aside-cards">
            <div class="stat-card">
              <div class="stat-label">Avg Attendance Rate</div>
              <strong id="metric-attendance" style="font-size: 24px; display: block; margin-top: 5px; color: #1a365d;">...</strong>
            </div>
            
            <div class="stat-card">
              <div class="stat-label">Club in Faculty</div>
              <strong id="aside-clubs" style="font-size: 24px; display: block; margin-top: 5px; color: #1a365d;">...</strong>
            </div>
            
            <div class="stat-card">
              <div class="stat-label">Student Join Club</div>
              <strong id="aside-joined-students" style="font-size: 24px; display: block; margin-top: 5px; color: #1a365d;">...</strong>
            </div>
          </div>

          <div class="chart-group">
            <div class="chart-card chart-large">
              <div class="chart-title">Students per Club</div>
              <div class="chart-placeholder">&lt;&lt; chart &gt;&gt;</div>
            </div>
            <div class="chart-card chart-small">
              <div class="chart-title">Role Distribution</div>
              <div class="chart-placeholder">&lt;&lt; pie chart &gt;&gt;</div>
            </div>
          </div>
        </section>

        <section class="table-section">
          <div class="table-panel">
            <div class="table-heading">Recent Registrations</div>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>User ID</th>
                    <th>User Name</th>
                    <th>User Email</th>
                    <th>Role ID</th>
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

    document.addEventListener("DOMContentLoaded", function() {
        // Fetch data from backend api
        fetch('admin_dashboard_api.php?action=get_dashboard_data')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 1. Create Bar Chart with metrics
                    createMetricsChart(data.metrics);

                    // Set additional metrics
                    document.getElementById('metric-attendance').textContent = data.metrics.avgAttendance;
                    document.getElementById('aside-clubs').textContent = data.metrics.totalClubs;

                    // FIXED: Dynamic data integration link for total students joined
                    document.getElementById('aside-joined-students').textContent = data.metrics.totalStudentsJoined || data.metrics.totalStudents;

                    // 2. Populate Recent Users Data Table Row
                    const tableBody = document.getElementById('user-table-body');
                    tableBody.innerHTML = ''; 

                    if (data.recentUsers && data.recentUsers.length > 0) {
                        data.recentUsers.forEach(user => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${escapeHTML(user.userID)}</td>
                                <td>${escapeHTML(user.userName)}</td>
                                <td>${escapeHTML(user.userEmail)}</td>
                                <td>${escapeHTML(user.roleID)}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="4" class="empty-cell">No recent registrations yet.</td></tr>';
                    }
                } else {
                    console.error("API Processing Error: " + data.message);
                }
            })
            .catch(error => console.error("Fetch API Connection Error:", error));
    });

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
                        max: 25,
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

    // Simple XSS sanitizer to protect output rendering
    function escapeHTML(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
  </script>
</body>
</html>