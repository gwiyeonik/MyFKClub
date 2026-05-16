<?php
session_start();
// Committee attendance report page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Committee Attendance Report | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <link rel="stylesheet" href="../CSS/committee_attendance_report.css">
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>
      <nav class="sidebar-nav">
        <a href="committee_dashboard.php" class="sidebar-link">Home</a>
        <a href="committee_club_details.php" class="sidebar-link">Manage Clubs</a>
        <a href="committee_manage_events.php" class="sidebar-link">Manage Events</a>
        <a href="committee_members.php" class="sidebar-link">Members</a>
        <a href="committee_attendance_report.php" class="sidebar-link active">Attendance</a>
        <a href="committee_participation_report.php" class="sidebar-link">Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Record Attendance</div>
        </div>
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

      <div class="content-area">
        <section class="event-summary">
          <div class="summary-card">
            <div class="summary-title">Selected Event Summary</div>
            <div class="summary-meta">Event name &nbsp; | &nbsp; date &nbsp; | &nbsp; venue &nbsp; | &nbsp; registered count</div>
          </div>
        </section>

        <section class="attendance-grid">
          <div class="attendance-card">
            <div class="attendance-title">Attendance QR</div>
            <div class="qr-placeholder">
              &lt;&lt;image&gt;&gt;<br>
              Event QR Code
            </div>
            <div class="attendance-actions">
              <button class="primary-pill">Generate QR</button>
              <button class="secondary-pill">Refresh QR</button>
            </div>
          </div>

          <div class="attendance-card attendance-manual">
            <div class="attendance-title">Manual Attendance Entry</div>
            <input type="text" name="student_search" placeholder="Student ID / Name">
            <div class="manual-actions">
              <select class="filter-select">
                <option>Attendance Status</option>
                <option>Present</option>
                <option>Late</option>
                <option>Absent</option>
              </select>
              <select class="filter-select">
                <option>Volunteer / Helper</option>
                <option>Volunteer</option>
                <option>Helper</option>
              </select>
            </div>
            <div class="points-preview">
              <div class="points-heading">Points Preview</div>
              <ul>
                <li>Present on time : +5 Points</li>
                <li>Late arrival : -5 Points</li>
                <li>Absent without notice : -10 Points</li>
                <li>Volunteer / helper : +5 Points</li>
              </ul>
            </div>
            <div class="attendance-actions">
              <button class="primary-pill">Save Attendance</button>
            </div>
          </div>
        </section>

        <section class="table-section">
          <div class="table-panel">
            <div class="table-heading">
              &lt;&lt;table&gt;&gt; Registered Participants
              <button class="edit-btn">Edit</button>
            </div>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Check-in Time</th>
                    <th>Status</th>
                    <th>Volunteer</th>
                    <th>Points</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="7" class="empty-cell">Student &nbsp; | &nbsp; ID &nbsp; | &nbsp; Check-in Time &nbsp; | &nbsp; Status &nbsp; | &nbsp; Volunteer &nbsp; | &nbsp; Points &nbsp; | &nbsp; Action</td>
                  </tr>
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
