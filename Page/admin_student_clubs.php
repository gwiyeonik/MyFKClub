<?php
// admin_student_clubs.php
session_start();

$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['club_name'])) {
    $clubName = trim($_POST['club_name']);
    $clubDesc = trim($_POST['club_desc']);
    $clubAdvisor = trim($_POST['club_advisor']);
    $clubStatus = (isset($_POST['club_status']) && $_POST['club_status'] === 'inactive') ? 'inactive' : 'Active';
    $clubCreated = trim($_POST['club_created']);

    if ($clubCreated === '') {
        $clubCreated = date('Y-m-d');
    }

    $stmt = mysqli_prepare($link, "INSERT INTO club (clubName, clubDesc, clubCreated, clubAdvisor, clubStatus) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $clubName, $clubDesc, $clubCreated, $clubAdvisor, $clubStatus);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: admin_student_clubs.php");
        exit;
    }
}

$nextID = 1;
$idResult = mysqli_query($link, "SELECT MAX(clubID) AS maxID FROM club");
if ($idResult && $row = mysqli_fetch_assoc($idResult)) {
    $nextID = isset($row['maxID']) ? ((int)$row['maxID'] + 1) : 1;
}

$clubList = [];
$clubResult = mysqli_query($link, "SELECT clubID, clubName FROM club ORDER BY clubName");
if ($clubResult) {
    while ($row = mysqli_fetch_assoc($clubResult)) {
        $clubList[] = $row;
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
          <form method="post" action="admin_student_clubs.php">
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
                <input id="club-created" type="date" name="club_created">
              </div>
              <div class="form-actions">
                <button type="submit" class="btn-add">Add</button>
              </div>
            </div>
          </section>
          </form>

          <section class="card club-info-panel">
                <h3 class="section-title">Club Information</h3><br>
                <div class="form-grid club-info-grid">
                   <div class="form-field form-field--stacked">
                  <label for="club-select">Club ID/Name</label>
                  <select id="club-select" class="club-select" name="club_select">
                    <option value=""></option>
                    <?php foreach ($clubList as $club): ?>
                      <option value="<?= htmlspecialchars($club['clubID']) ?>"><?= htmlspecialchars($club['clubID'] . ' - ' . $club['clubName']) ?></option>
                    <?php endforeach; ?>
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
                    <input id="info-club-created" type="date" name="info_club_created">
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
                <span>User ID</span>
                <span>User Name</span>
                <span>Committee Position</span>
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

          <section class="club-list-container">
              <h1 class="page-title">List of Clubs</h1>
              
              <div class="main-card">
                  <div class="top-row">
                      <div class="club-details">
                          <div class="input-group">
                              <label><strong>Club Name/Club ID</strong></label>
                              <input list="club-options" id="club-input" name="club-selection" placeholder="Select or type a club...">
                              <datalist id="club-options">
                                  <?php foreach ($clubList as $club): ?>
                                      <option value="<?= htmlspecialchars($club['clubID'] . ' - ' . $club['clubName']) ?>">
                                  <?php endforeach; ?>
                              </datalist>
                          </div>
                          <div class="info-field"><strong>Club Desc</strong><p id="list-club-desc" class="info-value">Select a club to view details</p></div>
                          <div class="info-field"><strong>Club Advisor</strong><p id="list-club-advisor" class="info-value">Select a club to view details</p></div>
                          <div class="info-field"><strong>Club Status</strong><p id="list-club-status" class="info-value">Select a club to view details</p></div>
                          <div class="info-field"><strong>Club Created</strong><p id="list-club-created" class="info-value">Select a club to view details</p></div>
                      </div>

                      <div class="committee-card">
                          <h3>Club Committee</h3>
                          <div class="committee-table-header">
                              <span>User ID</span>
                              <span>User Name</span>
                              <span>Committee Position</span>
                          </div>
                          <div class="committee-content" id="list-committee-content">
                              <div class="empty-cell">Select a club to load committee members.</div>
                          </div>
                      </div>
                  </div>

                  <div class="events-section">
                      <h3>Club Events</h3>
                      <div class="events-grid" id="list-events-grid">
                          <div class="event-placeholder">Select a club to view events.</div>
                      </div>
                      <div class="button-wrapper">
                          <button class="view-events-btn" type="button">View Events</button>
                      </div>
                  </div>
              </div>
          </section>
        </div>
      </div>
    </main>
    <script src="admin_student_clubs.js"></script>
  </div>
</body>
</html>
