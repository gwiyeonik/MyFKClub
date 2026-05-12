<?php
session_start();

if (isset($_POST['signup_btn'])) {

    // 1. Establish Database Connection
    $link = mysqli_connect("localhost", "root", "", "myfkclub");
    if (!$link) { 
        die("Connection failed: " . mysqli_connect_error()); 
    }

    // 2. Get Data & Sanitize Inputs
    // Note: We no longer need $userId from $_POST because database auto-generates it
    $userName  = mysqli_real_escape_string($link, $_POST['userName']);
    $userEmail = mysqli_real_escape_string($link, $_POST['userEmail']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirmPassword'];

    // 3. Validation: Password Match
    if ($password !== $confirm) {
        header("Location: studentsignup.php?error=Passwords do not match");
        exit();
    }

    // 4. Set Constant for Student Role
    $roleID = 3; 

    // 5. Hash the Password
    $hashedPass = password_hash($password, PASSWORD_DEFAULT);

    // 6. Insert into User Table
    // Notice: userID is NOT in the column list or values list
    $query = "INSERT INTO user (userName, userEmail, userPass, roleID) 
              VALUES ('$userName', '$userEmail', '$hashedPass', '$roleID')";

    if (mysqli_query($link, $query)) {
        // Get the ID that was just created by Auto-Increment
        $generatedID = mysqli_insert_id($link);
        
        // Redirect with the new ID so they know what to use to login
        header("Location: login.php?success=Success! Your Login ID is: " . $generatedID);
        exit();
    } else {
        header("Location: studentsignup.php?error=Registration failed: " . mysqli_error($link));
        exit();
    }

    mysqli_close($link);
} else {
    header("Location: studentsignup.php");
    exit();
}
?>