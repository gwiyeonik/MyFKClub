<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "myfkclub";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * Get next auto increment ID for a table
 */
function getNextID($conn, $table) {
    $result = $conn->query("SHOW TABLE STATUS LIKE '$table'");
    $row = $result->fetch_assoc();
    return $row['Auto_increment'];
}

/**
 * Get all events
 */
function getAllEvents($conn) {
    $sql = "SELECT * FROM event ORDER BY eventDateStart ASC";
    return $conn->query($sql);
}

/**
 * Get event list for dropdown
 */
function getEventList($conn) {
    $events = [];
    $result = $conn->query("SELECT eventID, eventTitle FROM event ORDER BY eventTitle ASC");

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }

    return $events;
}

/**
 * Insert new event
 */
function insertEvent($conn, $clubID, $title, $desc, $startDate, $endDate, $venue) {
    $sql = "INSERT INTO event
            (clubID, eventTitle, eventDesc, eventDateStart, eventDateEnd, eventVenue, eventStatus)
            VALUES (?, ?, ?, ?, ?, ?, 'Upcoming')";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "isssss",
        $clubID,
        $title,
        $desc,
        $startDate,
        $endDate,
        $venue
    );

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}
?>