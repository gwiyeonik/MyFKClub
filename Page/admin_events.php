<?php
// admin_events.php
session_start();

// 1. DATABASE CONNECTION
$host = "localhost";
$user = "root";
$pass = "";
$db   = "myfkclub"; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. FETCH STATS DATA (Using your exact column names)
// Total Events
$resTotal = $conn->query("SELECT COUNT(*) as total FROM event");
$totalEvents = ($resTotal) ? $resTotal->fetch_assoc()['total'] : 0;

// Upcoming Events (Using eventDate)
$resUpcoming = $conn->query("SELECT COUNT(*) as total FROM event WHERE eventDate >= CURDATE()");
$upcomingEvents = ($resUpcoming) ? $resUpcoming->fetch_assoc()['total'] : 0;

// Total Participants (Summing the eventParticipants column)
$resParticipants = $conn->query("SELECT SUM(eventParticipants) as total FROM event");
$totalParticipants = ($resParticipants) ? $resParticipants->fetch_assoc()['total'] : 0;

// 3. FETCH POPULAR EVENTS FOR TABLE (Using eventTitle and eventParticipants)
$popQuery = "SELECT eventTitle, eventParticipants FROM event ORDER BY eventParticipants DESC LIMIT 5";
$popResult = $conn->query($popQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <link rel="stylesheet" href="../CSS/events.css">
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
        <a href="admin_student_clubs.php" class="sidebar-link">Student Clubs</a>
        <a href="admin_events.php" class="sidebar-link active">Events</a>
        <a href="admin_participation_reports.php" class="sidebar-link">Participation Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Events</div>
        </div>
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

    <div class="content-area">

     <div class="search-bar-wrap">
        <input type="text" class="search-input" placeholder="Search Event/EventID">
        <div class="filter-row">
            <select class="filter-select" name="filter_club">
                <option value="">Filter by club</option>
                <option value="club_a">Club A</option>
            </select>
            
            <select class="filter-select" name="filter_semester">
                <option value="">Semester</option>
                <option value="sem1">1st Semester</option>
            </select>
            
            <select class="filter-select" name="filter_status">
                <option value="">Event Status</option>
                <option value="active">Active</option>
            </select>
            
            <button class="primary-pill" type="button">Apply Filter</button>
            <button class="secondary-pill" type="button">Export Report</button>
        </div>
    </div>

      <section class="stats-row">
        <div class="stat-card">
          <div class="stat-label">Total Events</div>
          <div class="stat-value"><?php echo $totalEvents; ?></div>
        </div>

        <div class="stat-card">
          <div class="stat-label">Upcoming Events</div>
          <div class="stat-value"><?php echo $upcomingEvents; ?></div>
        </div>

        <div class="stat-card">
          <div class="stat-label">Total Participants</div>
          <div class="stat-value"><?php echo $totalParticipants; ?></div>
        </div>

        <div class="stat-card">
          <div class="stat-label">Fully Booked Events</div>
          <div class="stat-value">0</div>
        </div>
      </section>

      <section class="events-grid-main">
        <div class="chart-card ">
          <div class="chart-title">Number of Events Organized by Each Club</div>
          <div class="chart-placeholder">&lt;&lt; bar chart &gt;&gt;</div>
        </div>
        <div class="chart-card">
          <div class="chart-title">Number of Participants for Each Events</div>
          <div class="chart-placeholder">&lt;&lt; bar chart &gt;&gt;</div>
        </div>
      </section>

      <section class="events-grid-bottom">
        <div class="chart-card">
          <div class="chart-title">Popular Events Based on Registration Count</div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Events</th>
                  <th>Number of Registrations</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($popResult && $popResult->num_rows > 0): ?>
                <?php while($row = $popResult->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['eventTitle']); ?></td>
                    <td><?php echo $row['eventParticipants']; ?></td>
                  </tr>
                <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="2" class="empty-cell">No events found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="chart-card">
          <div class="chart-title">Number of Participants for Each Events</div>
          <div class="chart-placeholder">&lt;&lt; line graph &gt;&gt;</div>
        </div>
      </section>
    </div>
    </main>
  </div>
</body>
</html>
