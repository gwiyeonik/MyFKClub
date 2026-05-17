<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

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

/* ================= SECURITY ================= */
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? null) != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

/* ================= GET USERS ================= */
if ($action === 'get_users') {

$result = $conn->query("
    SELECT userID, userName, userEmail, userContact, roleID, userStatus 
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

    $res = $conn->query("SELECT MAX(CAST(userID AS UNSIGNED)) AS maxID FROM user");
    $row = $res->fetch_assoc();

    $nextID = ($row['maxID'] ?? 0) + 1;

    echo json_encode([
        'success' => true,
        'nextID' => $nextID
    ]);
    exit;
}

/* ================= ADD USER ================= */
if ($action === 'add_user') {

    $name = $_POST['userName'] ?? '';
    $email = $_POST['userEmail'] ?? '';
    $contact = $_POST['userContact'] ?? '';
    $pass = password_hash($_POST['userPass'] ?? '', PASSWORD_DEFAULT);
    $role = $_POST['roleID'] ?? 3;

    // AUTO ID
    $res = $conn->query("SELECT MAX(userID) AS maxID FROM user");
    $row = $res->fetch_assoc();
    $userID = ($row['maxID']) ? ((int)$row['maxID'] + 1) : 1;

    // ================= PHOTO UPLOAD =================
    $photoPath = null;

    if (!empty($_FILES['userPhoto']['name'])) {

        $uploadDir = "uploads/user/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES['userPhoto']['name']);
        $targetFile = $uploadDir . $fileName;

        move_uploaded_file($_FILES['userPhoto']['tmp_name'], $targetFile);

        $photoPath = $targetFile;
    }

    $stmt = $conn->prepare("
        INSERT INTO user
        (userID, userName, userEmail, userContact, userPass, roleID, userStatus, userPhoto)
        VALUES (?, ?, ?, ?, ?, ?, 'Active', ?)
    ");

    $stmt->bind_param("issssis", $userID, $name, $email, $contact, $pass, $role, $photoPath);

    echo json_encode([
        'success' => $stmt->execute(),
        'userID' => $userID,
        'photo' => $photoPath
    ]);
    exit;
}

/* ================= UPDATE USER ================= */
if ($action === 'update_user') {

    $userID = $_POST['oldUserID'] ?? '';
    $name = $_POST['userName'] ?? '';
    $email = $_POST['userEmail'] ?? '';
    $contact = $_POST['userContact'] ?? '';
    $role = $_POST['roleID'] ?? 3;

    // GET OLD PHOTO
    $res = $conn->query("SELECT userPhoto FROM user WHERE userID='$userID'");
    $old = $res->fetch_assoc();
    $photoPath = $old['userPhoto'] ?? null;

    // NEW PHOTO (optional replace)
    if (!empty($_FILES['userPhoto']['name'])) {

        $uploadDir = "uploads/user/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES['userPhoto']['name']);
        $targetFile = $uploadDir . $fileName;

        move_uploaded_file($_FILES['userPhoto']['tmp_name'], $targetFile);

        $photoPath = $targetFile;
    }

    $stmt = $conn->prepare("
        UPDATE user 
        SET userName=?, userEmail=?, userContact=?, roleID=?, userPhoto=?
        WHERE userID=?
    ");

    $stmt->bind_param("ssisss", $name, $email, $contact, $role, $photoPath, $userID);

    echo json_encode([
        'success' => $stmt->execute()
    ]);
    exit;
}

/* ================= DELETE USER ================= */
if ($action === 'delete_user') {

    $userID = (int)($_POST['userID'] ?? 0);

    // GET PHOTO FIRST
    $res = $conn->query("SELECT userPhoto FROM user WHERE userID=$userID");
    $row = $res->fetch_assoc();

    if (!empty($row['userPhoto']) && file_exists($row['userPhoto'])) {
        unlink($row['userPhoto']);
    }

    $stmt = $conn->prepare("DELETE FROM user WHERE userID=?");
    $stmt->bind_param("s", $userID);

    echo json_encode([
        'success' => $stmt->execute()
    ]);
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