<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$link = mysqli_connect("localhost", "root", "", "myfkclub");

if (!$link) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);
    exit;
}
/* ================= SAFE ACTION ================= */
$action = $_REQUEST['action'] ?? '';

/* ======================================================
   GET NEXT MEMBERSHIP ID
====================================================== */
if ($action === 'get_next_membership_id') {

    $query = "SHOW TABLE STATUS LIKE 'clubmembership'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);

    echo json_encode([
        'success' => true,
        'nextID' => $row['Auto_increment'] ?? null
    ]);
    exit;
}

/* ======================================================
   GET USER NAME
====================================================== */
if ($action === 'get_user_name') {

    $userID = $_GET['userID'] ?? '';

    $userID = mysqli_real_escape_string($link, $userID);

    $query = "SELECT userName FROM user WHERE userID = '$userID' LIMIT 1";
    $result = mysqli_query($link, $query);

    if ($row = mysqli_fetch_assoc($result)) {

        echo json_encode([
            'success' => true,
            'userName' => $row['userName']
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'User not found.'
        ]);
    }

    exit;
}

/* ======================================================
   UPDATE CLUB
====================================================== */
if ($action === 'update_club') {

    $clubID = $_POST['clubID'] ?? '';
    $clubName = $_POST['clubName'] ?? '';
    $clubDesc = $_POST['clubDesc'] ?? '';
    $clubAdvisor = $_POST['clubAdvisor'] ?? '';
    $clubCreated = $_POST['clubCreated'] ?? '';
    $clubStatus = $_POST['clubStatus'] ?? '';

    $stmt = mysqli_prepare($link, "
        UPDATE club
        SET clubName=?, clubDesc=?, clubAdvisor=?, clubCreated=?, clubStatus=?
        WHERE clubID=?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "sssssi",
        $clubName,
        $clubDesc,
        $clubAdvisor,
        $clubCreated,
        $clubStatus,
        $clubID
    );

    if (mysqli_stmt_execute($stmt)) {

        echo json_encode([
            "success" => true,
            "message" => "Club updated successfully."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Failed to update club."
        ]);
    }

    exit;
}

/* ======================================================
   ADD CLUB
====================================================== */
if ($action === 'add_club') {

    $clubName = trim($_POST['clubName'] ?? '');
    $clubDesc = trim($_POST['clubDesc'] ?? '');
    $clubAdvisor = trim($_POST['clubAdvisor'] ?? '');
    $clubCreated = trim($_POST['clubCreated'] ?? '');
    $clubStatus = $_POST['clubStatus'] ?? 'Active';

    if ($clubName === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Club name is required.'
        ]);
        exit;
    }

    if ($clubCreated === '') {
        $clubCreated = date('Y-m-d');
    }

    if (strtolower($clubStatus) !== 'inactive') {
        $clubStatus = 'Active';
    }

    $stmt = mysqli_prepare($link, "INSERT INTO club (clubName, clubDesc, clubCreated, clubAdvisor, clubStatus) VALUES (?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to prepare add operation.'
        ]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'sssss', $clubName, $clubDesc, $clubCreated, $clubAdvisor, $clubStatus);

    if (mysqli_stmt_execute($stmt)) {
        $newID = mysqli_insert_id($link);

        echo json_encode([
            'success' => true,
            'message' => 'Club added successfully.',
            'clubID' => $newID,
            'nextID' => $newID + 1
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add club.'
        ]);
    }

    exit;
}

/* ======================================================
   DELETE CLUB
====================================================== */
if ($action === 'delete_club') {

    $clubID = $_POST['clubID'] ?? '';

    $stmt = mysqli_prepare($link, "DELETE FROM club WHERE clubID = ?");
    mysqli_stmt_bind_param($stmt, "i", $clubID);

    if (mysqli_stmt_execute($stmt)) {

        echo json_encode([
            "success" => true,
            "message" => "Club deleted successfully."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Failed to delete club."
        ]);
    }

    exit;
}

/* ======================================================
   GET CLUB LIST
====================================================== */
if ($action === 'get_clubs') {

    $query = "SELECT clubID, clubName FROM club ORDER BY clubName ASC";
    $result = mysqli_query($link, $query);

    $clubs = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $clubs[] = $row;
    }

    echo json_encode([
        'success' => true,
        'clubs' => $clubs
    ]);

    exit;
}

