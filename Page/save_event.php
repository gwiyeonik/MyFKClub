<?php
$conn = new mysqli("localhost", "root", "", "myfkclub");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clubID   = $_POST['clubID'];
    $title    = $_POST['eventTitle'];
    $desc     = $_POST['eventDesc'];
    $venue    = $_POST['eventVenue'];
    $dateType = $_POST['date_type'];

    if ($dateType === 'single') {
        $eventDateStart = $_POST['eventDate'];
        $eventDateEnd   = $_POST['eventDate'];
    } else {
        $eventDateStart = $_POST['eventDateStart'];
        $eventDateEnd   = $_POST['eventDateEnd'];
    }

    $stmt = $conn->prepare("INSERT INTO event (clubID, eventTitle, eventDesc, eventDateStart, eventDateEnd, eventVenue, eventStatus) VALUES (?, ?, ?, ?, ?, ?, 'Upcoming')");
    $stmt->bind_param("isssss", $clubID, $title, $desc, $eventDateStart, $eventDateEnd, $venue);

    if ($stmt->execute()) {
        header("Location: committee_manage_events.php?msg=added");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>