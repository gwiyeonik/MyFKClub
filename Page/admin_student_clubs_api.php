<?php
header('Content-Type: application/json');
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'list_clubs') {
    $result = mysqli_query($link, "SELECT clubID, clubName FROM club ORDER BY clubName");
    $clubs = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $clubs[] = $row;
        }
    }
    echo json_encode(['success' => true, 'clubs' => $clubs]);
    exit;
}

if ($action === 'club_details') {
    $clubKey = trim($_GET['clubKey'] ?? '');
    if ($clubKey === '') {
        echo json_encode(['success' => false, 'message' => 'clubKey is required.']);
        exit;
    }

    $club = null;
    if (preg_match('/^(\d+)\s*-\s*(.*)$/', $clubKey, $matches)) {
        $clubKey = $matches[1];
    }

    if (preg_match('/^\d+$/', $clubKey)) {
        $clubID = (int)$clubKey;
        $stmt = mysqli_prepare($link, "SELECT clubID, clubName, clubDesc, clubCreated, clubAdvisor, clubStatus FROM club WHERE clubID = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $clubID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $club = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    } else {
        $escapedKey = mysqli_real_escape_string($link, $clubKey);
        $query = "SELECT clubID, clubName, clubDesc, clubCreated, clubAdvisor, clubStatus FROM club WHERE clubName = '$escapedKey' OR clubName LIKE '%$escapedKey%' ORDER BY clubName LIMIT 1";
        $result = mysqli_query($link, $query);
        $club = $result ? mysqli_fetch_assoc($result) : null;
    }

    if (!$club) {
        echo json_encode(['success' => false, 'message' => 'Club not found.']);
        exit;
    }

    $clubID = (int)$club['clubID'];
    $committee = [];
    $committeeQuery = "SELECT u.userID, u.userName, cc.commiteePosition FROM clubcommittee cc JOIN clubmembership cm ON cc.membershipID = cm.membershipID JOIN user u ON cm.userID = u.userID WHERE cm.clubID = $clubID ORDER BY u.userName";
    $committeeResult = mysqli_query($link, $committeeQuery);
    if ($committeeResult) {
        while ($row = mysqli_fetch_assoc($committeeResult)) {
            $committee[] = $row;
        }
    }

    $events = [];
    $eventsQuery = "SELECT eventID, eventTitle, eventDate, eventVenue, eventStatus FROM event WHERE clubID = $clubID ORDER BY eventDate LIMIT 6";
    $eventsResult = mysqli_query($link, $eventsQuery);
    if ($eventsResult) {
        while ($row = mysqli_fetch_assoc($eventsResult)) {
            $events[] = $row;
        }
    }

    echo json_encode(['success' => true, 'club' => $club, 'committee' => $committee, 'events' => $events]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit;
