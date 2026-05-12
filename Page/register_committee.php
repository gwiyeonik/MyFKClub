<?php
session_start();

if (isset($_POST['signup_btn'])) {
    $link = mysqli_connect("localhost", "root", "", "myfkclub");

    $userId = mysqli_real_escape_string($link, $_POST['userId']);
    $password = $_POST['password'];
    $confirm = $_POST['confirmPassword'];

    if ($password !== $confirm) {
        header("Location: committeesignup.php?error=Passwords do not match");
        exit();
    }

    $hashedPass = password_hash($password, PASSWORD_DEFAULT);
    $roleID = 2; // Committee Role

    // Update the existing record instead of inserting a new one
    $query = "UPDATE user SET userPass = '$hashedPass', roleID = '$roleID' WHERE userID = '$userId'";

    if (mysqli_query($link, $query)) {
        header("Location: login.php?success=Committee registration complete!");
        exit();
    } else {
        header("Location: committeesignup.php?error=Failed to update details: " . mysqli_error($link));
        exit();
    }

    mysqli_close($link);
}
?>