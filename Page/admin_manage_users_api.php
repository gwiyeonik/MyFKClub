<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    strtolower(trim($_SESSION['role'])) !== 'admin'
) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'session' => $_SESSION
    ]);
    exit;
}
error_reporting(0);
ini_set('display_errors', 0);

$conn = mysqli_connect("localhost", "root", "", "myfkclub");

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

function ensureRegistrationRequestsTable($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS registration_requests (
        requestID INT AUTO_INCREMENT PRIMARY KEY,
        userName VARCHAR(255) NOT NULL,
        userEmail VARCHAR(255) NOT NULL,
        userContact VARCHAR(100),
        userProfile VARCHAR(255),
        userPass VARCHAR(255) NOT NULL,
        roleID INT NOT NULL DEFAULT 3,
        requestedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}


$action = $_GET['action'] ?? $_POST['action'] ?? '';

/* ================= GET USERS ================= */
if ($action === 'get_users' || $action === 'fetch_users_list') {

    // ✅ FIXED: Completely removed 'userStatus' so it matches your 7 schema columns perfectly!
    $result = $conn->query("
        SELECT userID, userName, userEmail, userContact, roleID, userProfile 
        FROM user
        ORDER BY userID ASC
    ");

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => $conn->error
        ]);
        exit;
    }

    $users = [];

    while ($row = $result->fetch_assoc()) {
        // Automatically inject human-readable names for your frontend script to render
        if ($row['roleID'] == 1) {
            $row['roleName'] = 'Admin';
        } elseif ($row['roleID'] == 2) {
            $row['roleName'] = 'Committee';
        } else {
            $row['roleName'] = 'Student';
        }
        $users[] = $row;
    }

    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    exit;
}

/* ================= GET USER ================= */
if ($action === 'get_user') {

    $userID = $_GET['userID'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM user WHERE userID=?");
    $stmt->bind_param("s", $userID);
    $stmt->execute();

    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    echo json_encode([
        'success' => (bool)$user,
        'user' => $user
    ]);
    exit;
}

if ($action === 'get_next_user_id') {

    $res = $conn->query("SELECT MAX(
            CASE
                WHEN userID LIKE 'US%' THEN CAST(SUBSTRING(userID, 3) AS UNSIGNED)
                WHEN userID RLIKE '^[0-9]+' THEN CAST(userID AS UNSIGNED)
                ELSE 0
            END
        ) AS maxID
        FROM user");
    $row = $res->fetch_assoc();

    $nextID = ($row['maxID'] ?? 0) + 1;

    echo json_encode([
        'success' => true,
        'nextID' => $nextID
    ]);
    exit;
}

if ($action === 'get_requests') {
    ensureRegistrationRequestsTable($conn);

    $result = $conn->query("SELECT requestID, userName, userEmail, userContact, roleID, requestedAt FROM registration_requests ORDER BY requestedAt DESC");
    if (!$result) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    echo json_encode(['success' => true, 'requests' => $requests]);
    exit;
}

if ($action === 'reject_request') {
    ensureRegistrationRequestsTable($conn);

    $requestID = $_POST['requestID'] ?? '';
    if (empty($requestID)) {
        echo json_encode(['success' => false, 'message' => 'Missing request ID']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM registration_requests WHERE requestID = ?");
    $stmt->bind_param("i", $requestID);
    $success = $stmt->execute();

    echo json_encode(['success' => $success, 'message' => $success ? 'Request rejected' : $stmt->error]);
    exit;
}

/* ================= ADD USER ================= */
if ($action === 'add_user') {

    $name = $_POST['userName'] ?? '';
    $email = $_POST['userEmail'] ?? '';
    $contact = $_POST['userContact'] ?? '';
    $pass = password_hash($_POST['userPass'] ?? '', PASSWORD_DEFAULT);
    $role = $_POST['roleID'] ?? 3;
    $userID = trim($_POST['userID'] ?? '');
    $requestID = $_POST['requestID'] ?? null;

    if ($userID === '') {
        $res = $conn->query("SELECT MAX(
                CASE
                    WHEN userID LIKE 'US%' THEN CAST(SUBSTRING(userID, 3) AS UNSIGNED)
                    WHEN userID RLIKE '^[0-9]+' THEN CAST(userID AS UNSIGNED)
                    ELSE 0
                END
            ) AS maxID
            FROM user");
        $row = $res->fetch_assoc();
        $userID = ($row['maxID'] ?? 0) + 1;
    }

    $checkStmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $checkStmt->bind_param("s", $userID);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'User ID already exists. Please choose a different ID.']);
        exit;
    }
    $checkStmt->close();

    $photoPath = null;
    $photoFile = $_FILES['userProfile'] ?? $_FILES['userPhoto'] ?? null;

    if ($photoFile && !empty($photoFile['name'])) {
        $uploadDir = "uploads/user/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($photoFile['name']);
        $targetFile = $uploadDir . $fileName;
        move_uploaded_file($photoFile['tmp_name'], $targetFile);
        $photoPath = $targetFile;
    }

    $stmt = $conn->prepare("INSERT INTO user (userID, userName, userEmail, userContact, userPass, roleID, userProfile) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $userID, $name, $email, $contact, $pass, $role, $photoPath);
    $success = $stmt->execute();

    if ($success && !empty($requestID)) {
        $rejectStmt = $conn->prepare("DELETE FROM registration_requests WHERE requestID = ?");
        $rejectStmt->bind_param("i", $requestID);
        $rejectStmt->execute();
        $rejectStmt->close();
    }

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'User added successfully' : $stmt->error,
        'userID' => $userID,
        'photo' => $photoPath
    ]);
    exit;
}

