<?php
session_start();

if (isset($_POST['signup_btn'])) {

    $link = mysqli_connect("localhost", "root", "", "myfkclub");

    if (!$link) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // ================= INPUT =================
    $userName    = $_POST['userName'];
    $userEmail   = $_POST['userEmail'];
    $userContact = $_POST['userContact'];
    $password    = $_POST['password'];
    $confirm     = $_POST['confirmPassword'];

    // ================= VALIDATION =================
    if ($password !== $confirm) {
        header("Location: studentsignup.php?error=Passwords do not match");
        exit();
    }

    if (!isset($_FILES['userPhoto']) || $_FILES['userPhoto']['error'] != 0) {
        header("Location: studentsignup.php?error=Please upload a profile photo");
        exit();
    }

    // ================= PHOTO UPLOAD =================
    $targetDir = "../uploads/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $photoName = basename($_FILES['userPhoto']['name']);
    $uniquePhoto = time() . "_" . $photoName;
    $targetFile = $targetDir . $uniquePhoto;

    if (!move_uploaded_file($_FILES['userPhoto']['tmp_name'], $targetFile)) {
        header("Location: studentsignup.php?error=Failed to upload photo");
        exit();
    }

    // ================= SECURE INSERT =================
    $hashedPass = password_hash($password, PASSWORD_DEFAULT);
    
    // ✅ FIXED: Changed role ID to 3 so they register as a Student instead of Committee
    $roleID = 3; 

    // ✅ FIXED: Changed column 'userPhoto' to 'userProfile' to resolve the database crash
    $stmt = $link->prepare("
        INSERT INTO user (userName, userEmail, userContact, userProfile, userPass, roleID)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // This stays exactly the same as before
    $stmt->bind_param(
        "sssssi",
        $userName,
        $userEmail,
        $userContact,
        $uniquePhoto,
        $hashedPass,
        $roleID
    );

    if ($stmt->execute()) {

        $generatedID = $stmt->insert_id;

        header("Location: login.php?success=Student registered! ID: " . $generatedID);
        exit();

    } else {
        header("Location: studentsignup.php?error=DB Error: " . $stmt->error);
        exit();
    }

    $stmt->close();
    mysqli_close($link);
}
?>