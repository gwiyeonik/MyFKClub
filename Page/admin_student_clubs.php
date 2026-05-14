<?php
// admin_student_clubs.php
session_start();

// Database connection for next Club ID auto-increment
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

$nextID = 1;
$result = mysqli_query($link, "SHOW TABLES");
if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $tableName = $row[0];
        if (stripos($tableName, 'club') !== false) {
            $escapedTable = mysqli_real_escape_string($link, $tableName);
            $status = mysqli_query($link, "SHOW TABLE STATUS LIKE '$escapedTable'");
            if ($status && mysqli_num_rows($status) > 0) {
                $statusRow = mysqli_fetch_assoc($status);
                $nextID = isset($statusRow['Auto_increment']) ? $statusRow['Auto_increment'] : 1;
                break;
            }
        }
    }
}
mysqli_close($link);
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
        <a href="admin_participation_reports.php" class="sidebar-link">Participation Reports</a>
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
            <select class="filter-select" name="filter_club">
              <option value="">Filter by club</option>
              <option value="club_a">Club A</option>
              <option value="club_b">Club B</option>
            </select>
            <select class="filter-select" name="filter_semester">
              <option value="">Semester</option>
              <option value="sem1">1st Semester</option>
              <option value="sem2">2nd Semester</option>
              <option value="sem3">3rd Semester</option>
            </select>
            <select class="filter-select" name="filter_status">
              <option value="">Club Status</option>
              <option value="active">Active</option>
              <option value="inactive">Not Active</option>
            </select>
            <button class="primary-pill" type="button">Apply Filter</button>
            <button class="secondary-pill" type="button">Export Report</button>
          </div>
        </div>

        <div class="clubs-grid">
          <section class="card club-form">
            <h3 class="section-title">Club Registration</h3>
            <div class="form-grid club-info-grid">
              <div class="form-field">
                <label for="club-id">Club ID</label>
                <input id="club-id" type="text" name="club_id" value="<?= isset($nextID) ? htmlspecialchars($nextID) : '' ?>" readonly required>
              </div>
              <div class="form-field">
                <label for="club-name">Club Name</label>
                <input id="club-name" type="text" name="club_name">
              </div>
              <div class="form-field">
                <label for="club-desc">Club Desc</label>
                <input id="club-desc" type="text" name="club_desc">
              </div>
              <div class="form-field">
                <label for="club-advisor">Club Advisor</label>
                <input id="club-advisor" type="text" name="club_advisor">
              </div>
              <div class="form-field form-row--inline">
                <label>Club Status</label>
                <label class="radio-label"><input type="radio" name="club_status" value="active"> Active</label>
                <label class="radio-label"><input type="radio" name="club_status" value="inactive"> Not Active</label>
              </div>
              <div class="form-field">
                <label for="club-created">Club Create</label>
                <input id="club-created" type="text" name="club_created">
              </div>
              <div class="form-actions">
                <button type="submit" class="btn-add">Add</button>
              </div>
            </div>
          </section>

          <section class="card club-info-panel">
                <h3 class="section-title">Club Information</h3><br>
                <div class="form-grid club-info-grid">
                   <div class="form-field form-field--stacked">
                  <label for="club-select">Club ID/Name</label>
                  <select id="club-select" class="club-select">
                    <option value=""></option>
                  </select>
                </div>
                <div class="form-field">
                    <label for="info-club-name">Club Name</label>
                    <input id="info-club-name" type="text" name="info_club_name">
                  </div>
                  <div class="form-field">
                    <label for="info-club-desc">Club Desc</label>
                    <input id="info-club-desc" type="text" name="info_club_desc">
                  </div>
                  <div class="form-field">
                    <label for="info-club-advisor">Club Advisor</label>
                    <input id="info-club-advisor" type="text" name="info_club_advisor">
                  </div>
                  <div class="form-field form-row--inline">
                    <label>Club Status</label>
                    <label class="radio-label"><input type="radio" name="info_club_status" value="active"> Active</label>
                    <label class="radio-label"><input type="radio" name="info_club_status" value="inactive"> Not Active</label>
                  </div>
                  <div class="form-field">
                    <label for="info-club-created">Club Create</label>
                    <input id="info-club-created" type="text" name="info_club_created">
                  </div>
                </div>

                <div class="action-buttons">
                  <button type="button" class="btn-delete">Delete</button>
                  <button type="button" class="btn-update">Update</button>
                </div>
              </div>
          </section>

          <section class="card committee-panel">
            <div class="card-header">
              <h3 class="section-title">Club Committee</h3>
              <input type="text" placeholder="Search userName/userID/......" class="pill-search">
            </div>

            <div class="committee-table-content">
              <div class="committee-grid-header">
                <span>Club ID</span>
                <span>Membership ID</span>
                <span>Committee ID</span>
                <span>User ID</span>
                <span>User Name</span>
              </div>
              <div class="committee-data-area">
                <div class="empty-cell">No committee records available.</div>
              </div>
            </div>

            <div class="committee-actions">
              <button class="btn-action btn-teal">Add</button>
              <button class="btn-action btn-teal">Update</button>
              <button class="btn-action btn-red">Delete</button>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