/* ================= UPDATE USER ================= */
if ($action === 'update_user') {
    $userID = $_POST['userID'] ?? null;
    $userName = $_POST['userName'] ?? null;
    $userEmail = $_POST['userEmail'] ?? null;
    $userContact = $_POST['userContact'] ?? null;
    $roleID = $_POST['roleID'] ?? null;

    if (!$userID || !$userName || !$userEmail) {
        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
        exit;
    }

    // Check if a new profile image was uploaded
    $photoFile = null;
    if (isset($_FILES['userProfile']) && $_FILES['userProfile']['error'] === UPLOAD_ERR_OK) {
        $photoFile = $_FILES['userProfile'];
    } elseif (isset($_FILES['userPhoto']) && $_FILES['userPhoto']['error'] === UPLOAD_ERR_OK) {
        $photoFile = $_FILES['userPhoto'];
    }

    if ($photoFile) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($photoFile['name']);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($photoFile['tmp_name'], $targetFilePath)) {
            $stmt = $conn->prepare("UPDATE user SET userName=?, userEmail=?, userContact=?, roleID=?, userProfile=? WHERE userID=?");
            $stmt->bind_param("ssssis", $userName, $userEmail, $userContact, $roleID, $targetFilePath, $userID);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save uploaded image']);
            exit;
        }
    } else {
        $stmt = $conn->prepare("UPDATE user SET userName=?, userEmail=?, userContact=?, roleID=? WHERE userID=?");
        $stmt->bind_param("sssis", $userName, $userEmail, $userContact, $roleID, $userID);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

/* ================= DELETE USER ================= */
if ($action === 'delete_user') {

    $userID = trim($_POST['userID'] ?? '');

    if ($userID === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or missing User ID.'
        ]);
        exit;
    }

    $stmt = $conn->prepare("SELECT userProfile FROM user WHERE userID = ?");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['userProfile']) && file_exists($row['userProfile'])) {
            unlink($row['userProfile']);
        }
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM user WHERE userID = ?");
    $stmt->bind_param("s", $userID);
    $success = $stmt->execute();

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'User deleted successfully' : $stmt->error
    ]);
    
    $stmt->close();
    exit;
}

/* ================= CLUB MEMBERS ================= */
if ($action === 'get_club_memberships') {

    $clubID = $_GET['clubID'] ?? '';

    $stmt = $conn->prepare("
        SELECT m.membershipID, u.userID, u.userName
        FROM club_memberships m
        JOIN user u ON u.userID = m.userID
        WHERE m.clubID=?
    ");

    $stmt->bind_param("s", $clubID);
    $stmt->execute();

    $res = $stmt->get_result();
    $data = [];

    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        'success' => true,
        'memberships' => $data
    ]);
    exit;
}

/* ================= POSITIONS ================= */
if ($action === 'get_committee_positions') {

    echo json_encode([
        'success' => true,
        'positions' => [
            "President",
            "Vice President",
            "Secretary",
            "Treasurer",
            "Member"
        ]
    ]);
    exit;
}

/* ================= ASSIGN COMMITTEE ================= */
if ($action === 'assign_committee') {

    $membershipID = $_POST['membershipID'] ?? '';
    $position = $_POST['committeePosition'] ?? '';

    $stmt = $conn->prepare("
        UPDATE club_memberships
        SET committeePosition=?
        WHERE membershipID=?
    ");

    $stmt->bind_param("ss", $position, $membershipID);

    echo json_encode([
        'success' => $stmt->execute()
    ]);
    exit;
}

/* ================= DEFAULT ================= */
echo json_encode([
    'success' => false,
    'message' => 'Invalid action'
]);