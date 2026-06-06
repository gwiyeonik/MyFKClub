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
$resUpcoming = $conn->query("SELECT COUNT(*) as total FROM event WHERE eventDateStart >= CURDATE()");
$upcomingEvents = ($resUpcoming) ? $resUpcoming->fetch_assoc()['total'] : 0;

// Total Participants (Summing the eventParticipants column)
$resParticipants = $conn->query("SELECT SUM(eventParticipants) as total FROM event");
$totalParticipants = ($resParticipants) ? $resParticipants->fetch_assoc()['total'] : 0;

// 3. FETCH POPULAR EVENTS FOR TABLE (Using eventTitle and eventParticipants)
$popQuery = "SELECT eventTitle, eventParticipants FROM event ORDER BY eventParticipants DESC LIMIT 5";
$popResult = $conn->query($popQuery);

// 4. FETCH DATA FOR CHARTS
// Events by Club
$clubQuery = "SELECT c.clubName, COUNT(e.eventID) as eventCount FROM club c LEFT JOIN event e ON c.clubID = e.clubID GROUP BY c.clubID, c.clubName";
$clubResult = $conn->query($clubQuery);
$clubNames = [];
$clubCounts = [];
if ($clubResult && $clubResult->num_rows > 0) {
    while($row = $clubResult->fetch_assoc()) {
        $clubNames[] = $row['clubName'];
        $clubCounts[] = $row['eventCount'];
    }
}

// Participants by Event
$eventPartQuery = "SELECT eventTitle, eventParticipants FROM event ORDER BY eventParticipants DESC";
$eventPartResult = $conn->query($eventPartQuery);
$eventTitles = [];
$participantCounts = [];
if ($eventPartResult && $eventPartResult->num_rows > 0) {
    while($row = $eventPartResult->fetch_assoc()) {
        $eventTitles[] = $row['eventTitle'];
        $participantCounts[] = $row['eventParticipants'];
    }
}

$clubNamesJson = json_encode($clubNames);
$clubCountsJson = json_encode($clubCounts);
$eventTitlesJson = json_encode($eventTitles);
$participantCountsJson = json_encode($participantCounts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <link rel="stylesheet" href="../CSS/events.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      </div>

    <div class="content-area">

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

      <section style="width: calc(100% + 60px); margin-left: 0px; margin-right: 0px; padding: 0; margin-bottom: 30px;">
        <div class="chart-card" style="width: 90%; padding: 30px; border-radius: 0; margin: 0;">
          <div class="chart-title" style="margin-bottom: 20px;">Number of Events Organized by Each Club</div>
          <canvas id="clubEventsChart" style="height: 350px; max-height: 350px;"></canvas>
        </div>
      </section>

      <section style="width: calc(100% + 60px); margin-left: 0px; margin-right: 0px; padding: 0; margin-bottom: 30px;">
        <div class="chart-card" style="width: 90%; padding: 30px; border-radius: 0; margin: 0;">
          <div class="chart-title" style="margin-bottom: 20px;">Number of Participants for Each Events</div>
          <canvas id="participantsChart" style="height: 350px; max-height: 350px;"></canvas>
        </div>
      </section>

      <section class="events-grid-bottom" style="margin-bottom: 40px;">
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
      </section>

      <section style="width: calc(100% + 60px); margin-left: 0px; margin-right: 0px; padding: 0; margin-bottom: 30px;">
        <div class="chart-card" style="width: 90%; padding: 30px; border-radius: 0; margin: 0;">
          <div class="chart-title" style="margin-bottom: 20px;">Participants Trend</div>
          <canvas id="lineChart" style="height: 350px; max-height: 350px;"></canvas>
        </div>
      </section>
    </div>
    </main>
  </div>

  <script>
    // Club Events Bar Chart
    const clubEventsCtx = document.getElementById('clubEventsChart').getContext('2d');
    new Chart(clubEventsCtx, {
      type: 'bar',
      data: {
        labels: <?php echo $clubNamesJson; ?>,
        datasets: [{
          label: 'Number of Events',
          data: <?php echo $clubCountsJson; ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.7)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Participants Bar Chart
    const participantsCtx = document.getElementById('participantsChart').getContext('2d');
    new Chart(participantsCtx, {
      type: 'bar',
      data: {
        labels: <?php echo $eventTitlesJson; ?>,
        datasets: [{
          label: 'Number of Participants',
          data: <?php echo $participantCountsJson; ?>,
          backgroundColor: 'rgba(75, 192, 75, 0.7)',
          borderColor: 'rgba(75, 192, 75, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Line Chart for Participants Trend
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    new Chart(lineCtx, {
      type: 'line',
      data: {
        labels: <?php echo $eventTitlesJson; ?>,
        datasets: [{
          label: 'Participants Trend',
          data: <?php echo $participantCountsJson; ?>,
          borderColor: 'rgba(153, 102, 255, 1)',
          backgroundColor: 'rgba(153, 102, 255, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
</body>
</html>
