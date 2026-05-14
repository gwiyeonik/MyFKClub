<?php
// admin_participation_reports.php
session_start();
// Add authentication logic as needed.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Participation Reports | MyFKClub Admin</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
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
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>
      <div class="content-area">
        <div class="search-bar-wrap">
          <input class="search-input" type="search" placeholder="Search Events/EventID">
          <div class="filter-row">
            <button class="filter-pill" type="button">Filter by club</button>
            <button class="filter-pill" type="button">Semester</button>
            <button class="primary-pill" type="button">Apply Filter</button>
            <button class="secondary-pill" type="button">Export Report</button>
          </div>
        </div>
        <section class="stats-row">
          <div class="stat-card"><div class="stat-label">Total Events</div></div>
          <div class="stat-card"><div class="stat-label">Total Participation</div></div>
          <div class="stat-card"><div class="stat-label">Avg Attendance Rate</div></div>
          <div class="stat-card"><div class="stat-label">Outstanding Students</div></div>
        </section>
        <section class="charts-row">
          <div class="chart-card">
            <div class="chart-title">Monthly Participation Trend</div>
            <div class="chart-placeholder">&lt;&lt; bar chart &gt;&gt;</div>
          </div>
          <div class="chart-card">
            <div class="chart-title">Recognition Level Distribution</div>
            <div class="chart-placeholder">&lt;&lt; bar chart &gt;&gt;</div>
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
                <tbody>
                  <tr><td colspan="7" class="empty-cell">No data available.</td></tr>
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
                    <th>Student</th>
                    <th>Club</th>
                    <th>Events</th>
                    <th>Total Points</th>
                    <th>Recognition</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="5" class="empty-cell">No data available.</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</body>
</html>
