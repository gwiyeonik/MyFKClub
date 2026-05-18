<?php
// student_clublist_api.php
session_start();
header('Content-Type: application/json');

$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'join_club') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Login required to join a club.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $clubID = intval($input['clubID'] ?? 0);
    $userID = intval($_SESSION['user_id']);

    if ($clubID <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid club selection.']);
        exit;
    }

    $stmt = mysqli_prepare($link, "SELECT clubID FROM club WHERE clubID = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $clubID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) === 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'error' => 'Club not found.']);
        exit;
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($link, "SELECT membershipID, clubJoinDate FROM clubmembership WHERE clubID = ? AND userID = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $clubID, $userID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $existingMembershipID, $existingJoinDate);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        echo json_encode([
            'success' => true,
            'membershipID' => $existingMembershipID,
            'clubJoinDate' => $existingJoinDate,
            'message' => 'Already a member of this club.'
        ]);
        exit;
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($link, "INSERT INTO clubmembership (clubID, userID, clubJoinDate) VALUES (?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => mysqli_error($link)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $clubID, $userID);
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'error' => 'Failed to join club: ' . $error]);
        exit;
    }
    $membershipID = mysqli_insert_id($link);
    mysqli_stmt_close($stmt);

    $joinDate = date('Y-m-d');
    echo json_encode(['success' => true, 'membershipID' => $membershipID, 'clubJoinDate' => $joinDate]);
    exit;
}

if ($action === 'add_club') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
    $name = trim($input['clubName'] ?? '');
    $desc = trim($input['clubDesc'] ?? '');
    $advisor = trim($input['clubAdvisor'] ?? '');
    $status = trim($input['clubStatus'] ?? 'Active');
    if ($name === '') {
        echo json_encode(['success' => false, 'error' => 'Club name required']);
        exit;
    }

    $stmt = mysqli_prepare($link, "INSERT INTO club (clubName, clubDesc, clubAdvisor, clubStatus, clubCreated) VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => mysqli_error($link)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $desc, $advisor, $status);
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        exit;
    }
    $id = mysqli_insert_id($link);
    echo json_encode(['success' => true, 'clubID' => $id, 'clubName' => $name]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
