<?php
$conn = new mysqli("localhost", "root", "", "myfkclub");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clubID     = $_POST['clubID'];
    $title      = $_POST['eventTitle'];
    $desc       = $_POST['eventDesc'];
    $venue      = $_POST['eventVenue'];
    $dateType   = $_POST['date_type'];

    if ($dateType === 'single') {
        $eventDate      = $_POST['eventDate'];
        $eventDateStart = $eventDate;
        $eventDateEnd   = $eventDate;
    } else {
        $eventDateStart = $_POST['eventDateStart'];
        $eventDateEnd   = $_POST['eventDateEnd'];
        $eventDate      = $eventDateStart;
    }

    $stmt = $conn->prepare("INSERT INTO event (clubID, eventTitle, eventDesc, eventDate, eventDateStart, eventDateEnd, eventVenue, eventStatus) VALUES (?, ?, ?, ?, ?, ?, ?, 'Upcoming')");
    $stmt->bind_param("issssss", $clubID, $title, $desc, $eventDate, $eventDateStart, $eventDateEnd, $venue);

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