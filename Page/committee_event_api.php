<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "myfkclub");

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

// Get event ID from URL
$eventID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($eventID <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid event ID."
    ]);
    exit;
}

// Prepare query
$stmt = $conn->prepare("
    SELECT
        eventID,
        eventTitle,
        eventDesc,
        eventVenue,
        eventDateStart,
        eventDateEnd,
        eventStatus,
        eventParticipants,
        eventMaxParticipants
    FROM event
    WHERE eventID = ?
");

$stmt->bind_param("i", $eventID);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $event = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "data" => $event
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Event not found."
    ]);
}

$stmt->close();
$conn->close();
?>