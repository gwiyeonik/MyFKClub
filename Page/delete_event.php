<?php
// delete_event.php
session_start();

$conn = new mysqli("localhost", "root", "", "myfkclub");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure it's a number

    $stmt = $conn->prepare("DELETE FROM event WHERE eventID = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back with a success message
        header("Location: committee_manage_events.php?msg=deleted");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>