/* ======================================================
   CLUB DETAILS
====================================================== */
if ($action === 'club_details') {

    $clubKey = trim($_GET['clubKey'] ?? '');

    if ($clubKey == '') {
        echo json_encode([
            'success' => false,
            'message' => 'Club key required.'
        ]);
        exit;
    }

    // Escape input safely
    $clubKeySafe = mysqli_real_escape_string($link, $clubKey);

    // Detect if input starts with numeric ID
    if (preg_match('/^(\d+)/', $clubKey, $matches)) {

        $clubID = (int)$matches[1];

        $clubQuery = "
            SELECT clubID, clubName, clubDesc, clubCreated,
                   clubAdvisor, clubStatus
            FROM club
            WHERE clubID = $clubID
            LIMIT 1
        ";

    } else {

        // Search by club name
        $clubQuery = "
            SELECT clubID, clubName, clubDesc, clubCreated,
                   clubAdvisor, clubStatus
            FROM club
            WHERE clubName LIKE '%$clubKeySafe%'
            LIMIT 1
        ";
    }

    $clubResult = mysqli_query($link, $clubQuery);

    $club = mysqli_fetch_assoc($clubResult);

    if (!$club) {
        echo json_encode([
            'success' => false,
            'message' => 'Club not found.'
        ]);
        exit;
    }

    $clubID = $club['clubID'];

    /* committee */
    $committee = [];

    $committeeQuery = "
        SELECT 
            cm.clubID,
            cm.membershipID,
            u.userID,
            u.userName,
            cc.committeePosition,
            cc.committeeAssignedDate
        FROM clubcommittee cc
        JOIN clubmembership cm ON cc.membershipID = cm.membershipID
        JOIN user u ON cm.userID = u.userID
        WHERE cm.clubID = $clubID
        ORDER BY u.userName ASC
    ";

    $committeeResult = mysqli_query($link, $committeeQuery);

    while ($row = mysqli_fetch_assoc($committeeResult)) {
        $committee[] = $row;
    }

    echo json_encode([
        'success' => true,
        'club' => $club,
        'committee' => $committee
    ]);

    exit;
}

if ($action === 'get_events') {

    $sql = "
        SELECT
            eventID,
            eventTitle,
            eventVenue,
            eventDateStart,
            eventDateEnd,
            eventStatus,
            eventParticipants,
            eventMaxParticipants,
            eventDesc
        FROM event
        ORDER BY eventName DESC
    ";

    $result = mysqli_query($link, $sql);

    $events = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }

    echo json_encode([
        "success" => true,
        "events" => $events
    ]);

    exit;
}

/* ======================================================
   GET CLUB MEMBERS
====================================================== */
if ($action === 'get_club_members') {

    $clubID = $_GET['clubID'] ?? '';

    $clubID = (int)$clubID;

    if ($clubID <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid club ID.'
        ]);

        exit;
    }

    $members = [];

    $query = "
        SELECT
            cm.membershipID,
            cm.userID,
            u.userName
        FROM clubmembership cm
        JOIN user u
            ON cm.userID = u.userID
        WHERE cm.clubID = $clubID
        ORDER BY cm.membershipID ASC
    ";

    $result = mysqli_query($link, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }

    echo json_encode([
        'success' => true,
        'members' => $members
    ]);

    exit;
}

/* ======================================================
   ADD COMMITTEE
====================================================== */
if ($action === 'add_committee') {

    $clubID = trim($_POST['clubID'] ?? '');
    $membershipID = trim($_POST['membershipID'] ?? '');
    $userID = trim($_POST['userID'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $assignedDate = trim($_POST['assignedDate'] ?? date('Y-m-d'));

    if ($clubID === '' || $membershipID === '' || $userID === '' || $position === '') {
        echo json_encode([
            "success" => false,
            "message" => "Club ID, membership ID, user ID, and position are required."
        ]);
        exit;
    }

    $clubID = intval($clubID);
    $membershipID = intval($membershipID);
    $userID = intval($userID);

    if ($clubID <= 0 || $membershipID <= 0 || $userID <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid club, membership, or user ID."
        ]);
        exit;
    }

    // Validate club and user exist
    $clubResult = mysqli_query($link, "SELECT clubID FROM club WHERE clubID = $clubID LIMIT 1");
    if (!$clubResult || mysqli_num_rows($clubResult) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Club not found."
        ]);
        exit;
    }

    $userResult = mysqli_query($link, "SELECT userID FROM user WHERE userID = $userID LIMIT 1");
    if (!$userResult || mysqli_num_rows($userResult) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "User not found."
        ]);
        exit;
    }

    mysqli_begin_transaction($link);

