<?php
// admin_participation_reports_api.php
session_start();
header('Content-Type: application/json');

// Authentication Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
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
    
    // Count total events
    $totalEventsQuery = "SELECT COUNT(*) as total FROM event";
    $totalEventsResult = mysqli_query($conn, $totalEventsQuery);
    $totalEvents = $totalEventsResult ? (int)mysqli_fetch_assoc($totalEventsResult)['total'] : 0;

    // Count total participants (distinct students in events)
    $totalParticipationQuery = "SELECT COUNT(DISTINCT er.userID) as total FROM eventregistration er";
    $totalParticipationResult = mysqli_query($conn, $totalParticipationQuery);
    $totalParticipation = $totalParticipationResult ? (int)mysqli_fetch_assoc($totalParticipationResult)['total'] : 0;

    // Calculate average attendance rate
    $attendanceQuery = "SELECT 
                            COUNT(*) as total_records,
                            SUM(CASE WHEN attendanceStatus = 'present' THEN 1 ELSE 0 END) as total_present
                        FROM eventattendance";
    $attendanceResult = mysqli_query($conn, $attendanceQuery);
    $attendanceData = $attendanceResult ? mysqli_fetch_assoc($attendanceResult) : null;
    
    $totalRecords = $attendanceData ? (int)$attendanceData['total_records'] : 0;
    $totalPresent = $attendanceData ? (int)$attendanceData['total_present'] : 0;
    $avgAttendance = ($totalRecords > 0) ? round(($totalPresent / $totalRecords) * 100) : 0;

    // Count outstanding students (top 10% by points)
    $topPointsQuery = "SELECT MAX(totalPoints) as max_points FROM studentpointsummary";
    $topPointsResult = mysqli_query($conn, $topPointsQuery);
    $topPoints = $topPointsResult ? (int)mysqli_fetch_assoc($topPointsResult)['max_points'] : 0;
    $threshold = ($topPoints > 0) ? ($topPoints * 0.9) : 0;

    $outstandingQuery = "SELECT COUNT(*) as total FROM studentpointsummary WHERE totalPoints >= ?";
    $outstandingStmt = mysqli_prepare($conn, $outstandingQuery);
    mysqli_stmt_bind_param($outstandingStmt, "i", $threshold);
    mysqli_stmt_execute($outstandingStmt);
    $outstandingResult = mysqli_stmt_get_result($outstandingStmt);
    $outstandingStudents = $outstandingResult ? (int)mysqli_fetch_assoc($outstandingResult)['total'] : 0;

    // Get monthly participation trend (last 12 months)
    $monthlyTrendQuery = "SELECT 
                            DATE_FORMAT(e.eventDateStart, '%Y-%m') as month,
                            COUNT(DISTINCT er.registrationID) as participation_count,
                            COUNT(DISTINCT er.userID) as unique_students
                        FROM event e
                        LEFT JOIN eventregistration er ON e.eventID = er.eventID
                        WHERE e.eventDateStart >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                        GROUP BY DATE_FORMAT(e.eventDateStart, '%Y-%m')
                        ORDER BY month ASC";
    
    $monthlyTrendResult = mysqli_query($conn, $monthlyTrendQuery);
    $monthlyLabels = [];
    $monthlyData = [];

    if ($monthlyTrendResult) {
        while ($row = mysqli_fetch_assoc($monthlyTrendResult)) {
            $monthlyLabels[] = date('M Y', strtotime($row['month'] . '-01'));
            $monthlyData[] = (int)$row['participation_count'];
        }
    }

    // Get recognition level distribution (based on points ranges)
    $recognitionQuery = "SELECT 
                            CASE 
                                WHEN sps.totalPoints >= 100 THEN 'Gold'
                                WHEN sps.totalPoints >= 70 THEN 'Silver'
                                WHEN sps.totalPoints >= 40 THEN 'Bronze'
                                WHEN sps.totalPoints > 0 THEN 'Participant'
                                ELSE 'No Points'
                            END as recognition_level,
                            COUNT(*) as count
                        FROM studentpointsummary sps
                        GROUP BY recognition_level
                        ORDER BY CASE 
                            WHEN recognition_level = 'Gold' THEN 1
                            WHEN recognition_level = 'Silver' THEN 2
                            WHEN recognition_level = 'Bronze' THEN 3
                            WHEN recognition_level = 'Participant' THEN 4
                            ELSE 5
                        END";
    
    $recognitionResult = mysqli_query($conn, $recognitionQuery);
    $recognitionLabels = [];
    $recognitionData = [];

    if ($recognitionResult) {
        while ($row = mysqli_fetch_assoc($recognitionResult)) {
            $recognitionLabels[] = $row['recognition_level'];
            $recognitionData[] = (int)$row['count'];
        }
    }

    // Get attendance rate per event
    $eventAttendanceQuery = "SELECT 
                                e.eventTitle,
                                c.clubName,
                                COUNT(DISTINCT er.userID) as registered,
                                SUM(CASE WHEN ea.attendanceStatus = 'present' THEN 1 ELSE 0 END) as present,
                                SUM(CASE WHEN ea.attendanceStatus = 'late' THEN 1 ELSE 0 END) as late,
                                SUM(CASE WHEN ea.attendanceStatus = 'absent' THEN 1 ELSE 0 END) as absent,
                                ROUND(SUM(CASE WHEN ea.attendanceStatus IN ('present', 'late') THEN 1 ELSE 0 END) / COUNT(DISTINCT er.userID) * 100) as attendance_rate
                            FROM event e
                            LEFT JOIN club c ON e.clubID = c.clubID
                            LEFT JOIN eventregistration er ON e.eventID = er.eventID
                            LEFT JOIN eventattendance ea ON er.registrationID = ea.registrationID
                            GROUP BY e.eventID, e.eventTitle, c.clubName
                            ORDER BY e.eventDateStart DESC
                            LIMIT 20";
    
    $eventAttendanceResult = mysqli_query($conn, $eventAttendanceQuery);
    $eventAttendanceData = [];

    if ($eventAttendanceResult) {
        while ($row = mysqli_fetch_assoc($eventAttendanceResult)) {
            $eventAttendanceData[] = [
                'eventTitle' => $row['eventTitle'],
                'clubName' => $row['clubName'] ?: 'N/A',
                'registered' => (int)$row['registered'],
                'present' => (int)$row['present'],
                'late' => (int)$row['late'],
                'absent' => (int)$row['absent'],
                'attendance_rate' => (int)$row['attendance_rate'] . '%'
            ];
        }
    }

    // Get top students by points rank
    $topStudentsQuery = "SELECT 
                            u.userID,
                            u.userName,
                            c.clubName,
                            COUNT(DISTINCT ea.attendanceID) as events_attended,
                            sps.totalPoints,
                            CASE 
                                WHEN sps.totalPoints >= 100 THEN 'Gold'
                                WHEN sps.totalPoints >= 70 THEN 'Silver'
                                WHEN sps.totalPoints >= 40 THEN 'Bronze'
                                ELSE 'Participant'
                            END as recognition
                        FROM studentpointsummary sps
                        INNER JOIN user u ON sps.userID = u.userID
                        LEFT JOIN clubmembership cm ON u.userID = cm.userID
                        LEFT JOIN club c ON cm.clubID = c.clubID
                        LEFT JOIN eventregistration er ON u.userID = er.userID
                        LEFT JOIN eventattendance ea ON er.registrationID = ea.registrationID
                        WHERE u.roleID = 3
                        GROUP BY u.userID, u.userName, c.clubName, sps.totalPoints
                        ORDER BY sps.totalPoints DESC
                        LIMIT 20";
    
    $topStudentsResult = mysqli_query($conn, $topStudentsQuery);
    $topStudentsData = [];

    if ($topStudentsResult) {
        while ($row = mysqli_fetch_assoc($topStudentsResult)) {
            $topStudentsData[] = [
                'userID' => 'US' . str_pad($row['userID'], 4, '0', STR_PAD_LEFT),
                'userName' => $row['userName'],
                'clubName' => $row['clubName'] ?: 'N/A',
                'events_attended' => (int)$row['events_attended'],
                'total_points' => (int)$row['total_points'],
                'recognition' => $row['recognition']
            ];
        }
    }

    mysqli_close($conn);

    echo json_encode([
        'success' => true,
        'metrics' => [
            'totalEvents' => $totalEvents,
            'totalParticipation' => $totalParticipation,
            'avgAttendance' => $avgAttendance,
            'outstandingStudents' => $outstandingStudents
        ],
        'monthlyTrend' => [
            'labels' => $monthlyLabels,
            'data' => $monthlyData
        ],
        'recognitionDistribution' => [
            'labels' => $recognitionLabels,
            'data' => $recognitionData
        ],
        'eventAttendance' => $eventAttendanceData,
        'topStudents' => $topStudentsData
    ]);
}
?>
