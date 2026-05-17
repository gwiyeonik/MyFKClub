<?php
/**
 * admin_manage_users_api.php
 * Handles all database operations for user management
 */

session_start();

// Security check - only allow admins
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? null) !== 1) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Database Connection
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) { 
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Connection failed: ' . mysqli_connect_error()]));
}

// Get the action from request
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

header('Content-Type: application/json');

switch ($action) {
    
    case 'get_users':
        // Fetch all users from database
        $userQuery = "SELECT userID, userName, userEmail, userContact, roleID FROM user ORDER BY userID ASC";
        $usersResult = mysqli_query($link, $userQuery);
        
        if (!$usersResult) {
            echo json_encode(['success' => false, 'message' => 'Query failed: ' . mysqli_error($link)]);
            break;
        }
        
        $users = [];
        while ($row = mysqli_fetch_assoc($usersResult)) {
            $users[] = $row;
        }
        
        echo json_encode(['success' => true, 'users' => $users]);
        break;

    case 'add_user':
        // Add a new user to database
        $userID = isset($_POST['userID']) ? mysqli_real_escape_string($link, $_POST['userID']) : '';
        $userName = isset($_POST['userName']) ? mysqli_real_escape_string($link, $_POST['userName']) : '';
        $userEmail = isset($_POST['userEmail']) ? mysqli_real_escape_string($link, $_POST['userEmail']) : '';
        $userContact = isset($_POST['userContact']) ? mysqli_real_escape_string($link, $_POST['userContact']) : '';
        $userPass = isset($_POST['userPass']) ? $_POST['userPass'] : '';
        $roleID = isset($_POST['roleID']) ? (int)$_POST['roleID'] : 3;
        
        // Validate required fields
        if (empty($userID) || empty($userName) || empty($userEmail) || empty($userPass)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            break;
        }
        
        // Check if user already exists
        $checkQuery = "SELECT userID FROM user WHERE userID = '$userID'";
        $checkResult = mysqli_query($link, $checkQuery);
        if (mysqli_num_rows($checkResult) > 0) {
            echo json_encode(['success' => false, 'message' => 'User ID already exists']);
            break;
        }
        
        // Hash password
        $hashedPassword = password_hash($userPass, PASSWORD_BCRYPT);
        
        // Insert user
        $insertQuery = "INSERT INTO user (userID, userName, userEmail, userContact, userPass, roleID) 
                       VALUES ('$userID', '$userName', '$userEmail', '$userContact', '$hashedPassword', $roleID)";
        
        if (mysqli_query($link, $insertQuery)) {
            echo json_encode(['success' => true, 'message' => 'User added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Insert failed: ' . mysqli_error($link)]);
        }
        break;

    case 'update_user':
        // Update user information
        $oldUserID = isset($_POST['oldUserID']) ? mysqli_real_escape_string($link, $_POST['oldUserID']) : '';
        $newUserID = isset($_POST['userID']) ? mysqli_real_escape_string($link, $_POST['userID']) : '';
        $userName = isset($_POST['userName']) ? mysqli_real_escape_string($link, $_POST['userName']) : '';
        $userEmail = isset($_POST['userEmail']) ? mysqli_real_escape_string($link, $_POST['userEmail']) : '';
        $userContact = isset($_POST['userContact']) ? mysqli_real_escape_string($link, $_POST['userContact']) : '';
        $roleID = isset($_POST['roleID']) ? (int)$_POST['roleID'] : 3;
        
        if (empty($oldUserID) || empty($newUserID)) {
            echo json_encode(['success' => false, 'message' => 'Old and new User ID are required']);
            break;
        }
        
        if ($newUserID !== $oldUserID) {
            $checkQuery = "SELECT userID FROM user WHERE userID = '$newUserID'";
            $checkResult = mysqli_query($link, $checkQuery);
            if (mysqli_num_rows($checkResult) > 0) {
                echo json_encode(['success' => false, 'message' => 'New User ID already exists']);
                break;
            }
        }
        
        $updateQuery = "UPDATE user SET userID = '$newUserID', userName = '$userName', userEmail = '$userEmail', 
                       userContact = '$userContact', roleID = $roleID WHERE userID = '$oldUserID'";
        
        if (mysqli_query($link, $updateQuery)) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed: ' . mysqli_error($link)]);
        }
        break;

    case 'get_user':
        // Get single user details
        $userID = isset($_GET['userID']) ? mysqli_real_escape_string($link, $_GET['userID']) : '';
        
        if (empty($userID)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            break;
        }
        
        $query = "SELECT userID, userName, userEmail, userContact, roleID FROM user WHERE userID = '$userID'";
        $result = mysqli_query($link, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        break;

    case 'get_club_memberships':
        $clubID = isset($_GET['clubID']) ? (int)$_GET['clubID'] : 0;
        if ($clubID <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid club ID']);
            break;
        }

        $query = "SELECT cm.membershipID, u.userID, u.userName FROM clubmembership cm JOIN user u ON cm.userID = u.userID WHERE cm.clubID = $clubID ORDER BY u.userName";
        $result = mysqli_query($link, $query);
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Query failed: ' . mysqli_error($link)]);
            break;
        }

        $memberships = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $memberships[] = $row;
        }

        echo json_encode(['success' => true, 'memberships' => $memberships]);
        break;

    case 'get_committee_positions':
        $result = mysqli_query($link, "SELECT DISTINCT commiteePosition FROM clubcommittee ORDER BY commiteePosition");
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Query failed: ' . mysqli_error($link)]);
            break;
        }

        $positions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['commiteePosition'])) {
                $positions[] = $row['commiteePosition'];
            }
        }

        if (empty($positions)) {
            $positions = ['President', 'Vice President', 'Secretary', 'Treasurer', 'Member'];
        }

        echo json_encode(['success' => true, 'positions' => $positions]);
        break;

    case 'assign_committee':
        $membershipID = isset($_POST['membershipID']) ? (int)$_POST['membershipID'] : 0;
        $committeePosition = isset($_POST['committeePosition']) ? mysqli_real_escape_string($link, trim($_POST['committeePosition'])) : '';

        if ($membershipID <= 0 || empty($committeePosition)) {
            echo json_encode(['success' => false, 'message' => 'Membership ID and position are required']);
            break;
        }

        $membershipQuery = "SELECT userID FROM clubmembership WHERE membershipID = $membershipID LIMIT 1";
        $membershipResult = mysqli_query($link, $membershipQuery);
        if (!$membershipResult || mysqli_num_rows($membershipResult) === 0) {
            echo json_encode(['success' => false, 'message' => 'Club membership not found']);
            break;
        }

        $membershipRow = mysqli_fetch_assoc($membershipResult);
        $userID = mysqli_real_escape_string($link, $membershipRow['userID']);

        $checkQuery = "SELECT membershipID FROM clubcommittee WHERE membershipID = $membershipID LIMIT 1";
        $checkResult = mysqli_query($link, $checkQuery);
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            echo json_encode(['success' => false, 'message' => 'This member already has a committee assignment']);
            break;
        }

        $insertQuery = "INSERT INTO clubcommittee (membershipID, commiteePosition) VALUES ($membershipID, '$committeePosition')";
        if (!mysqli_query($link, $insertQuery)) {
            echo json_encode(['success' => false, 'message' => 'Insert failed: ' . mysqli_error($link)]);
            break;
        }

        $updateUserQuery = "UPDATE user SET roleID = 2 WHERE userID = '$userID'";
        if (!mysqli_query($link, $updateUserQuery)) {
            echo json_encode(['success' => false, 'message' => 'Failed to update user role: ' . mysqli_error($link)]);
            break;
        }

        echo json_encode(['success' => true, 'message' => 'Committee assigned successfully']);
        break;

    case 'delete_user':
        // Delete a user
        $userID = isset($_POST['userID']) ? mysqli_real_escape_string($link, $_POST['userID']) : '';
        
        if (empty($userID)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            break;
        }
        
        // Prevent deleting self
        if ($userID == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
            break;
        }
        
        $deleteQuery = "DELETE FROM user WHERE userID = '$userID'";
        
        if (mysqli_query($link, $deleteQuery)) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed: ' . mysqli_error($link)]);
        }
        break;

    case 'search_users':
        // Search for users
        $searchTerm = isset($_GET['searchTerm']) ? mysqli_real_escape_string($link, $_GET['searchTerm']) : '';
        
        if (strlen($searchTerm) < 1) {
            echo json_encode(['success' => false, 'message' => 'Search term required']);
            break;
        }
        
        $query = "SELECT userID, userName, userEmail, userContact, roleID FROM user 
                  WHERE userID LIKE '%$searchTerm%' OR userName LIKE '%$searchTerm%' OR userEmail LIKE '%$searchTerm%'
                  ORDER BY userID ASC LIMIT 20";
        $result = mysqli_query($link, $query);
        
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        
        echo json_encode(['success' => true, 'users' => $users]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

mysqli_close($link);
?>
