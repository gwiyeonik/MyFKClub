<?php
session_start(); // Start session to remember the user after they log in

if (isset($_POST['login_btn'])) {

    // 1. Database Connection
    $link = mysqli_connect("localhost", "root", "", "myfkclub");
    if (!$link) { die("Connection failed: " . mysqli_connect_error()); }

    // 2. ✅ UPDATED: Get User Name from Login Form instead of User ID
    $userName = mysqli_real_escape_string($link, $_POST['userName']);
    $password = $_POST['password']; // Raw string needed for verification
    $roleType = $_POST['role'];     // 'admin', 'student', or 'committee'

    // 3. Map the role string to the numerical ID (1, 2, 3)
    $roleMap = [
        'admin'     => 1,
        'committee' => 2,
        'student'   => 3
    ];
    $roleID = $roleMap[$roleType] ?? 3;

    // 4. ✅ UPDATED: Query the database checking userName column instead of userID
    $query = "SELECT * FROM user WHERE userName = '$userName' AND roleID = '$roleID'";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // 5. Verify Password
        if (password_verify($password, $user['userPass'])) {
            
            // Set Session variables
            $_SESSION['user_id']   = $user['userID']; // Still save ID for database relational keys
            $_SESSION['user_name'] = $user['userName'];
            
            // Force cast the database value to an Integer to pass strict dashboard checks
            $_SESSION['role_id']   = (int)$user['roleID']; 
            $_SESSION['role']      = $roleType;

            // 6. Redirect to dashboard securely
            if ($_SESSION['role_id'] === 1 || $roleType === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header("Location: " . $roleType . "_dashboard.php");
            }
            exit();

        } else {
            header("Location: login.php?error=Incorrect Password");
            exit();
        }
    } else {
        // ✅ UPDATED: Clean error message referencing User Name
        header("Location: login.php?error=User Name not found or Role mismatch");
        exit();
    }

    mysqli_close($link);
} else {
    header("Location: login.php");
    exit();
}
?>