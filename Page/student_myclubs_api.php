<?php
// student_myclubs_api.php
session_start();
header('Content-Type: application/json');

$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$userID = intval($_SESSION['user_id']);
$action = $_GET['action'] ?? '';

function userHasClubMembership($link, $userID, $clubID) {
    $stmt = mysqli_prepare($link, "SELECT 1 FROM clubmembership WHERE userID = ? AND clubID = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $userID, $clubID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

if ($action === 'get_club_details') {
    $clubID = intval($_GET['clubID'] ?? 0);
    if ($clubID <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid club ID']);
        exit;
    }

    $stmt = mysqli_prepare($link, "SELECT c.clubID, c.clubName, c.clubDesc, c.clubAdvisor, c.clubStatus, c.clubCreated FROM clubmembership m JOIN club c ON m.clubID = c.clubID WHERE m.userID = ? AND c.clubID = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $userID, $clubID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $club = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$club) {
        echo json_encode(['success' => false, 'error' => 'Club not found or not in your memberships']);
        exit;
    }

    echo json_encode(['success' => true, 'club' => $club]);
    exit;
}

if ($action === 'get_club_members') {
    $clubID = intval($_GET['clubID'] ?? 0);
    if ($clubID <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid club ID']);
        exit;
    }

    if (!userHasClubMembership($link, $userID, $clubID)) {
        echo json_encode(['success' => false, 'error' => 'You are not a member of this club']);
        exit;
    }

    $stmt = mysqli_prepare($link, "SELECT m.membershipID, u.userName, m.clubJoinDate FROM clubmembership m JOIN user u ON m.userID = u.userID WHERE m.clubID = ? ORDER BY m.clubJoinDate DESC");
    mysqli_stmt_bind_param($stmt, 'i', $clubID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $members = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    echo json_encode(['success' => true, 'members' => $members]);
    exit;
}

if ($action === 'get_club_committee') {
    $clubID = intval($_GET['clubID'] ?? 0);
    if ($clubID <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid club ID']);
        exit;
    }

    if (!userHasClubMembership($link, $userID, $clubID)) {
        echo json_encode(['success' => false, 'error' => 'You are not a member of this club']);
        exit;
    }

    $stmt = mysqli_prepare($link, "SELECT m.userID, u.userName, cc.committeePosition FROM clubcommittee cc JOIN clubmembership m ON cc.membershipID = m.membershipID JOIN user u ON m.userID = u.userID WHERE m.clubID = ? ORDER BY u.userName ASC");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => mysqli_error($link)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'i', $clubID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $committee = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    echo json_encode(['success' => true, 'committee' => $committee]);
    exit;
}

if ($action === 'get_club_events') {
    $clubID = intval($_GET['clubID'] ?? 0);
    if ($clubID <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid club ID']);
        exit;
    }

    $stmt = mysqli_prepare($link, "SELECT eventID, eventTitle, eventVenue, eventDateStart, eventDateEnd, eventStatus, eventParticipants, eventMaxParticipants FROM event WHERE clubID = ? ORDER BY eventDateStart DESC");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => mysqli_error($link)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'i', $clubID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $events = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    echo json_encode(['success' => true, 'events' => $events]);
    exit;
}

// ================= UNJOIN CLUB =================
if (isset($_GET['action']) && $_GET['action'] === 'unjoin_club') {

    header('Content-Type: application/json');

    $userID = intval($_SESSION['user_id'] ?? 0);
    $clubID = intval($_POST['clubID'] ?? 0);

    if ($userID <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'User not logged in.'
        ]);

        exit;
    }

    if ($clubID <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid club.'
        ]);

        exit;
    }

    $link = mysqli_connect("localhost", "root", "", "myfkclub");

    if (!$link) {

        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed.'
        ]);

        exit;
    }

    $stmt = mysqli_prepare(
        $link,
        "DELETE FROM clubmembership 
         WHERE userID = ? AND clubID = ?"
    );

    if (!$stmt) {

        echo json_encode([
            'success' => false,
            'message' => 'SQL prepare failed.'
        ]);

        exit;
    }

    mysqli_stmt_bind_param($stmt, "ii", $userID, $clubID);

    if (mysqli_stmt_execute($stmt)) {

        echo json_encode([
            'success' => true,
            'message' => 'Successfully unjoined club.'
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Failed to unjoin club.'
        ]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);

    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
