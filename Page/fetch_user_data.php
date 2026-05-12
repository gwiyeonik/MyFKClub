<?php
$link = mysqli_connect("localhost", "root", "", "myfkclub");

$id = mysqli_real_escape_string($link, $_GET['id']);

// Find the user with that ID
$query = "SELECT userName, userEmail FROM user WHERE userID = '$id' LIMIT 1";
$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'userName' => $user['userName'],
        'userEmail' => $user['userEmail']
    ]);
} else {
    echo json_encode(['success' => false]);
}

mysqli_close($link);
?>