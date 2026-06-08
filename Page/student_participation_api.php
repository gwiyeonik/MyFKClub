<?php
// student_participation_api.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userID = intval($_SESSION['user_id']);

// DB connection - adjust credentials if different
$conn = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$response = [
    'success' => true,
    'data' => [
        'totalPoints' => 0,
        'recognition' => '',
        'rank' => 0,
        'totalStudents' => 0,
        'history' => [],
        'topStudents' => []
    ]
];

// 1) Get total points from studentpointsummary if available
$ptsSql = "SELECT totalPoints FROM studentpointsummary WHERE userID = ? LIMIT 1";
if ($ptsStmt = mysqli_prepare($conn, $ptsSql)) {
    mysqli_stmt_bind_param($ptsStmt, "i", $userID);
    mysqli_stmt_execute($ptsStmt);
    $ptsRes = mysqli_stmt_get_result($ptsStmt);
    if ($ptsRes && mysqli_num_rows($ptsRes) > 0) {
        $row = mysqli_fetch_assoc($ptsRes);
        $response['data']['totalPoints'] = intval($row['totalPoints']);
    }
    mysqli_stmt_close($ptsStmt);
}

// Fallback: compute from eventattendance
if (intval($response['data']['totalPoints']) === 0) {
    $calcSql = "SELECT COALESCE(SUM(ea.attendancePoints),0) AS sumPoints
                FROM eventattendance ea
                INNER JOIN eventregistration er ON ea.registrationID = er.registrationID
                WHERE er.userID = ?";
    if ($calcStmt = mysqli_prepare($conn, $calcSql)) {
        mysqli_stmt_bind_param($calcStmt, "i", $userID);
        mysqli_stmt_execute($calcStmt);
        $calcRes = mysqli_stmt_get_result($calcStmt);
        if ($calcRes && mysqli_num_rows($calcRes) > 0) {
            $r = mysqli_fetch_assoc($calcRes);
            $response['data']['totalPoints'] = intval($r['sumPoints']);
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
if ($histStmt = mysqli_prepare($conn, $histSql)) {
    mysqli_stmt_bind_param($histStmt, "i", $userID);
    mysqli_stmt_execute($histStmt);
    $histRes = mysqli_stmt_get_result($histStmt);
    if ($histRes) {
        while ($r = mysqli_fetch_assoc($histRes)) {
            $history[] = [
                'eventTitle' => $r['eventTitle'],
                'eventDate' => $r['eventDateStart'],
                'attendanceCheckinTime' => $r['attendanceCheckinTime'],
                'attendanceStatus' => $r['attendanceStatus'],
                'attendancePoints' => intval($r['attendancePoints'])
            ];
        }
    }
    mysqli_stmt_close($histStmt);
}
$response['data']['history'] = $history;

// 3) Recognition level
$tp = intval($response['data']['totalPoints']);
if ($tp >= 80) {
    $response['data']['recognition'] = 'Outstanding participant';
} elseif ($tp >= 50) {
    $response['data']['recognition'] = 'Eligible for active student award';
} elseif ($tp >= 20) {
    $response['data']['recognition'] = 'Eligible for participation certificate';
} else {
    $response['data']['recognition'] = 'Warning/Reminder';
}

// 4) Ranking and total students
// total students (roleID = 3 assumed)
$stuCountSql = "SELECT COUNT(*) AS total FROM user WHERE roleID = 3";
if ($stuCountRes = mysqli_query($conn, $stuCountSql)) {
    $stuRow = mysqli_fetch_assoc($stuCountRes);
    $response['data']['totalStudents'] = intval($stuRow['total']);
}

// rank: count how many have more points (considering missing summaries as 0)
$greaterSql = "SELECT COUNT(*) AS higher FROM user u LEFT JOIN studentpointsummary sps ON u.userID = sps.userID WHERE u.roleID = 3 AND COALESCE(sps.totalPoints,0) > ?";
if ($gStmt = mysqli_prepare($conn, $greaterSql)) {
    mysqli_stmt_bind_param($gStmt, "i", $tp);
    mysqli_stmt_execute($gStmt);
    $gRes = mysqli_stmt_get_result($gStmt);
    if ($gRes) {
        $gRow = mysqli_fetch_assoc($gRes);
        $higherCount = intval($gRow['higher']);
        $response['data']['rank'] = $higherCount + 1;
    }
    mysqli_stmt_close($gStmt);
}

// 5) Top students (top 10)
$topStudents = [];
$topSql = "SELECT u.userID, u.userName, COALESCE(sps.totalPoints,0) AS totalPoints
           FROM user u
           LEFT JOIN studentpointsummary sps ON u.userID = sps.userID
           WHERE u.roleID = 3
           ORDER BY totalPoints DESC, u.userName ASC
           LIMIT 10";
if ($topRes = mysqli_query($conn, $topSql)) {
    while ($r = mysqli_fetch_assoc($topRes)) {
        $topStudents[] = [
            'userID' => 'US' . str_pad($r['userID'], 4, '0', STR_PAD_LEFT),
            'userName' => $r['userName'],
            'totalPoints' => intval($r['totalPoints'])
        ];
    }
}
$response['data']['topStudents'] = $topStudents;

mysqli_close($conn);

echo json_encode($response);
exit;