$stmt = mysqli_prepare($link, "
    SELECT clubID, userID
    FROM clubmembership
    WHERE membershipID = ?
");

mysqli_stmt_bind_param($stmt, "i", $membershipID);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {

    mysqli_stmt_close($stmt);

    mysqli_rollback($link);

    echo json_encode([
        "success" => false,
        "message" => "This user is not a club member."
    ]);

    exit;
}

mysqli_stmt_bind_result($stmt, $existingClubID, $existingUserID);

mysqli_stmt_fetch($stmt);

mysqli_stmt_close($stmt);

if ($existingClubID != $clubID || $existingUserID != $userID) {

    mysqli_rollback($link);

    echo json_encode([
        "success" => false,
        "message" => "Membership does not belong to this club/user."
    ]);

    exit;
}

    $stmt = mysqli_prepare($link, "SELECT membershipID FROM clubcommittee WHERE membershipID = ?");
    mysqli_stmt_bind_param($stmt, "i", $membershipID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        mysqli_rollback($link);
        echo json_encode([
            "success" => false,
            "message" => "This membership is already assigned to a committee role."
        ]);
        exit;
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($link, "INSERT INTO clubcommittee (membershipID, committeePosition, committeeAssignedDate) VALUES (?, ?, ?)");
    if (!$stmt) {
        mysqli_rollback($link);
        echo json_encode([
            "success" => false,
            "message" => "Prepare failed: " . mysqli_error($link)
        ]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "iss", $membershipID, $position, $assignedDate);
    if (!mysqli_stmt_execute($stmt)) {
        $errorMessage = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_rollback($link);
        echo json_encode([
            "success" => false,
            "message" => "Failed to add committee member: " . $errorMessage
        ]);
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_commit($link);

    echo json_encode([
        "success" => true,
        "message" => "Committee added successfully."
    ]);
    exit;
}

/* ======================================================
   UPDATE COMMITTEE
====================================================== */
if ($action === 'update_committee') {

    $membershipID = $_POST['membershipID'] ?? '';
    $position = $_POST['position'] ?? '';
    $assignedDate = date('Y-m-d');

    $stmt = mysqli_prepare($link, "
        UPDATE clubcommittee
        SET committeePosition=?,
            committeeAssignedDate=?
        WHERE membershipID=?
    ");

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Prepare failed: ' . mysqli_error($link)
        ]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "ssi", $position, $assignedDate, $membershipID);

    if (mysqli_stmt_execute($stmt)) {

        echo json_encode([
            'success' => true,
            'message' => 'Committee updated successfully.'
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Execute failed: ' . mysqli_stmt_error($stmt)
        ]);
    }

    mysqli_stmt_close($stmt);
    exit;
}

/* ======================================================
   DELETE COMMITTEE
====================================================== */
if ($action === 'delete_committee') {

    $membershipID = $_POST['membershipID'] ?? '';

    $stmt = mysqli_prepare($link, "
        DELETE FROM clubcommittee
        WHERE membershipID = ?
    ");

    mysqli_stmt_bind_param($stmt, "i", $membershipID);

    if (mysqli_stmt_execute($stmt)) {

        echo json_encode([
            'success' => true,
            'message' => 'Committee deleted successfully.'
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => mysqli_error($link)
        ]);
    }

    exit;
}

/* ======================================================
   DEFAULT
====================================================== */
/* ======================================================
   GET CLUB EVENTS
====================================================== */
if ($action === 'get_club_events') {

    $clubID = $_GET['clubID'] ?? $_POST['clubID'] ?? '';

    $clubID = (int)$clubID;

    if ($clubID <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Club ID required.'
        ]);
        exit;
    }

    $events = [];

   $evtQuery = "
    SELECT 
        eventID,
        eventTitle,
        eventVenue,
        eventDateStart,
        eventDateEnd,
        eventStatus,
        eventParticipants,
        eventMaxParticipants,
        eventDesc
    FROM event
    WHERE clubID = $clubID
    ORDER BY eventID ASC";
    $evtResult = mysqli_query($link, $evtQuery);

    if (!$evtResult) {
        echo json_encode([
            'success' => false,
            'message' => 'Event query failed: ' . mysqli_error($link)
        ]);
        exit;
    }

    while ($row = mysqli_fetch_assoc($evtResult)) {
        $events[] = $row;
    }

    echo json_encode([
        'success' => true,
        'events' => $events
    ]);

    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid action.'
]);
exit;
?>