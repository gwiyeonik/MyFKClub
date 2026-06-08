<?php
session_start();

// Security Check: If the user is not logged in, kick them back to the login page
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's name from the session
$current_user = $_SESSION['user_name'];
// User ID for DB queries (preferred)
$current_user_id = $_SESSION['user_id'] ?? null;

// Database connection for dashboard data
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
  $activeClubs = 0;
  $upcomingRegistered = 0;
  $participationPoints = 0;
  $attendanceRate = '0%';
  $upcomingEvents = [];
} else {
  $uid = mysqli_real_escape_string($link, $current_user_id);

  // Active clubs count
  $res = mysqli_query($link, "SELECT COUNT(*) AS total FROM clubmembership WHERE userID = '$uid'");
  $activeClubs = ($res && $row = mysqli_fetch_assoc($res)) ? (int)$row['total'] : 0;

  // Upcoming events the user has registered for
  $res = mysqli_query($link, "SELECT COUNT(*) AS total FROM eventregistration er JOIN event e ON er.eventID = e.eventID WHERE er.userID = '$uid' AND e.eventDateStart >= CURDATE()");
  $upcomingRegistered = ($res && $row = mysqli_fetch_assoc($res)) ? (int)$row['total'] : 0;

  // Participation points (simple count of attended records)
  $res = mysqli_query($link, "SELECT COUNT(*) AS total FROM eventattendance ea JOIN eventregistration er ON ea.registrationID = er.registrationID WHERE er.userID = '$uid'");
  $participationPoints = ($res && $row = mysqli_fetch_assoc($res)) ? (int)$row['total'] : 0;

  // Attendance rate = attended / registered * 100
  $resReg = mysqli_query($link, "SELECT COUNT(*) AS total FROM eventregistration WHERE userID = '$uid'");
  $registeredCount = ($resReg && $r = mysqli_fetch_assoc($resReg)) ? (int)$r['total'] : 0;
  $attendedCount = ($participationPoints);
  $attendanceRate = $registeredCount > 0 ? round(($attendedCount / $registeredCount) * 100) . '%' : '0%';

  // Upcoming events list (global upcoming events)
  $upcomingEvents = [];
  $evRes = mysqli_query($link, "SELECT eventTitle, eventDateStart, eventVenue FROM event WHERE eventDateStart >= CURDATE() ORDER BY eventDateStart ASC LIMIT 3");
  if ($evRes) {
    while ($erow = mysqli_fetch_assoc($evRes)) {
      $upcomingEvents[] = $erow;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/student_dashboard.css">
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>

      <nav class="sidebar-nav">
        <a href="student_dashboard.php" class="sidebar-link active">Home</a>
        <a href="student_myclubs.php" class="sidebar-link">My Clubs</a>
        <a href="student_clublist.php" class="sidebar-link">Club List</a>
        <a href="student_events.php" class="sidebar-link">Events</a>
        <a href="student_participation.php" class="sidebar-link">Participation</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Welcome back, <?php echo htmlspecialchars($current_user); ?></div>
        </div>
        <div class="topbar-right">
          <a href="myProfile.php" class="topbar-button">My Profile</a>
        </div>
      </div>

      <div class="content-area">
        <section class="stats-row">
          <div class="stat-card">
            <div class="stat-label">Active Clubs Memberships</div>
            <div class="stat-value"><?php echo (int)$activeClubs; ?> Active Clubs</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Upcoming Registered Events</div>
            <div class="stat-value"><?php echo (int)$upcomingRegistered; ?> Events</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Participation Points</div>
            <div class="stat-value"><?php echo (int)$participationPoints; ?> Points</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Attendance Rate</div>
            <div class="stat-value"><?php echo htmlspecialchars($attendanceRate); ?></div>
          </div>
        </section>

        <section class="manage-grid">
          <div class="manage-panel" style="width:100%;">
            <div class="section-header">Upcoming Events</div>
            <div style="padding: 18px;">
              <div style="display:flex; gap:24px; justify-content:space-between;">
                <?php if (!empty($upcomingEvents)): ?>
                  <?php foreach ($upcomingEvents as $ev): ?>
                    <div style="background:white; border:1px solid var(--border); box-shadow:var(--shadow); padding:26px; width:30%;">
                      <div style="font-weight:700; margin-bottom:10px;">Event : <?php echo htmlspecialchars($ev['eventTitle']); ?></div>
                      <div style="margin-bottom:8px;">Date : <?php echo htmlspecialchars(date('d M Y', strtotime($ev['eventDateStart']))); ?></div>
                      <div>Venue : <?php echo htmlspecialchars($ev['eventVenue']); ?></div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div style="width:100%; text-align:center; color:var(--muted);">No upcoming events found.</div>
                <?php endif; ?>
              </div>
              <div style="text-align:center; margin-top:18px;">
                <a href="student_events.php" class="btn-add">View more</a>
              </div>
            </div>
          </div>
        </section>
      </div>

    </main>
  </div>
</body>
</html>