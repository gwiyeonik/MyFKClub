<?php
session_start(); 

if (isset($_POST['login_btn'])) {

    // 1. Database Connection
    $link = mysqli_connect("localhost", "root", "", "myfkclub");
    if (!$link) { die("Connection failed: " . mysqli_connect_error()); }

    $rawInput = trim($_POST['userID'] ?? '');
    $password = $_POST['password'];
    $roleType = $_POST['role'];

    if ($rawInput === '') {
        header("Location: login.php?error=User ID is required");
        exit();
    }

    $digitsOnly = preg_replace('/\D+/', '', $rawInput);
    if ($digitsOnly === '') {
        header("Location: login.php?error=Invalid User ID");
        exit();
    }

    $numericID = ltrim($digitsOnly, '0');
    if ($numericID === '') {
        $numericID = '0';
    }

    $paddedID = str_pad($numericID, 4, '0', STR_PAD_LEFT);
    $prefixedID = 'US' . $paddedID;
    $safeNumericID = mysqli_real_escape_string($link, $numericID);
    $safePaddedID = mysqli_real_escape_string($link, $paddedID);
    $safePrefixedID = mysqli_real_escape_string($link, $prefixedID);

    // 2. Query the database based on possible user ID formats
    $query = "SELECT * FROM user WHERE userID IN ('$safeNumericID', '$safePaddedID', '$safePrefixedID') LIMIT 1";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // 3. Verify Password
        if (password_verify($password, $user['userPass'])) {
            
            $dbRoleID = (int)$user['roleID'];
            $userID   = $user['userID'];

            // 4. COMMITTEE LOGIN LOGIC
            // Checks if student (Role 3) is assigned in the clubcommittee table
            if ($roleType === 'committee' && $dbRoleID === 3) {
                
                // UPDATED: Changed 'membership' to 'clubmembership' to match your database
                $committeeQuery = "SELECT cc.committeePosition 
                                   FROM clubcommittee cc
                                   JOIN clubmembership cm ON cc.membershipID = cm.membershipID
                                   WHERE cm.userID = '$userID' LIMIT 1";
                
                $commResult = mysqli_query($link, $committeeQuery);
                
                if (mysqli_num_rows($commResult) === 1) {
                    $commData = mysqli_fetch_assoc($commResult);
                    
                    // Success: Student is recognized as committee
                $_SESSION['user_id']   = $userID;
                $_SESSION['user_name'] = $user['userName'];
                $_SESSION['role']      = 'committee';
                $_SESSION['roleID']    = $dbRoleID;
                $_SESSION['position']  = $commData['committeePosition'];
                    
                    header('Location: committee_dashboard.php');
                    exit();
                } else {
                    header("Location: login.php?error=You are not assigned to any committee yet.");
                    exit();
                }
            }

            // 5. STANDARD LOGIN LOGIC (Admin and regular Students)
            $roleMap = ['admin' => 1, 'student' => 3];
            $expectedID = $roleMap[$roleType] ?? 0;

            if ($dbRoleID === $expectedID) {
                $_SESSION['user_id']   = $userID;
                $_SESSION['user_name'] = $user['userName'];
                $_SESSION['role']      = $roleType;
                $_SESSION['roleID']    = $dbRoleID;
                if ($roleType === 'admin') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: student_dashboard.php');
                }
                exit();
            } else {
                header("Location: login.php?error=Role mismatch for this user");
                exit();
            }

        } else {
            header("Location: login.php?error=Incorrect Password");
            exit();
        }
    } else {
        header("Location: login.php?error=User ID not found");
        exit();
    }
    mysqli_close($link);
}
?>