// save_event.php
<?php
$conn = new mysqli("localhost", "root", "", "myfkclub");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clubID   = $_POST['clubID'];
    $title    = $_POST['eventTitle'];
    $desc     = $_POST['eventDesc'];
    $venue    = $_POST['eventVenue'];
    $dateType = $_POST['date_type'];

    // Handle dates
    if ($dateType === 'single') {
    $eventDateStart = $_POST['eventDateStart'];
    $eventDateEnd   = $_POST['eventDateStart'];
    } 
    else {
    $eventDateStart = $_POST['eventDateStartRange'];
    $eventDateEnd   = $_POST['eventDateEnd'];
    }

    // Validate dates
    if (empty($eventDateStart) || empty($eventDateEnd)) {
        die("Error: Event date is required.");
    }

    $sql = "INSERT INTO event
            (
                clubID,
                eventTitle,
                eventDesc,
                eventDateStart,
                eventDateEnd,
                eventVenue,
                eventStatus
            )
            VALUES
            (?, ?, ?, ?, ?, ?, 'Upcoming')";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssss",
        $clubID,
        $title,
        $desc,
        $eventDateStart,
        $eventDateEnd,
        $venue
    );

    if ($stmt->execute()) {
        header("Location: committee_manage_events.php?msg=added");
        exit();
    } else {
        echo "Execute failed: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>