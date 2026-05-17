<?php
// committee_participation_report.php
session_start();
// Add authentication logic as needed.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Committee Participation Report | MyFKClub Committee</title>

  <link rel="stylesheet" href="../CSS/dashboard.css">
  <link rel="stylesheet" href="../CSS/committee_participation_report.css">
</head>
<body>

  <div class="dashboard-shell">

    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>

      <nav class="sidebar-nav">
        <a href="committee_dashboard.php" class="sidebar-link">Home</a>
        <a href="committee_club_details.php" class="sidebar-link">Manage Clubs</a>
        <a href="committee_manage_events.php" class="sidebar-link">Manage Events</a>
        <a href="committee_members.php" class="sidebar-link">Members</a>
        <a href="committee_attendance_report.php" class="sidebar-link">Attendance</a>
        <a href="committee_participation_report.php" class="sidebar-link active">Reports</a>
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="dashboard-main">

      <!-- TOPBAR -->
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Participation Reports</div>
        </div>

        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

      <!-- CONTENT -->
      <div class="content-area">

        <!-- SEARCH -->
        <div class="search-bar-wrap">
          <input class="search-input" type="search" placeholder="Search Events/EventID">
        </div>

        <div class="filter-row">
          <select class="filter-select" name="event_filter">
            <option value="">Event</option>
            <option value="unity_workshop">Unity Workshop</option>
            <option value="game_jam">Game Jam</option>
            <option value="club_talk">Club Talk</option>
          </select>

          <select class="filter-select" name="semester_filter">
            <option value="">Semester</option>
            <option value="sem1">1st Semester</option>
            <option value="sem2">2nd Semester</option>
            <option value="sem3">3rd Semester</option>
          </select>

          <button class="secondary-pill" type="button">Export Report</button>
        </div>

        <!-- TOP CARDS -->
        <section class="committee-stats-row">

          <div class="committee-card">
            <div class="committee-card-title">Total Club Participation</div>
            <div class="committee-card-meta">
              Registered&nbsp;&nbsp; | &nbsp;&nbsp;Present&nbsp;&nbsp; | &nbsp;&nbsp;Late&nbsp;&nbsp; | &nbsp;&nbsp;Absent
            </div>
          </div>

          <div class="committee-card">
            <div class="committee-card-title">Avg Attendance Rate Per Club Events</div>
          </div>

          <div class="committee-card">
            <div class="committee-card-title">Most Active Members & Top Members by Points</div>
          </div>

        </section>

        <!-- CHARTS -->
        <section class="committee-charts-row">

          <div class="chart-card">
            <div class="chart-title">Club Attendance Trend</div>
            <div class="chart-placeholder">&lt;&lt;bar chart&gt;&gt;</div>
          </div>

          <div class="chart-card">
            <div class="chart-title">Points Distribution</div>
            <div class="chart-placeholder">&lt;&lt;bar chart&gt;&gt;</div>
          </div>

        </section>

        <!-- CLUB MEMBER PARTICIPATION -->
        <section class="table-section">
          <div class="table-panel large-table-panel">
            <div class="table-heading">&lt;&lt;table&gt;&gt; Club Member Participation</div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Student</th>
                    <th>Event</th>
                    <th>Attendance Status</th>
                    <th>Volunteer</th>
                    <th>Points</th>
                    <th>Recognition</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="6" class="table-placeholder">
                      Student&nbsp;&nbsp; | &nbsp;&nbsp;Event&nbsp;&nbsp; | &nbsp;&nbsp;Attendance Status&nbsp;&nbsp; | &nbsp;&nbsp;Volunteer&nbsp;&nbsp; | &nbsp;&nbsp;Points&nbsp;&nbsp; | &nbsp;&nbsp;Recognition
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- BOTTOM SECTION -->
        <section class="committee-bottom-grid">

          <div class="table-panel small-table-panel">
            <div class="table-heading">&lt;&lt;table&gt;&gt; Most Active Members</div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Rank</th>
                    <th>Student</th>
                    <th>Event Attended</th>
                    <th>Total Points</th>
                    <th>Recognition Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="5" class="table-placeholder">
                      Rank&nbsp;&nbsp; | &nbsp;&nbsp;Student&nbsp;&nbsp; | &nbsp;&nbsp;Event Attended&nbsp;&nbsp; | &nbsp;&nbsp;Total Points&nbsp;&nbsp; | &nbsp;&nbsp;Recognition Status
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="engagement-card">
            <div class="engagement-title">Engagement Summary</div>
            <div class="engagement-content">
              Participation Trend&nbsp;&nbsp; | &nbsp;&nbsp;Active Members
            </div>
          </div>

        </section>

      </div>
    </main>
  </div>

</body>
</html>