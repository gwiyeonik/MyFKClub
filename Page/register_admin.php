<?php
session_start();

if (isset($_POST['signup_btn'])) {

    $link = mysqli_connect("localhost", "root", "", "myfkclub");

    if (!$link) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Inputs
    $userName    = $_POST['userName'];
    $userEmail   = $_POST['userEmail'];
    $userContact = $_POST['userContact'];
    $password    = $_POST['password'];
    $confirm     = $_POST['confirmPassword'];

    // Password check
    if ($password !== $confirm) {
        header("Location: adminsignup.php?error=Passwords do not match");
        exit();
    }

    // File check
    if (!isset($_FILES['userPhoto']) || $_FILES['userPhoto']['error'] != 0) {
        header("Location: adminsignup.php?error=Profile photo required");
        exit();
    }

    $photoName = $_FILES['userPhoto']['name'];
    $tmpName   = $_FILES['userPhoto']['tmp_name'];

    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $uniquePhotoName = time() . "_" . basename($photoName);
    $targetFile = $targetDir . $uniquePhotoName;

    if (!move_uploaded_file($tmpName, $targetFile)) {
        header("Location: adminsignup.php?error=Failed to upload photo");
        exit();
    }

    // Hash password
    $hashedPass = password_hash($password, PASSWORD_DEFAULT);
    $roleID = 1;

    // INSERT using prepared statement
    $stmt = $link->prepare("
        INSERT INTO user (userName, userEmail, userContact, userPhoto, userPass, roleID)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssi",
        $userName,
        $userEmail,
        $userContact,
        $uniquePhotoName,
        $hashedPass,
        $roleID
    );

    if ($stmt->execute()) {

        // ✅ THIS works only if userID is AUTO_INCREMENT INT
        $generatedID = $stmt->insert_id;

        header("Location: login.php?success=Registered! Your ID: " . $generatedID);
        exit();

    } else {
        header("Location: adminsignup.php?error=DB Error");
        exit();
    }

    $stmt->close();
    $link->close();
}
?>