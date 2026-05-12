<?php
session_start();

if (isset($_POST['signup_btn'])) {
    $link = mysqli_connect("localhost", "root", "", "myfkclub");
    if (!$link) { die("Connection failed: " . mysqli_connect_error()); }

    $userName  = mysqli_real_escape_string($link, $_POST['userName']);
    $userEmail = mysqli_real_escape_string($link, $_POST['userEmail']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirmPassword'];

    if ($password !== $confirm) {
        header("Location: adminsignup.php?error=Passwords do not match");
        exit();
    }

    $hashedPass = password_hash($password, PASSWORD_DEFAULT);
    $roleID = 1; // Role for Admin

    $query = "INSERT INTO user (userName, userEmail, userPass, roleID) 
              VALUES ('$userName', '$userEmail', '$hashedPass', '$roleID')";

    if (mysqli_query($link, $query)) {
        $generatedID = mysqli_insert_id($link);
        header("Location: login.php?success=Admin registered! Your Login ID is: " . $generatedID);
        exit();
    } else {
        header("Location: adminsignup.php?error=Registration failed: " . mysqli_error($link));
        exit();
    }
    mysqli_close($link);
}
?>