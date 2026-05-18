<?php
session_start();

// Security Check: If the user is not logged in, kick them back to the login page
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's name from the session
$current_user = $_SESSION['user_name'];
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
        
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Welcome back, <?php echo htmlspecialchars($current_user); ?></div>
        </div>
        <a href="myProfile.php" class="topbar-button">My Profile</a>
      </div>

      </main>
  </div>
</body>
</html>