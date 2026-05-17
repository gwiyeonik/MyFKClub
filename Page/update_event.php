<?php
// update_event.php
session_start();

// 1. Connection settings
$host = "localhost";
$user = "root";
$pass = "";
$db   = "myfkclub"; // Your confirmed database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Collect data from form names (matches the 'name' attribute in your HTML)
    $id      = $_POST['event_id'];     // From <select name="event_id">
    $title   = $_POST['event_title'];  // From <input name="event_title">
    $desc    = $_POST['event_desc'];   // From <input name="event_desc">
    $venue   = $_POST['event_venue'];  // From <input name="event_venue">
    $status  = $_POST['event_status']; // From <input name="event_status">
    $date    = $_POST['event_date'];   // From <input name="event_date">

    // 3. Update the 'events' table
    // We use ? (Prepared Statements) to prevent SQL injection and errors
    $sql = "UPDATE event SET 
            eventTitle = ?, 
            eventDesc = ?, 
            eventVenue = ?, 
            eventStatus = ?, 
            eventDate = ? 
            WHERE eventID = ?";

    $stmt = $conn->prepare($sql);
    
    // "sssssi" means: string, string, string, string, string, integer
    $stmt->bind_param("sssssi", $title, $desc, $venue, $status, $date, $id);

    if ($stmt->execute()) {
        // Success! Go back to the management page
        header("Location: committee_manage_events.php?msg=updated");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>