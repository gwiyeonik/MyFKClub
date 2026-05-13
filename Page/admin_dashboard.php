<?php
// admin_dashboard.php
session_start();
// Add authentication logic here if needed:
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: login.php');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
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
        <a href="#" class="sidebar-link">Student Clubs</a>
        <a href="#" class="sidebar-link">Events</a>
        <a href="#" class="sidebar-link">Participation Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">FK Club Admin</div>
        </div>
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

      <section class="stats-row">
        <div class="stat-card">
          <div class="stat-label">Registered Students</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Active Clubs</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Upcoming Events</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Avg Attendance Rate</div>
        </div>
      </section>

      <section class="charts-row">
        <div class="chart-card chart-large">
          <div class="chart-title">Students per Club</div>
          <div class="chart-placeholder">&lt;&lt; chart &gt;&gt;</div>
        </div>
        <div class="chart-card chart-small">
          <div class="chart-title">Students per Club</div>
          <div class="chart-placeholder">&lt;&lt; pie chart &gt;&gt;</div>
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
                  <th>RoleID</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="5" class="empty-cell">No recent registrations yet.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
