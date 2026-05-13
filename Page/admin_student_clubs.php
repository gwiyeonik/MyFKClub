<?php
// admin_student_clubs.php
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
  <title>Student Clubs | MyFKClub Admin</title>
  <link rel="stylesheet" href="../CSS/student_clubs.css">
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
        <a href="admin_student_clubs.php" class="sidebar-link active">Student Clubs</a>
        <a href="admin_events.php" class="sidebar-link">Events</a>
        <a href="#" class="sidebar-link">Participation Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <!-- Topbar -->
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Student Clubs</div>
        </div>
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

      <!-- Content Area -->
      <div class="content-area">
        <!-- Search and Filter Bar -->
        <div class="search-bar-wrap">
          <input type="text" class="search-input" placeholder="Search clubname/clubID">
          <div class="filter-row">
            <button class="filter-pill">Filter by club ▼</button>
            <button class="filter-pill">Semester ▼</button>
            <button class="filter-pill">Club Status ▼</button>
            <button class="primary-pill">Apply filter</button>
            <button class="secondary-pill">Export Report</button>
          </div>
        </div>

        <!-- Stats Cards Row -->
        <section class="stats-row">
          <div class="stat-card">
            <div class="stat-label">Total Clubs in Faculty</div>
            <div class="stat-value">--</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Total Number Active Clubs</div>
            <div class="stat-value">--</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Total Students Join Clubs</div>
            <div class="stat-value">--</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Avg Attendance Rate</div>
            <div class="stat-value">--</div>
          </div>
        </section>

        <!-- Main Content Grid: Club Registration Form + Club Info Panel + Recent Registrations -->
        <div class="clubs-grid">
          <!-- Left Column: Club Registration Form -->
          <div class="form-section">
            <h3 class="section-title">Club Registration</h3>
            <form class="club-form">
              <div class="form-field">
                <label>Club ID</label>
                <input type="text" placeholder="">
              </div>

              <div class="form-field">
                <label>Club Name</label>
                <input type="text" placeholder="">
              </div>

              <div class="form-field">
                <label>Club Desc</label>
                <input type="text" placeholder="">
              </div>

              <div class="form-field">
                <label>Club Advisor</label>
                <input type="text" placeholder="">
              </div>

              <div class="form-field">
                <label>Club Status</label>
                <div class="radio-group">
                  <label class="radio-label">
                    <input type="radio" name="clubStatus" value="active">
                    <span>Active</span>
                  </label>
                  <label class="radio-label">
                    <input type="radio" name="clubStatus" value="inactive">
                    <span>Not Active</span>
                  </label>
                </div>
              </div>

              <div class="form-field">
                <label>Club Create</label>
                <input type="text" placeholder="">
              </div>

              <button type="submit" class="btn-add">Add</button>
            </form>

            <!-- Charts Section -->
            <div class="charts-section">
              <div class="chart-card chart-large">
                <div class="chart-title">Number of Students per Club</div>
                <div class="chart-placeholder">&lt;&lt;bar chart&gt;&gt;</div>
              </div>

              <div class="chart-card chart-small">
                <div class="chart-title">Number of Participants by Events</div>
                <div class="chart-placeholder">&lt;&lt;donut chart&gt;&gt;</div>
              </div>
            </div>
          </div>

          <!-- Right Column: Club Information + Recent Registrations -->
          <div class="info-section">
            <!-- Club Information Panel -->
            <div class="club-info-panel">
              <h3 class="section-title">Club Information</h3>
              <div class="form-grid">
                <div class="form-field">
                  <label>Club Name</label>
                  <input type="text" placeholder="">
                </div>

                <div class="form-field">
                  <label>Club Desc</label>
                  <input type="text" placeholder="">
                </div>

                <div class="form-field">
                  <label>Club Advisor</label>
                  <input type="text" placeholder="">
                </div>

                <div class="form-field">
                  <label>Club Status</label>
                  <div class="radio-group">
                    <label class="radio-label">
                      <input type="radio" name="clubStatusInfo" value="active">
                      <span>Active</span>
                    </label>
                    <label class="radio-label">
                      <input type="radio" name="clubStatusInfo" value="inactive">
                      <span>Not Active</span>
                    </label>
                  </div>
                </div>

                <div class="form-field">
                  <label>Club Create</label>
                  <input type="text" placeholder="">
                </div>
              </div>

              <div class="action-buttons">
                <button class="btn-delete">Delete</button>
                <button class="btn-update">Update</button>
              </div>
            </div>

            <!-- Recent Registrations Table -->
            <div class="registrations-panel">
              <h3 class="section-title">Recent Registrations</h3>
              <div class="table-wrapper">
                <table>
                  <thead>
                    <tr>
                      <th>User ID</th>
                      <th>Club Name</th>
                      <th>Membership ID</th>
                      <th>User Name</th>
                      <th>Committee Type (if committee)</th>
                      <th>Join Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td colspan="6" class="empty-cell">No recent registrations yet.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
