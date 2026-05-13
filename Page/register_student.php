<?php
session_start();

if (isset($_POST['signup_btn'])) {
    $link = mysqli_connect("localhost", "root", "", "myfkclub");
    if (!$link) { die("Connection failed: " . mysqli_connect_error()); }

    $userName    = mysqli_real_escape_string($link, $_POST['userName']);
    $userEmail   = mysqli_real_escape_string($link, $_POST['userEmail']);
    $userContact = mysqli_real_escape_string($link, $_POST['userContact']);
    $password    = $_POST['password'];
    $confirm     = $_POST['confirmPassword'];

    // 1. Validate Password
    if ($password !== $confirm) {
        header("Location: studentsignup.php?error=Passwords do not match");
        exit();
    }

    // 2. Handle Profile Photo Upload
    $photoName = $_FILES['userProfile']['name'];
    $targetDir = "../uploads/";
    
    if (!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }
    
    $uniquePhoto = time() . "_" . basename($photoName);
    $targetFile  = $targetDir . $uniquePhoto;

    if (move_uploaded_file($_FILES['userProfile']['tmp_name'], $targetFile)) {
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $roleID = 2; // Typically 2 for Students

        // 3. Insert into DB
        $query = "INSERT INTO user (userName, userEmail, userContact, userProfile, userPass, roleID) 
                  VALUES ('$userName', '$userEmail', '$userContact', '$uniquePhoto', '$hashedPass', '$roleID')";

        if (mysqli_query($link, $query)) {
            $generatedID = mysqli_insert_id($link);
            header("Location: login.php?success=Student registered! ID: " . $generatedID);
            exit();
        } else {
            header("Location: studentsignup.php?error=DB Error: " . mysqli_error($link));
        }
    } else {
        header("Location: studentsignup.php?error=Failed to upload photo.");
    }
    mysqli_close($link);
}
?>