<?php
require_once "committee_attendance_api.php";
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
        <a href="committee_view_clubs.php" class="sidebar-link">View Clubs</a>
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
        <?php if (!empty($message)) { ?>
          <div class="attendance-message">
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php } ?>



<form method="GET" class="event-selector-form">
  <label for="eventID">Event Name</label>

  <select 
    name="eventID" 
    id="eventID" 
    class="event-selector"
    onchange="this.form.submit()"
  >
    <?php foreach ($events as $event) { ?>
      <option 
        value="<?php echo $event['eventID']; ?>"
        <?php if ($selectedEventID == $event['eventID']) echo "selected"; ?>
      >
        <?php echo htmlspecialchars($event['eventTitle']); ?>
      </option>
    <?php } ?>
  </select>
</form>

        <section class="event-summary">
          <div class="summary-card">
            <div class="summary-title">Selected Event Summary</div>
            <div class="summary-meta">
  <?php if ($eventInfo) { ?>
    <?php echo htmlspecialchars($eventInfo['eventTitle']); ?>
    &nbsp; | &nbsp;
    <?php echo htmlspecialchars(date('Y-m-d', strtotime($eventInfo['eventDateStart']))); ?>
    to
    <?php echo htmlspecialchars(date('Y-m-d', strtotime($eventInfo['eventDateEnd']))); ?>
    &nbsp; | &nbsp;
    <?php echo htmlspecialchars($eventInfo['eventVenue']); ?>
    &nbsp; | &nbsp;
    Registered count:
    <?php echo htmlspecialchars($eventInfo['registeredCount']); ?>
  <?php } else { ?>
    No event selected.
  <?php } ?>
</div>
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
              <button class="primary-pill" type="button">Generate QR</button>
              <button class="secondary-pill" type="button">Refresh QR</button>
            </div>
          </div>

          <div class="attendance-card attendance-manual">
            <div class="attendance-title">Manual Attendance Entry</div>

            <form class="manual-attendance-form" action="" method="POST">
              <div class="manual-entry-layout">
                <div class="manual-form-left">
                  <div class="form-group">
                    <label for="student_search">Student Name</label>
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
    class="manual-input readonly-input"
    value="<?php echo $eventInfo ? htmlspecialchars($eventInfo['eventTitle']) : ''; ?>"
    readonly
  >

  <input 
    type="hidden" 
    name="event_id" 
    value="<?php echo $selectedEventID; ?>"
  >
</div>

                 <div class="form-group">
  <label for="event_date">Event Date</label>

  <input 
    type="text" 
    id="event_date" 
    class="manual-input readonly-input"
    value="<?php echo $eventInfo ? htmlspecialchars(date('d/m/Y', strtotime($eventInfo['eventDateStart']))) : ''; ?>"
    readonly
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
                      <option value="0">No</option>
                      <option value="1">Yes</option>
                    </select>
                  </div>
                </div>

                <div class="points-preview">
                  <div class="points-heading">Points Preview</div>
                  <div class="points-result">
                    Total Points:
                    <span id="total_points">0</span>
                  </div>
                  <ul>
                    <li>Present on time : +10 Points</li>
                    <li>Late arrival : -5 Points</li>
                    <li>Absent without notice : -10 Points</li>
                    <li>Volunteer / helper : +5 Points</li>
                  </ul>
                </div>
              </div>

              <div class="attendance-actions">
                <button class="primary-pill" type="submit" name="save_manual_attendance">Save Attendance</button>
              </div>
            </form>
          </div>
        </section>

        <section class="table-section">
          <div class="table-panel">
            <div class="table-heading">
              Registered Participants
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Check-in Time</th>
                    <th>Status</th>
                    <th>Volunteer / Helper</th>
                    <th>Points</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody>
                <?php
                if ($selectedEventID > 0) {
                    $sql = "SELECT
                                er.registrationID,
                                er.userID,
                                u.userName,
                                ea.attendanceCheckinTime,
                                ea.attendanceStatus,
                                ea.attendanceIsVolunteer,
                                ea.attendancePoints
                            FROM eventregistration er
                            INNER JOIN `user` u ON er.userID = u.userID
                            LEFT JOIN eventattendance ea ON er.registrationID = ea.registrationID
                            WHERE er.eventID = ?
                            ORDER BY u.userName ASC";

                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $selectedEventID);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $formID = "attendanceForm" . $row['registrationID'];
                ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['userName']); ?></td>
                            <td><?php echo htmlspecialchars($row['userID']); ?></td>
                            <td>
                              <?php
                                echo !empty($row['attendanceCheckinTime'])
                                  ? htmlspecialchars($row['attendanceCheckinTime'])
                                  : "-";
                              ?>
                            </td>
                            <td>
                              <select name="attendanceStatus" class="table-select" form="<?php echo $formID; ?>">
                                <option value="">Select</option>
                                <option value="present" <?php if ($row['attendanceStatus'] === "present") echo "selected"; ?>>Present</option>
                                <option value="late" <?php if ($row['attendanceStatus'] === "late") echo "selected"; ?>>Late</option>
                                <option value="absent" <?php if ($row['attendanceStatus'] === "absent") echo "selected"; ?>>Absent</option>
                              </select>
                            </td>
                            <td>
                              <select name="attendanceIsVolunteer" class="table-select" form="<?php echo $formID; ?>">
                                <option value="0" <?php if (intval($row['attendanceIsVolunteer']) === 0) echo "selected"; ?>>No</option>
                                <option value="1" <?php if (intval($row['attendanceIsVolunteer']) === 1) echo "selected"; ?>>Yes</option>
                              </select>
                            </td>
                            <td>
                              <?php
                                echo $row['attendancePoints'] !== null
                                  ? htmlspecialchars($row['attendancePoints'])
                                  : "0";
                              ?>
                            </td>
                            <td>
  <form method="POST" action="" id="<?php echo $formID; ?>">
    <input type="hidden" name="registrationID" value="<?php echo $row['registrationID']; ?>">
    <input type="hidden" name="userID" value="<?php echo $row['userID']; ?>">

    <button type="submit" name="update_attendance" class="update-btn">
      Update
    </button>

    <button 
      type="submit" 
      name="delete_attendance" 
      class="delete-btn"
      onclick="return confirm('Are you sure you want to delete this attendance record?');"
    >
      Delete
    </button>
  </form>
</td>
                          </tr>
                <?php
                        }
                    } else {
                ?>
                      <tr>
                        <td colspan="7" class="empty-cell">No registered participants found for this event.</td>
                      </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                      <td colspan="7" class="empty-cell">No event found. Please create an event first.</td>
                    </tr>
                <?php
                }
                ?>
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

      if (volunteerStatus.value === "1") {
        points += 5;
      }

      totalPoints.textContent = points;
    }

    if (attendanceStatus && volunteerStatus && totalPoints && checkinTime) {
      updateCheckinTime();
      attendanceStatus.addEventListener("change", calculatePoints);
      volunteerStatus.addEventListener("change", calculatePoints);
    }
  </script>
</body>
</html>
