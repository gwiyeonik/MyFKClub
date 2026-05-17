<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "myfkclub";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed");
}

if(isset($_GET['id'])) {

    $eventID = $_GET['id'];

    $sql = "SELECT * FROM event WHERE eventID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();

    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()) {
        echo json_encode($row);
    }
}
?>