<?php
// committee_participation_api.php
session_start();
header('Content-Type: application/json');

// Authentication Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'committee') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';

// Action: Fetch participation data
if ($action === 'get_participation_data') {
    
    // Count total students registered in any event
    $totalStudentsQuery = "SELECT COUNT(DISTINCT er.userID) as total FROM eventregistration er";
    $totalStudentsResult = mysqli_query($conn, $totalStudentsQuery);
    $totalStudents = $totalStudentsResult ? (int)mysqli_fetch_assoc($totalStudentsResult)['total'] : 0;

    // Count total clubs
    $totalClubsQuery = "SELECT COUNT(*) as total FROM club";
    $totalClubsResult = mysqli_query($conn, $totalClubsQuery);
    $totalClubs = $totalClubsResult ? (int)mysqli_fetch_assoc($totalClubsResult)['total'] : 0;

    // Count total events
    $totalEventsQuery = "SELECT COUNT(*) as total FROM event";
    $totalEventsResult = mysqli_query($conn, $totalEventsQuery);
    $totalEvents = $totalEventsResult ? (int)mysqli_fetch_assoc($totalEventsResult)['total'] : 0;

    // Calculate attendance rate
    $attendanceQuery = "SELECT 
                            COUNT(*) as total_records,
                            SUM(CASE WHEN attendanceStatus = 'present' THEN 1 ELSE 0 END) as total_present
                        FROM eventattendance";
    $attendanceResult = mysqli_query($conn, $attendanceQuery);
    $attendanceData = $attendanceResult ? mysqli_fetch_assoc($attendanceResult) : null;
    
    $totalRecords = $attendanceData ? (int)$attendanceData['total_records'] : 0;
    $totalPresent = $attendanceData ? (int)$attendanceData['total_present'] : 0;
    $avgAttendance = ($totalRecords > 0) ? round(($totalPresent / $totalRecords) * 100) : 0;

    // Get club participation data (attendance by club)
    $clubParticipationQuery = "SELECT 
                                    c.clubName,
                                    COUNT(DISTINCT er.userID) as registered,
                                    SUM(CASE WHEN ea.attendanceStatus = 'present' THEN 1 ELSE 0 END) as present,
                                    SUM(CASE WHEN ea.attendanceStatus = 'late' THEN 1 ELSE 0 END) as late,
                                    SUM(CASE WHEN ea.attendanceStatus = 'absent' THEN 1 ELSE 0 END) as absent
                                FROM club c
                                LEFT JOIN event e ON c.clubID = e.clubID
                                LEFT JOIN eventregistration er ON e.eventID = er.eventID
                                LEFT JOIN eventattendance ea ON er.registrationID = ea.registrationID
                                GROUP BY c.clubID, c.clubName
                                ORDER BY registered DESC
                                LIMIT 8";
    
    $clubParticipationResult = mysqli_query($conn, $clubParticipationQuery);
    $clubParticipation = [];
    $clubNames = [];
    $registeredCounts = [];
    $presentCounts = [];
    $lateCounts = [];
    $absentCounts = [];

    if ($clubParticipationResult) {
        while ($row = mysqli_fetch_assoc($clubParticipationResult)) {
            $clubParticipation[] = $row;
            $clubNames[] = $row['clubName'] ?: 'Unnamed';
            $registeredCounts[] = (int)$row['registered'];
            $presentCounts[] = (int)$row['present'];
            $lateCounts[] = (int)$row['late'];
            $absentCounts[] = (int)$row['absent'];
        }
    }

    // Get student distribution across clubs
    $distributionQuery = "SELECT c.clubName, COUNT(DISTINCT cm.userID) as members
                          FROM club c
                          LEFT JOIN clubmembership cm ON c.clubID = cm.clubID
                          GROUP BY c.clubID, c.clubName
                          ORDER BY members DESC
                          LIMIT 10";
    
    $distributionResult = mysqli_query($conn, $distributionQuery);
    $studentsPerClub = [];
    $clubDistributionLabels = [];
    $clubDistributionData = [];

    if ($distributionResult) {
        while ($row = mysqli_fetch_assoc($distributionResult)) {
            $studentsPerClub[] = $row;
            $clubDistributionLabels[] = $row['clubName'] ?: 'Unnamed';
            $clubDistributionData[] = (int)$row['members'];
        }
    }

    // Get most active members (by attendance count)
    $activeMembersQuery = "SELECT 
                                u.userID,
                                u.userName,
                                COUNT(DISTINCT ea.attendanceID) as events_attended,
                                SUM(COALESCE(ea.attendancePoints, 0)) as total_points
                            FROM user u
                            LEFT JOIN eventregistration er ON u.userID = er.userID
                            LEFT JOIN eventattendance ea ON er.registrationID = ea.registrationID
                            WHERE u.roleID = 3
                            GROUP BY u.userID, u.userName
                            HAVING events_attended > 0
                            ORDER BY total_points DESC, events_attended DESC
                            LIMIT 10";
    
    $activeMembersResult = mysqli_query($conn, $activeMembersQuery);
    $activeMembers = [];

    if ($activeMembersResult) {
        while ($row = mysqli_fetch_assoc($activeMembersResult)) {
            $activeMembers[] = [
                'userID' => 'US' . str_pad($row['userID'], 4, '0', STR_PAD_LEFT),
                'userName' => $row['userName'],
                'events_attended' => (int)$row['events_attended'],
                'total_points' => (int)$row['total_points']
            ];
        }
    }

    // Get all member participation records
    $memberParticipationQuery = "SELECT 
                                    u.userID,
                                    u.userName,
                                    e.eventTitle,
                                    ea.attendanceStatus,
                                    ea.attendanceIsVolunteer,
                                    ea.attendancePoints,
                                    c.clubName
                                FROM user u
                                INNER JOIN eventregistration er ON u.userID = er.userID
                                INNER JOIN event e ON er.eventID = e.eventID
                                INNER JOIN club c ON e.clubID = c.clubID
                                LEFT JOIN eventattendance ea ON er.registrationID = ea.registrationID
                                WHERE u.roleID = 3
                                ORDER BY u.userName ASC, e.eventTitle ASC
                                LIMIT 50";
    
    $memberParticipationResult = mysqli_query($conn, $memberParticipationQuery);
    $memberParticipation = [];

    if ($memberParticipationResult) {
        while ($row = mysqli_fetch_assoc($memberParticipationResult)) {
            $memberParticipation[] = [
                'userID' => 'US' . str_pad($row['userID'], 4, '0', STR_PAD_LEFT),
                'userName' => $row['userName'],
                'eventTitle' => $row['eventTitle'],
                'clubName' => $row['clubName'],
                'attendanceStatus' => $row['attendanceStatus'] ?: 'Not Recorded',
                'volunteer' => $row['attendanceIsVolunteer'] ? 'Yes' : 'No',
                'points' => $row['attendancePoints'] ?? 0
            ];
        }
    }

    mysqli_close($conn);

    echo json_encode([
        'success' => true,
        'metrics' => [
            'totalStudents' => $totalStudents,
            'totalClubs' => $totalClubs,
            'totalEvents' => $totalEvents,
            'avgAttendance' => $avgAttendance
        ],
        'clubParticipation' => [
            'labels' => $clubNames,
            'registered' => $registeredCounts,
            'present' => $presentCounts,
            'late' => $lateCounts,
            'absent' => $absentCounts
        ],
        'studentsPerClub' => [
            'labels' => $clubDistributionLabels,
            'data' => $clubDistributionData
        ],
        'activeMembers' => $activeMembers,
        'memberParticipation' => $memberParticipation
    ]);
}
?>
