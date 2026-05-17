<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0); // IMPORTANT: prevent breaking JSON

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

    $clubKey = $_GET['clubKey'] ?? '';

    if ($clubKey == '') {
        echo json_encode([
            'success' => false,
            'message' => 'Club key required.'
        ]);
        exit;
    }

    preg_match('/^(\d+)/', $clubKey, $matches);
    $clubID = (int)($matches[1] ?? $clubKey);

    $clubQuery = "
        SELECT clubID, clubName, clubDesc, clubCreated,
               clubAdvisor, clubStatus
        FROM club
        WHERE clubID = $clubID
        LIMIT 1
    ";

    $clubResult = mysqli_query($link, $clubQuery);
    $club = mysqli_fetch_assoc($clubResult);

    if (!$club) {
        echo json_encode([
            'success' => false,
            'message' => 'Club not found.'
        ]);
        exit;
    }

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

/* ======================================================
   ADD COMMITTEE
====================================================== */
if ($action === 'add_committee') {

    $membershipID = $_POST['membershipID'] ?? '';
    $position = $_POST['position'] ?? '';
    $assignedDate = $_POST['assignedDate'] ?? date('Y-m-d');

    $stmt = mysqli_prepare($link, "
        INSERT INTO clubcommittee
        (membershipID, committeePosition, committeeAssignedDate)
        VALUES (?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "iss",
        $membershipID,
        $position,
        $assignedDate
    );

    if (mysqli_stmt_execute($stmt)) {

        echo json_encode([
            "success" => true,
            "message" => "Committee added successfully."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => mysqli_error($link)
        ]);
    }

    exit;
}

/* ======================================================
   UPDATE COMMITTEE
====================================================== */
if ($action === 'update_committee') {

    $membershipID = $_POST['membershipID'] ?? '';
    $position = $_POST['position'] ?? '';
    $assignedDate = date('Y-m-d');

    $membershipID = mysqli_real_escape_string($link, $membershipID);
    $position = mysqli_real_escape_string($link, $position);

    $sql = "
        UPDATE clubcommittee
        SET committeePosition='$position',
            committeeAssignedDate='$assignedDate'
        WHERE membershipID='$membershipID'
    ";

    if (mysqli_query($link, $sql)) {

        echo json_encode([
            'success' => true,
            'message' => 'Committee updated successfully.'
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
echo json_encode([
    'success' => false,
    'message' => 'Invalid action.'
]);
exit;
?>