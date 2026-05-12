<?php
session_start(); // Start session to remember the user after they log in

if (isset($_POST['login_btn'])) {

    // 1. Database Connection
    $link = mysqli_connect("localhost", "root", "", "myfkclub");
    if (!$link) { die("Connection failed: " . mysqli_connect_error()); }

    // 2. Get Data from Login Form
    $userId   = mysqli_real_escape_string($link, $_POST['userId']);
    $password = $_POST['password']; // Don't escape the password yet, we need the raw string to verify it
    $roleType = $_POST['role'];     // 'admin', 'student', or 'committee'

    // 3. Map the role string to the numerical ID (1, 2, 3)
    $roleMap = [
        'admin'     => 1,
        'committee' => 2,
        'student'   => 3
    ];
    $roleID = $roleMap[$roleType] ?? 3;

    // 4. Query the database for the user
    $query = "SELECT * FROM user WHERE userID = '$userId' AND roleID = '$roleID'";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // 5. Verify Password
        // password_verify checks if the raw password matches the $2y$ hash in your DB
        if (password_verify($password, $user['userPass'])) {
            
            // Set Session variables
            $_SESSION['user_id']   = $user['userID'];
            $_SESSION['user_name'] = $user['userName'];
            $_SESSION['role_id']   = $user['roleID'];

            // 6. Redirect to dashboard
            header("Location: " . $roleType . "_dashboard.php");
            exit();

        } else {
            header("Location: login.php?error=Incorrect Password");
            exit();
        }
    } else {
        header("Location: login.php?error=User ID not found or Role mismatch");
        exit();
    }

    mysqli_close($link);
} else {
    header("Location: login.php");
    exit();
}
?>