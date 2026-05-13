<?php
session_start();

if (isset($_POST['signup_btn'])) {
    $link = mysqli_connect("localhost", "root", "", "myfkclub");
    if (!$link) { 
        die("Connection failed: " . mysqli_connect_error()); 
    }

    // Sanitize text inputs
    $userName    = mysqli_real_escape_string($link, $_POST['userName']);
    $userEmail   = mysqli_real_escape_string($link, $_POST['userEmail']);
    $userContact = mysqli_real_escape_string($link, $_POST['userContact']);
    $password    = $_POST['password'];
    $confirm     = $_POST['confirmPassword'];

    // 1. Password Match Validation
    if ($password !== $confirm) {
        header("Location: adminsignup.php?error=Passwords do not match");
        exit();
    }

    // 2. Image Upload Handling
    $photoName = $_FILES['userProfile']['name'];
    $tempName  = $_FILES['userProfile']['tmp_name'];
    $targetDir = "../uploads/"; // Make sure this folder exists
    
    // Create folder if it doesn't exist
    if (!is_dir($targetDir)) { 
        mkdir($targetDir, 0777, true); 
    }
    
    // Create a unique name for the file to prevent overwriting
    $uniquePhotoName = time() . "_" . basename($photoName);
    $targetFile = $targetDir . $uniquePhotoName;

    if (move_uploaded_file($tempName, $targetFile)) {
        // 3. Password Hashing for security
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $roleID = 1; // 1 = Admin

        // 4. SQL Insertion
        // Ensure your database has columns: userName, userEmail, userContact, userProfile, userPass, roleID
        $query = "INSERT INTO user (userName, userEmail, userContact, userProfile, userPass, roleID) 
                  VALUES ('$userName', '$userEmail', '$userContact', '$uniquePhotoName', '$hashedPass', '$roleID')";

        if (mysqli_query($link, $query)) {
            $generatedID = mysqli_insert_id($link);
            header("Location: login.php?success=Registration successful! Your Login ID is: " . $generatedID);
            exit();
        } else {
            header("Location: adminsignup.php?error=Database Error: " . mysqli_error($link));
            exit();
        }
    } else {
        header("Location: adminsignup.php?error=Failed to upload profile photo.");
        exit();
    }

    mysqli_close($link);
} else {
    header("Location: adminsignup.php");
    exit();
}
?>