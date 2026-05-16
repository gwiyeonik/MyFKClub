<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Clubs | MyFKClub committee</title>
  <link rel="stylesheet" href="../CSS/committee_events.css">
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>

      <nav class="sidebar-nav">
        <a href="#" class="sidebar-link">Home</a>
        <a href="#" class="sidebar-link">Manage Clubs</a>
        <a href="committee_manage_events.php" class="sidebar-link active">Manage Events</a>
        <a href="#" class="sidebar-link">Members</a>
        <a href="#" class="sidebar-link">Attendance</a>
        <a href="committee_participation_report.php" class="sidebar-link">Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Manage Events</div>
        </div>
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

      <div class="content-area">
        
        <div class="search-bar-wrap">
          <input type="text" class="search-input" placeholder="Search event name/event ID">
          <div class="filter-row">
            <select class="filter-select" name="filter_club">
              <option value="" disabled selected>Filter by club</option>
              <option value="club_a">Club A</option>
              <option value="club_b">Club B</option>
            </select>
            <select class="filter-select" name="filter_status">
              <option value="" disabled selected>Event Status</option>
              <option value="active">Active</option>
              <option value="inactive">Not Active</option>
            </select>
            <div class="action-row">
                <button class="primary-pill" type="button">Apply Filter</button>
                <button class="secondary-pill" type="button">Export Report</button>
            </div>
          </div>
        </div>

        <section class="manage-grid">
          
          <div class="chart-card">
            <div class="chart-title">Event List</div>
            <div class="chart-placeholder">
              &lt;&lt; bar chart &gt;&gt;
            </div>
          </div>

        <section class="club-info-panel">
            <h3 class="section-title">Event Information</h3>
            
            <form action="update_club.php" method="POST">
                <div class="form-grid club-info-grid">
                  
                  <div class="form-field">
                    <label for="club-select">Event ID Name</label>
                    <select id="club-select" class="club-select" name="club_select">
                      <option value=""></option>
                      <?php foreach ($clubList as $club): ?>
                        <option value="<?= htmlspecialchars($club['clubID']) ?>">
                            <?= htmlspecialchars($club['clubID'] . ' - ' . $club['clubName']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-field">
                    <label for="info-club-name">Event Name</label>
                    <input id="info-club-name" type="text" name="info_club_name">
                  </div>

                  <div class="form-field">
                    <label for="info-club-desc">Event Description</label>
                    <input id="info-club-desc" type="text" name="info_club_desc">
                  </div>

                  <div class="form-field">
                    <label for="info-club-advisor">Event Advisor</label>
                    <input id="info-club-advisor" type="text" name="info_club_advisor">
                  </div>

                  <div class="form-field">
                    <label>Event Status</label>
                    <div class="role-row">
                        <label class="radio-label"><input type="radio" name="info_club_status" value="active"> Active</label>
                        <label class="radio-label"><input type="radio" name="info_club_status" value="inactive"> Not Active</label>
                    </div>
                  </div>

                  <div class="form-field">
                    <label for="info-club-created">Event Created Date</label>
                    <input id="info-club-created" type="date" name="info_club_created">
                  </div>

                  <div class="form-field file-field">
                    <label>Upload Photo</label>
                    <input type="file" name="club_photo" accept="image/*">
                  </div>

                

                  <div class="action-buttons">
                    <button type="button" class="btn-delete">Delete</button>
                    <button type="submit" class="btn-update">Update</button>
                  </div>
                </div>
            </form>
        </section>
      </section>

      </div>
    </main>
  </div>
</body>
</html>