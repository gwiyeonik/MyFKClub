<?php 
session_start(); 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'committee') {
    header('Location: login.php');
    exit;
}

$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    die("Database connection failed: " . mysqli_connect_error());
}

$position = $_SESSION['position'] ?? 'Committee Member';
$userName = $_SESSION['user_name'] ?? 'username';
$userID = $_SESSION['user_id'];
$currentPage = basename($_SERVER['PHP_SELF']);

$safeUserID = mysqli_real_escape_string($link, $userID);

$managedClubSubquery = "
    SELECT DISTINCT cm.clubID
    FROM clubmembership cm
    INNER JOIN clubcommittee cc ON cm.membershipID = cc.membershipID
    WHERE cm.userID = '$safeUserID'
";

function getSingleValue($link, $sql) {
    $result = mysqli_query($link, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    }
    return 0;
}

$managedClubs = getSingleValue($link, "
    SELECT COUNT(DISTINCT clubID) AS total
    FROM ($managedClubSubquery) AS managed
");

$upcomingEvents = getSingleValue($link, "
    SELECT COUNT(*) AS total
    FROM event
    WHERE clubID IN ($managedClubSubquery)
    AND (eventStatus = 'Upcoming' OR eventDateStart >= CURDATE())
");

$registeredParticipants = getSingleValue($link, "
    SELECT COUNT(*) AS total
    FROM eventregistration er
    INNER JOIN event e ON er.eventID = e.eventID
    WHERE e.clubID IN ($managedClubSubquery)
");

$attendanceRecorded = getSingleValue($link, "
    SELECT COUNT(*) AS total
    FROM eventattendance ea
    INNER JOIN eventregistration er ON ea.registrationID = er.registrationID
    INNER JOIN event e ON er.eventID = e.eventID
    WHERE e.clubID IN ($managedClubSubquery)
");

$events = [];
$eventResult = mysqli_query($link, "
    SELECT e.eventTitle, c.clubName, e.eventDateStart, e.eventStatus
    FROM event e
    INNER JOIN club c ON e.clubID = c.clubID
    WHERE e.clubID IN ($managedClubSubquery)
    AND (e.eventStatus = 'Upcoming' OR e.eventDateStart >= CURDATE())
    ORDER BY e.eventDateStart ASC
    LIMIT 5
");

if ($eventResult) {
    while ($row = mysqli_fetch_assoc($eventResult)) {
        $events[] = $row;
    }
}

$clubs = [];
$clubResult = mysqli_query($link, "
    SELECT c.clubName, c.clubStatus, cc.committeePosition
    FROM clubmembership cm
    INNER JOIN clubcommittee cc ON cm.membershipID = cc.membershipID
    INNER JOIN club c ON cm.clubID = c.clubID
    WHERE cm.userID = '$safeUserID'
    ORDER BY c.clubName ASC
");

if ($clubResult) {
    while ($row = mysqli_fetch_assoc($clubResult)) {
        $clubs[] = $row;
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Dashboard | MyFKClub</title>
    <link rel="stylesheet" href="../CSS/committee.css">
</head>

<body>
<div class="dashboard-shell">

    <aside class="dashboard-sidebar">
        <div class="brand-panel">
            <img src="../Image/fkclub.jpg" alt="FKClub Logo">
        </div>

        <nav class="sidebar-nav">
            <a href="committee_dashboard.php" class="sidebar-link <?php echo $currentPage == 'committee_dashboard.php' ? 'active' : ''; ?>">Home</a>
            <a href="committee_view_clubs.php" class="sidebar-link <?php echo $currentPage == 'committee_view_clubs.php' ? 'active' : ''; ?>">View Clubs</a>
            <a href="committee_manage_events.php" class="sidebar-link <?php echo $currentPage == 'committee_manage_events.php' ? 'active' : ''; ?>">Manage Events</a>
            <a href="committee_attendance_report.php" class="sidebar-link <?php echo $currentPage == 'committee_attendance_report.php' ? 'active' : ''; ?>">Attendance</a>
            <a href="committee_participation_report.php" class="sidebar-link <?php echo $currentPage == 'committee_participation_report.php' ? 'active' : ''; ?>">Reports</a>
        </nav>
    </aside>

    <main class="dashboard-main">

        <header class="topbar">
            <div class="topbar-left">
                <h1 class="topbar-title">
                    Welcome back, <?php echo htmlspecialchars($userName); ?><br>
                    Position: <?php echo htmlspecialchars($position); ?>
                </h1>
            </div>
            <a href="logout.php" class="topbar-button">Logout</a>
        </header>

        <div class="content-area">

            <section class="stats-row">
                <div class="stat-card">
                    Managed Clubs
                    <div style="font-size:1.8rem;margin-top:12px;color:var(--text);">
                        <?php echo $managedClubs; ?>
                    </div>
                </div>

                <div class="stat-card">
                    Upcoming Events
                    <div style="font-size:1.8rem;margin-top:12px;color:var(--text);">
                        <?php echo $upcomingEvents; ?>
                    </div>
                </div>

                <div class="stat-card">
                    Registered Participants
                    <div style="font-size:1.8rem;margin-top:12px;color:var(--text);">
                        <?php echo $registeredParticipants; ?>
                    </div>
                </div>

                <div class="stat-card">
                    Attendance Recorded
                    <div style="font-size:1.8rem;margin-top:12px;color:var(--text);">
                        <?php echo $attendanceRecorded; ?>
                    </div>
                </div>
            </section>

            <section>
                <div class="content-panel">
                    <h2 class="panel-header-left">Upcoming Events</h2>

                    <div class="table-columns">
                        <span>Event Name</span>
                        <span>Club</span>
                        <span>Date</span>
                        <span>Status</span>
                    </div>

                    <?php if (empty($events)): ?>
                        <div class="event-body">
                            <p style="color: var(--muted);">No upcoming events found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <div class="table-columns">
                                <span><?php echo htmlspecialchars($event['eventTitle']); ?></span>
                                <span><?php echo htmlspecialchars($event['clubName']); ?></span>
                                <span><?php echo htmlspecialchars($event['eventDateStart']); ?></span>
                                <span><?php echo htmlspecialchars($event['eventStatus']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="panel-footer">
                        <button class="btn-primary" onclick="window.location.href='committee_manage_events.php'">
                            Manage Events
                        </button>
                    </div>
                </div>
            </section>

            <br>

            <section>
                <div class="content-panel">
                    <h2 class="panel-header-left">My Managed Clubs</h2>

                    <div class="table-columns">
                        <span>Club Name</span>
                        <span>Position</span>
                        <span>Status</span>
                    </div>

                    <?php if (empty($clubs)): ?>
                        <div class="event-body">
                            <p style="color: var(--muted);">No managed clubs found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($clubs as $club): ?>
                            <div class="table-columns">
                                <span><?php echo htmlspecialchars($club['clubName']); ?></span>
                                <span><?php echo htmlspecialchars($club['committeePosition']); ?></span>
                                <span><?php echo htmlspecialchars($club['clubStatus']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </section>

        </div>
    </main>
</div>
</body>
</html>