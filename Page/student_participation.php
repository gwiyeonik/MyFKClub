<?php
// student_participations.php
session_start();

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userID = intval($_SESSION['user_id']);

// Database connection
$conn = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// 1) Get total points from studentpointsummary if available, otherwise compute
$totalPoints = 0;
$ptsSql = "SELECT totalPoints FROM studentpointsummary WHERE userID = ? LIMIT 1";
$ptsStmt = mysqli_prepare($conn, $ptsSql);
if ($ptsStmt) {
    mysqli_stmt_bind_param($ptsStmt, "i", $userID);
    mysqli_stmt_execute($ptsStmt);
    $ptsResult = mysqli_stmt_get_result($ptsStmt);
    if ($ptsResult && mysqli_num_rows($ptsResult) > 0) {
        $row = mysqli_fetch_assoc($ptsResult);
        $totalPoints = intval($row['totalPoints']);
    }
    mysqli_stmt_close($ptsStmt);
}

if ($totalPoints === 0) {
    // Compute from eventattendance/joined registrations as fallback
    $calcSql = "SELECT COALESCE(SUM(ea.attendancePoints),0) AS sumPoints
                FROM eventattendance ea
                INNER JOIN eventregistration er ON ea.registrationID = er.registrationID
                WHERE er.userID = ?";
    $calcStmt = mysqli_prepare($conn, $calcSql);
    if ($calcStmt) {
        mysqli_stmt_bind_param($calcStmt, "i", $userID);
        mysqli_stmt_execute($calcStmt);
        $calcRes = mysqli_stmt_get_result($calcStmt);
        if ($calcRes && mysqli_num_rows($calcRes) > 0) {
            $r = mysqli_fetch_assoc($calcRes);
            $totalPoints = intval($r['sumPoints']);
        }
        mysqli_stmt_close($calcStmt);
    }
}

// 2) Participation history
$history = [];
$histSql = "SELECT e.eventTitle, e.eventDateStart, ea.attendanceCheckinTime, COALESCE(ea.attendanceStatus, 'Not Recorded') AS attendanceStatus, COALESCE(ea.attendancePoints, 0) AS attendancePoints
            FROM eventregistration er
            INNER JOIN event e ON er.eventID = e.eventID
            LEFT JOIN eventattendance ea ON er.registrationID = ea.registrationID
            WHERE er.userID = ?
            ORDER BY e.eventDateStart DESC";
$histStmt = mysqli_prepare($conn, $histSql);
if ($histStmt) {
    mysqli_stmt_bind_param($histStmt, "i", $userID);
    mysqli_stmt_execute($histStmt);
    $histRes = mysqli_stmt_get_result($histStmt);
    if ($histRes) {
        while ($r = mysqli_fetch_assoc($histRes)) {
            $history[] = $r;
        }
    }
    mysqli_stmt_close($histStmt);
}

// 3) Recognition level based on Table B
$recognition = "Warning/Reminder";
if ($totalPoints >= 80) {
    $recognition = "Outstanding participant";
} elseif ($totalPoints >= 50) {
    $recognition = "Eligible for active student award";
} elseif ($totalPoints >= 20) {
    $recognition = "Eligible for participation certificate";
} else {
    $recognition = "Warning/Reminder";
}

// 4) Ranking among other students
// Compute student's rank using studentpointsummary; treat missing entries as 0
$rank = 1;
$totalStudents = 0;

// Get total students (roleID = 3 assumed)
$stuCountSql = "SELECT COUNT(*) AS total FROM user WHERE roleID = 3";
$stuCountRes = mysqli_query($conn, $stuCountSql);
if ($stuCountRes) {
    $stuRow = mysqli_fetch_assoc($stuCountRes);
    $totalStudents = intval($stuRow['total']);
}

// Count how many students have more points than current (use studentpointsummary)
$greaterSql = "SELECT COUNT(*) AS higher FROM studentpointsummary WHERE totalPoints > ?";
$greaterStmt = mysqli_prepare($conn, $greaterSql);
$higherCount = 0;
if ($greaterStmt) {
    mysqli_stmt_bind_param($greaterStmt, "i", $totalPoints);
    mysqli_stmt_execute($greaterStmt);
    $gRes = mysqli_stmt_get_result($greaterStmt);
    if ($gRes) {
        $gRow = mysqli_fetch_assoc($gRes);
        $higherCount = intval($gRow['higher']);
    }
    mysqli_stmt_close($greaterStmt);
}

// If the current user is not present in studentpointsummary and has >0 points, they may not be counted; adjust using a union
// Final rank is higherCount + 1
$rank = $higherCount + 1;

