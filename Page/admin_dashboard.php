<?php
// admin_dashboard.php
session_start();
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
      <!-- Topbar stays flush to the sidebar -->
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">FK Club Admin</div>
        </div>
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

      <!-- NEW WRAPPER: This provides the 'breathing room' seen in image_2a6841.png -->
      <div class="content-area">
        <section class="quick-links-row">
          <div class="section-heading">Quick Admin Access</div>
          <div class="stats-row">
            <a href="admin_manage_users.php" class="stat-card" style="text-decoration:none;color:inherit;"><div class="stat-label">Manage Users</div></a>
            <a href="admin_student_clubs.php" class="stat-card" style="text-decoration:none;color:inherit;"><div class="stat-label">Student Clubs</div></a>
            <a href="admin_events.php" class="stat-card" style="text-decoration:none;color:inherit;"><div class="stat-label">Events</div></a>
            <a href="admin_participation_reports.php" class="stat-card" style="text-decoration:none;color:inherit;"><div class="stat-label">Participation Reports</div></a>
          </div>
        </section>

        <section class="stats-row">
          <div class="stat-card"><div class="stat-label">Registered Students</div></div>
          <div class="stat-card"><div class="stat-label">Active Clubs</div></div>
          <div class="stat-card"><div class="stat-label">Upcoming Events</div></div>
          <div class="stat-card"><div class="stat-label">Avg Attendance Rate</div></div>
        </section>

        <section class="charts-row">
          <div class="aside-cards">
            <div class="stat-card">
              <div class="stat-label">Club in Faculty</div>
            </div>
            <div class="stat-card">
              <div class="stat-label">Student Join Club</div>
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
                    <th>RoleID</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="5" class="empty-cell">No recent registrations yet.</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div> <!-- End content-area -->
    </main>
  </div>
</body>
</html>
