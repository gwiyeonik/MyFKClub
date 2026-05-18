<?php
$conn = new mysqli("localhost", "root", "", "myfkclub");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventID = $_POST['event_id'];
    $title   = $_POST['event_title'];
    $desc    = $_POST['event_desc'];
    $venue   = $_POST['event_venue'];
    $status  = $_POST['event_status'];
    $dateType = $_POST['update_date_type'];

    // Determine dates
    if ($dateType === 'single') {
        $eventDateStart = $_POST['event_date'];
        $eventDateEnd   = $_POST['event_date']; // same date
    } else {
        $eventDateStart = $_POST['event_date_start'];
        $eventDateEnd   = $_POST['event_date_end'];
    }

    // Update query
    $stmt = $conn->prepare("
        UPDATE event
        SET
            eventTitle = ?,
            eventDesc = ?,
            eventVenue = ?,
            eventStatus = ?,
            eventDateStart = ?,
            eventDateEnd = ?
        WHERE eventID = ?
    ");

    $stmt->bind_param(
        "ssssssi",
        $title,
        $desc,
        $venue,
        $status,
        $eventDateStart,
        $eventDateEnd,
        $eventID
    );

    if ($stmt->execute()) {
        header("Location: committee_manage_events.php?msg=updated");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>