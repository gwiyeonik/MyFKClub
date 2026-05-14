<?php
// admin_events.php
session_start();
// Add authentication logic as needed.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <link rel="stylesheet" href="../CSS/events.css">
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
        <a href="admin_events.php" class="sidebar-link active">Events</a>
        <a href="admin_participation_reports.php" class="sidebar-link">Participation Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-title">Events</div>
      </div>

      <div class="search-bar-wrap">
        <input class="search-input" type="search" placeholder="Search Events/EventID">
        <div class="filter-row">
          <button class="filter-pill">Filter by club</button>
          <button class="filter-pill">Semester</button>
          <button class="filter-pill">Event Status</button>
          <button class="primary-pill">Apply Filter</button>
          <button class="secondary-pill">Export Report</button>
        </div>
      </div>

      <section class="stats-row">
        <div class="stat-card">
          <div class="stat-label">Total Events</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Upcoming Events</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Participants</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Fully Booked Events</div>
        </div>
      </section>

      <section class="events-grid-main">
        <div class="chart-card chart-highlight">
          <div class="chart-title">Number of Events Organized by Each Club</div>
          <div class="chart-placeholder">&lt;&lt; bar chart &gt;&gt;</div>
        </div>
        <div class="chart-card">
          <div class="chart-title">Number of Participants for Each Events</div>
          <div class="chart-placeholder">&lt;&lt; bar chart &gt;&gt;</div>
        </div>
      </section>

      <section class="events-grid-bottom">
        <div class="chart-card">
          <div class="chart-title">Popular Events Based on Registration Count</div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Events</th>
                  <th>Number of Registrations</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="2" class="empty-cell">No events found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="chart-card">
          <div class="chart-title">Number of Participants for Each Events</div>
          <div class="chart-placeholder">&lt;&lt; line graph &gt;&gt;</div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
