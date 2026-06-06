<?php
// admin_dashboard_api.php
session_start();
header('Content-Type: application/json');

// 1. Authentication Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// 2. Database Connection
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

$action = $_GET['action'] ?? '';

// 3. Action: Fetch Dashboard Metrics & Recent Users
if ($action === 'get_dashboard_data') {
    
    // Count total students (roleID = 3)
    $studentCountResult = mysqli_query($link, "SELECT COUNT(*) as total FROM user WHERE roleID = 3");
    if (!$studentCountResult) {
        $studentCountResult = mysqli_query($link, "SELECT COUNT(*) as total FROM user");
    }
    $totalStudents = $studentCountResult ? (int)mysqli_fetch_assoc($studentCountResult)['total'] : 0;

    // Count all clubs in faculty
    $clubTotalResult = mysqli_query($link, "SELECT COUNT(*) as total FROM club");
    $totalClubs = $clubTotalResult ? (int)mysqli_fetch_assoc($clubTotalResult)['total'] : 0;

    // Count active clubs
    $activeClubResult = mysqli_query($link, "SELECT COUNT(*) as total FROM club WHERE LOWER(TRIM(clubStatus)) = 'active'");
    $totalActiveClubs = $activeClubResult ? (int)mysqli_fetch_assoc($activeClubResult)['total'] : 0;

    // Count students involved in clubs
    $studentsInClubsResult = mysqli_query($link, "SELECT COUNT(DISTINCT userID) as total FROM clubmembership");
    $totalStudentsInClubs = $studentsInClubsResult ? (int)mysqli_fetch_assoc($studentsInClubsResult)['total'] : 0;

    // Count upcoming events
    $eventCountResult = mysqli_query($link, "SELECT COUNT(*) as total FROM event WHERE eventStatus = 'Upcoming'");
    $totalEvents = $eventCountResult ? (int)mysqli_fetch_assoc($eventCountResult)['total'] : 0;

    // --- FIXED: DYNAMIC ATTENDANCE RATE CALCULATION LOGIC ---
    // Updated to use your exact column name: attendanceStatus
    $attendanceQuery = "SELECT 
                            COUNT(*) as total_records,
                            SUM(CASE WHEN attendanceStatus = 'Present' THEN 1 ELSE 0 END) as total_present
                        FROM eventattendance";

    $attendanceResult = mysqli_query($link, $attendanceQuery);
    $attendanceData = $attendanceResult ? mysqli_fetch_assoc($attendanceResult) : null;

    $totalRecords = $attendanceData ? (int)$attendanceData['total_records'] : 0;
    $totalPresent = $attendanceData ? (int)$attendanceData['total_present'] : 0;

    // Calculate percentage safely to prevent division-by-zero crash if table starts empty
    if ($totalRecords > 0) {
        $avgAttendance = round(($totalPresent / $totalRecords) * 100) . '%';
    } else {
        $avgAttendance = '0%'; // Default fallback display state
    }
    // --- END OF DYNAMIC LOGIC ---

    // Build distribution data for students-per-club chart
    $distributionQuery = "SELECT c.clubName, COUNT(cm.userID) AS members
                          FROM club c
                          LEFT JOIN clubmembership cm ON c.clubID = cm.clubID
                          GROUP BY c.clubID, c.clubName
                          ORDER BY members DESC, c.clubName ASC";
    $distributionResult = mysqli_query($link, $distributionQuery);

    $studentsPerClub = [];
    if ($distributionResult) {
        while ($row = mysqli_fetch_assoc($distributionResult)) {
            $studentsPerClub[] = [
                'clubName' => $row['clubName'] ?: 'Unnamed Club',
                'members' => (int)$row['members']
            ];
        }
    }

    // Fetch recent 5 registrations
    $recentUsersQuery = "SELECT userID, userName, userEmail, roleID FROM user ORDER BY userID DESC LIMIT 5";
    $recentUsersResult = mysqli_query($link, $recentUsersQuery);
    
    $recentUsers = [];
    if ($recentUsersResult) {
        while ($row = mysqli_fetch_assoc($recentUsersResult)) {
            $recentUsers[] = $row;
        }
    }

    // Close connection cleanly
    mysqli_close($link);

    // Return everything as a unified JSON response
    echo json_encode([
        'success' => true,
        'metrics' => [
            'totalStudents' => $totalStudents,
            'totalClubs' => $totalClubs,
            'totalActiveClubs' => $totalActiveClubs,
            'totalStudentsInClubs' => $totalStudentsInClubs,
            'totalEvents' => $totalEvents,
            'avgAttendance' => $avgAttendance
        ],
        'studentsPerClub' => $studentsPerClub,
        'recentUsers' => $recentUsers
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
mysqli_close($link);
exit;