// 5) Top students list (faculty ranking) - top 10
$topStudents = [];
$topSql = "SELECT u.userID, u.userName, COALESCE(sps.totalPoints,0) AS totalPoints
           FROM user u
           LEFT JOIN studentpointsummary sps ON u.userID = sps.userID
           WHERE u.roleID = 3
           ORDER BY totalPoints DESC, u.userName ASC
           LIMIT 10";
$topRes = mysqli_query($conn, $topSql);
if ($topRes) {
    while ($r = mysqli_fetch_assoc($topRes)) {
        $r['userID'] = 'US' . str_pad($r['userID'], 4, '0', STR_PAD_LEFT);
        $topStudents[] = $r;
    }
}

mysqli_close($conn);

// Render page
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Participation | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <link rel="stylesheet" href="../CSS/participationreport.css">
  <style>
    .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 18px; }
    .stat-card { background:#fff; padding:14px; border-radius:6px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align:center; }
    .stat-card h3{ margin:0; font-size:0.85rem; color:#556; }
    .stat-card .value{ font-size:1.6rem; font-weight:700; margin-top:8px; }
    .panel-grid { display:grid; grid-template-columns: 1fr 360px; gap:18px; }
    .panel { background:#fff; padding:18px; border-radius:6px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px; text-align:left; border-bottom:1px solid rgba(0,0,0,0.06); }
    .empty { color:#999; font-style:italic; padding:18px; }
  </style>
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel"><img src="../Image/fkclub.jpg" alt="FKClub logo"></div>
      <nav class="sidebar-nav">
        <a href="student_dashboard.php" class="sidebar-link">Home</a>
        <a href="student_myclubs.php" class="sidebar-link">My Clubs</a>
        <a href="student_clublist.php" class="sidebar-link">Club List</a>
        <a href="student_events.php" class="sidebar-link">Events</a>
        <a href="student_participation.php" class="sidebar-link active">Participation</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar"><div class="topbar-left"><div class="topbar-title">My Participation</div></div></div>

      <div class="content-area">

        <div class="stat-row">
          <div class="stat-card"><h3>Total Points</h3><div class="value"><?php echo htmlspecialchars($totalPoints); ?></div></div>
          <div class="stat-card"><h3>Events Attended</h3><div class="value"><?php echo count($history); ?></div></div>
          <div class="stat-card"><h3>Faculty Ranking</h3><div class="value"><?php echo $rank; ?>/<?php echo $totalStudents; ?></div></div>
          <div class="stat-card"><h3>Recognition Level</h3><div class="value"><?php echo htmlspecialchars($recognition); ?></div></div>
        </div>

        <div class="panel-grid">
          <div class="panel">
            <h3>Recognition status</h3>
            <div style="margin-top:12px; padding:12px; border:1px solid rgba(0,0,0,0.06); background:#fafafa;">
              <p><strong>Current points:</strong> <?php echo htmlspecialchars($totalPoints); ?></p>
              <ul>
                <li>Below 20 pts → Warning/Reminder</li>
                <li>20–49 pts → Eligible for participation certificate</li>
                <li>50–79 pts → Eligible for active student award</li>
                <li>80+ pts → Outstanding participant</li>
              </ul>
            </div>

            <h3 style="margin-top:18px;">Participation History</h3>
            <?php if (count($history) === 0) { ?>
              <div class="empty">No participation records found.</div>
            <?php } else { ?>
              <table>
                <thead>
                  <tr><th>Event</th><th>Date</th><th>Status</th><th>Points</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($history as $h) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars($h['eventTitle']); ?></td>
                      <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($h['eventDateStart']))); ?></td>
                      <td><?php echo htmlspecialchars(ucfirst($h['attendanceStatus'])); ?></td>
                      <td><?php echo intval($h['attendancePoints']); ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            <?php } ?>
          </div>

          <div class="panel">
            <h3>Faculty Rankings</h3>
            <?php if (count($topStudents) === 0) { ?>
              <div class="empty">No ranking data available.</div>
            <?php } else { ?>
              <table>
                <thead>
                  <tr><th>#</th><th>Name</th><th>Points</th></tr>
                </thead>
                <tbody>
                  <?php $i=1; foreach ($topStudents as $ts) { ?>
                    <tr>
                      <td><?php echo $i++; ?></td>
                      <td><?php echo htmlspecialchars($ts['userName']); ?></td>
                      <td><?php echo intval($ts['totalPoints']); ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            <?php } ?>
          </div>
        </div>

      </div>
    </main>
  </div>
</body>
</html>
