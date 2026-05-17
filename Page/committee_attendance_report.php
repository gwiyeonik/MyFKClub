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

  <form class="manual-attendance-form" action="" method="POST">

    <div class="manual-entry-layout">

      <!-- LEFT SIDE: INPUT FORM -->
      <div class="manual-form-left">

        <div class="form-group">
          <label for="student_search">Student ID / Name</label>
          <input 
            type="text" 
            id="student_search" 
            name="student_search" 
            class="manual-input"
            placeholder="Search student ID or name"
            required
          >
        </div>

        <div class="form-group">
          <label for="event_name">Event Name</label>
          <input 
            type="text" 
            id="event_name" 
            name="event_name" 
            class="manual-input"
            placeholder="Search event name"
            required
          >
        </div>

        <div class="form-group">
          <label for="event_date">Event Date</label>
          <input 
            type="date" 
            id="event_date" 
            name="event_date" 
            class="manual-input"
            required
          >
        </div>

        <div class="form-group">
          <label for="checkin_time">Check-in Time</label>
          <input 
            type="text" 
            id="checkin_time" 
            name="checkin_time" 
            class="manual-input readonly-input"
            readonly
          >
        </div>

        <div class="form-group">
          <label for="attendance_status">Attendance Status</label>
          <select id="attendance_status" name="attendance_status" class="filter-select" required>
            <option value="">Select Status</option>
            <option value="present">Present on time</option>
            <option value="late">Late arrival</option>
            <option value="absent">Absent without notice</option>
          </select>
        </div>

        <div class="form-group">
          <label for="is_volunteer">Volunteer / Helper</label>
          <select id="is_volunteer" name="is_volunteer" class="filter-select">
            <option value="no">No</option>
            <option value="yes">Yes</option>
          </select>
        </div>

      </div>

      <!-- RIGHT SIDE: POINTS PREVIEW -->
      <div class="points-preview">
        <div class="points-heading">Points Preview</div>

        <div class="points-result">
          Total Points:
          <span id="total_points">0</span>
        </div>

        <ul>
          <li>Present on time : +10 Points</li>
          <li>Late arrival : +5 Points</li>
          <li>Absent without notice : -10 Points</li>
          <li>Volunteer / helper : +5 Points</li>
        </ul>
      </div>

    </div>

    <div class="attendance-actions">
      <button class="primary-pill" type="submit">Save Attendance</button>
    </div>

  </form>
</div>

  </form>
</div>

        <section class="table-section">
          <div class="table-panel">
            <div class="table-heading">
              Registered Participants
              <button class="edit-btn">Edit</button>
            </div>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Name / ID</th>    
                    <th>Check-in Time</th>
                    <th>Status</th>
                    <th>Volunteer / Helper</th>
                    <th>Points</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                   
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
  <script>
  const attendanceStatus = document.getElementById("attendance_status");
  const volunteerStatus = document.getElementById("is_volunteer");
  const totalPoints = document.getElementById("total_points");
  const checkinTime = document.getElementById("checkin_time");

  function updateCheckinTime() {
    const now = new Date();
    checkinTime.value = now.toLocaleString();
  }

  function calculatePoints() {
    let points = 0;

    if (attendanceStatus.value === "present") {
      points += 10;
    } else if (attendanceStatus.value === "late") {
      points -= 5;
    } else if (attendanceStatus.value === "absent") {
      points -= 10;
    }

    if (volunteerStatus.value === "yes") {
      points += 5;
    }

    totalPoints.textContent = points;
  }

  updateCheckinTime();

  attendanceStatus.addEventListener("change", calculatePoints);
  volunteerStatus.addEventListener("change", calculatePoints);
</script>
</body>
</html>
