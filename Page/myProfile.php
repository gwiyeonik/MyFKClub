<?php
session_start();

// 1. SECURITY CONTROL GATE: Protect page from unauthenticated sessions
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// 2. DATABASE INITIALIZATION
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    die("Database connection error: " . mysqli_connect_error());
}

// Fetch current user credentials securely
if (isset($_SESSION['user_id'])) {
    $userID = mysqli_real_escape_string($link, $_SESSION['user_id']);
    $userDataQuery = mysqli_query($link, "SELECT * FROM user WHERE userID = '$userID'");
} else {
    $sessionName = mysqli_real_escape_string($link, $_SESSION['user_name']);
    $userDataQuery = mysqli_query($link, "SELECT * FROM user WHERE userName = '$sessionName'");
}

$currentUserData = mysqli_fetch_assoc($userDataQuery);

if (!$currentUserData) {
    header("Location: login.php");
    exit();
}

$userID = $currentUserData['userID'];
$roleID = (int)$currentUserData['roleID']; // 1 = admin, 2 = committee, 3 = student

$successMessage = "";
$errorMessage = "";

// ==========================================================================
// 3. CORE ENGINE A: UPDATE PERSONAL DETAILS & IMAGE HANDLER (SUBMITTED FROM MODAL)
// ==========================================================================
if (isset($_POST['commit_profile_update_btn'])) {
    $userName = mysqli_real_escape_string($link, $_POST['userName']);
    $userEmail = mysqli_real_escape_string($link, $_POST['userEmail']);
    $userContact = mysqli_real_escape_string($link, $_POST['userContact']);
    
    $fileNameStored = $currentUserData['userProfile'];

    if (isset($_FILES['userProfile']) && $_FILES['userProfile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['userProfile']['tmp_name'];
        $originalFileName = $_FILES['userProfile']['name'];
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = "profile_" . $userID . "_" . time() . "." . $fileExtension;
            $uploadFileDir = '../Uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $fileNameStored = $newFileName;
                $_SESSION['user_profile'] = $fileNameStored;
            } else {
                $errorMessage = "Failed to move uploaded file to target directory.";
            }
        } else {
            $errorMessage = "Invalid file type extension. Only JPG, JPEG, PNG, and GIF allowed.";
        }
    }

    if (empty($errorMessage)) {
        // 1. UPDATE DATA PERMANENTLY IN MYSQL DATABASE
        $updateQuery = "UPDATE user SET userName = '$userName', userEmail = '$userEmail', userContact = '$userContact', userProfile = '$fileNameStored' WHERE userID = '$userID'";
        
        if (mysqli_query($link, $updateQuery)) {
            // 2. SYNCHRONIZE ACTIVE GLOBAL SESSION TOKENS (Updates Topbar / Side Navigation Names)
            $_SESSION['user_name'] = $userName; 
            $_SESSION['user_profile'] = $fileNameStored; // Ensures header avatars update immediately!
            
            // 3. TRIGGER SET TIMEOUT POPUP STATE
            $successMessage = "Profile updated successfully!";
            
            // 4. SYNC CURRENT ARRAY PERSISTENCE DATA (Updates Read-Only Cards underneath the Modal)
            $currentUserData['userName'] = $userName;
            $currentUserData['userEmail'] = $userEmail;
            $currentUserData['userContact'] = $userContact;
            $currentUserData['userProfile'] = $fileNameStored;
        } else {
            $errorMessage = "Failed to update configuration database metrics: " . mysqli_error($link);
        }
    }
}

// ==========================================================================
// 4. CORE ENGINE B: ENCRYPTED PASSWORD MUTATION HANDLER (MODAL DRIVEN)
// ==========================================================================
if (isset($_POST['update_password_btn'])) {
    $currentPass = $_POST['currentPass'];
    $newPass = $_POST['newPass'];
    $confirmPass = $_POST['confirmPass'];

    if (password_verify($currentPass, $currentUserData['userPass'])) {
        if ($newPass === $confirmPass) {
            $securedHash = password_hash($newPass, PASSWORD_DEFAULT);
            $passUpdate = mysqli_query($link, "UPDATE user SET userPass = '$securedHash' WHERE userID = '$userID'");
            if ($passUpdate) {
                $successMessage = "Password updated successfully.";
                $currentUserData['userPass'] = $securedHash;
            } else {
                $errorMessage = "Failed to update security hashes.";
            }
        } else {
            $errorMessage = "Mismatch error: New passwords do not match.";
        }
    } else {
        $errorMessage = "Current password does not match database record.";
    }
}

// ==========================================================================
// 5. DYNAMIC ROLE-BASED INFORMATION COMPILER
// ==========================================================================
$roleMap = [1 => 'Admin', 2 => 'Committee', 3 => 'Student'];
$roleName = $roleMap[$roleID] ?? 'Student';

$committeePosition = "N/A";
$assignedClubName = "N/A";
$joinedClubs = [];

if ($roleID === 2) {
    $commQuery = "SELECT cc.committeePosition, c.clubName 
                  FROM clubCommittee cc
                  JOIN clubMembership cm ON cc.membershipID = cm.membershipID
                  JOIN club c ON cm.clubID = c.clubID
                  WHERE cm.userID = '$userID' LIMIT 1";
    $commResult = mysqli_query($link, $commQuery);
    if ($commResult && mysqli_num_rows($commResult) > 0) {
        $commRow = mysqli_fetch_assoc($commResult);
        $committeePosition = $commRow['committeePosition'];
        $assignedClubName = $commRow['clubName'];
    }
} elseif ($roleID === 3) {
    $studQuery = "SELECT c.clubName, cm.clubJoinDate 
                  FROM clubMembership cm
                  JOIN club c ON cm.clubID = c.clubID
                  WHERE cm.userID = '$userID' ORDER BY cm.clubJoinDate DESC";
    $studResult = mysqli_query($link, $studQuery);
    if ($studResult) {
        while ($row = mysqli_fetch_assoc($studResult)) {
            $joinedClubs[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($roleName); ?> Profile | MyFKClub</title>
  
  <?php if ($roleID === 1): ?>
    <link rel="stylesheet" href="../CSS/dashboard.css">
  <?php else: ?>
    <link rel="stylesheet" href="../CSS/student_dashboard.css">
  <?php endif; ?>
  
  <link rel="stylesheet" href="../CSS/myProfile.css">
</head>
<body>

  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>
      <nav class="sidebar-nav">
        <?php if ($roleID === 1): ?>
          <a href="admin_dashboard.php" class="sidebar-link">Home</a>
          <a href="admin_manage_users.php" class="sidebar-link">Manage Users</a>
          <a href="admin_student_clubs.php" class="sidebar-link">Student Clubs</a>
          <a href="admin_events.php" class="sidebar-link">Events</a>
          <a href="admin_participation_reports.php" class="sidebar-link">Participation Reports</a>
        <?php elseif ($roleID === 2): ?>
          <a href="committee_dashboard.php" class="sidebar-link">Home</a>
          <a href="#" class="sidebar-link">My Club Panel</a>
          <a href="#" class="sidebar-link">Organize Events</a>
          <a href="#" class="sidebar-link">Attendance Logs</a>
        <?php else: ?>
          <a href="student_dashboard.php" class="sidebar-link">Home</a>
          <a href="#" class="sidebar-link">Clubs</a>
          <a href="#" class="sidebar-link">Events</a>
          <a href="#" class="sidebar-link">Participation</a>
        <?php endif; ?>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title"><?php echo htmlspecialchars($roleName); ?> Profile</div>
        </div>
        <a href="myProfile.php" class="topbar-button active">My Profile</a>
      </div>

      <div class="profile-content-area">
        <?php if(!empty($successMessage)): ?>
          <div id="successBanner" class="status-banner status-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if(!empty($errorMessage)): ?>
          <div class="status-banner status-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <div class="profile-form-wrapper">
          <div class="avatar-upload-container">
            <div class="avatar-circle-frame">
              <?php $photoPath = !empty($currentUserData['userProfile']) ? '../Uploads/'.$currentUserData['userProfile'] : '../Image/default_avatar.png'; ?>
              <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Active User Avatar">
            </div>
          </div>

          <div class="profile-card-layout">
            <div class="profile-card-header-region">
              <span class="role-badge role-color-<?php echo $roleID; ?>"><?php echo htmlspecialchars($roleName); ?></span>
            </div>

            <div class="profile-field-row">
              <label>Full Name</label>
              <input type="text" value="<?php echo htmlspecialchars($currentUserData['userName']); ?>" readonly class="readonly-field">
            </div>

            <div class="profile-field-row">
              <label>User ID</label>
              <input type="text" value="<?php echo htmlspecialchars($currentUserData['userID']); ?>" readonly class="readonly-field">
            </div>

            <div class="profile-field-row">
              <label>Email Address</label>
              <input type="text" value="<?php echo htmlspecialchars($currentUserData['userEmail']); ?>" readonly class="readonly-field">
            </div>

            <div class="profile-field-row">
              <label>Current Role</label>
              <input type="text" value="<?php echo htmlspecialchars($roleName); ?>" readonly class="readonly-field">
            </div>

            <div class="profile-field-row">
              <label>Contact Number</label>
              <input type="text" value="<?php echo htmlspecialchars($currentUserData['userContact']); ?>" readonly class="readonly-field">
            </div>
            
            <?php if ($roleID !== 1): ?>
              <div class="profile-field-row">
                <label>Committee Position</label>
                <input type="text" value="<?php echo htmlspecialchars($committeePosition); ?>" readonly class="readonly-field">
              </div>
            <?php endif; ?>

            <?php if ($roleID === 1): ?>
              <div class="dynamic-divider"></div>
              <div class="admin-access-notice-box">
                <h4>System Root Privileges Granted</h4>
                <p>You have system administrator capabilities. Membership logs and database structures can be altered from your primary administrative toolbar panels.</p>
              </div>
            <?php elseif ($roleID === 2): ?>
              <div class="dynamic-divider"></div>
              <div class="profile-field-row">
                <label>Assigned Club Track</label>
                <input type="text" value="<?php echo htmlspecialchars($assignedClubName); ?>" readonly class="readonly-field">
              </div>
            <?php elseif ($roleID === 3): ?>
              <div class="dynamic-divider"></div>
              <div class="membership-section-title">Registered Club Memberships</div>
              <?php if(count($joinedClubs) > 0): ?>
                <table class="membership-table-view">
                  <thead>
                    <tr>
                      <th style="width: 70px; text-align: center;">No</th>
                      <th>Club Name</th>
                      <th>Affiliation Join Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($joinedClubs as $idx => $club): ?>
                      <tr>
                        <td style="text-align: center; font-weight: 600; color: #718096;"><?php echo $idx + 1; ?></td>
                        <td class="club-highlight-cell"><?php echo htmlspecialchars($club['clubName']); ?></td>
                        <td style="color: #4a5568;"><?php echo date('d M Y', strtotime($club['clubJoinDate'])); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p class="empty-track-text">This registration entity is not currently linked with any club memberships.</p>
              <?php endif; ?>
            <?php endif; ?>

            <div class="profile-action-buttons">
              <button type="button" class="btn-action btn-teal" onclick="toggleEditProfileModal(true)">Update Profile</button>
              <button type="button" class="btn-action btn-secondary" onclick="togglePasswordModal(true)">Change Password</button>
            </div>
            
          </div>
        </div>
      </div>
    </main>
  </div>

  <div class="modal-background-overlay" id="editProfileModal" style="display: none;">
    <div class="modal-card-box" style="max-width: 550px;">
      <h3>Edit Personal Information</h3>
      <p class="modal-descriptor-text">Update your account display details and avatar image below.</p>
      
      <form action="myProfile.php" method="POST" enctype="multipart/form-data">
        
        <div class="avatar-upload-container" style="margin-bottom: 20px;">
          <div class="avatar-circle-frame">
            <img id="avatarPreview" src="<?php echo htmlspecialchars($photoPath); ?>" alt="Preview Avatar">
          </div>
          <label for="fileInput" class="file-input-label">Upload New Photo</label>
          <input type="file" id="fileInput" name="userProfile" accept="image/*" onchange="previewImage(event)">
        </div>

        <div class="modal-group">
          <label for="userName">Full Name</label>
          <input type="text" id="userName" name="userName" value="<?php echo htmlspecialchars($currentUserData['userName']); ?>" required>
        </div>

        <div class="modal-group">
          <label for="userEmail">Email Address</label>
          <input type="email" id="userEmail" name="userEmail" value="<?php echo htmlspecialchars($currentUserData['userEmail']); ?>" required>
        </div>

        <div class="modal-group">
          <label for="userContact">Contact Number</label>
          <input type="text" id="userContact" name="userContact" value="<?php echo htmlspecialchars($currentUserData['userContact']); ?>" required>
        </div>
        
        <div class="modal-footer-btns" style="margin-top: 25px;">
          <button type="button" class="btn-action btn-modal-cancel" onclick="toggleEditProfileModal(false)">Cancel</button>
          <button type="submit" name="commit_profile_update_btn" class="btn-action btn-teal">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-background-overlay" id="passwordModal" style="display: none;">
    <div class="modal-card-box">
      <h3>Change Password Verification</h3>
      <p class="modal-descriptor-text">Please verify your current tokens prior to committing changes.</p>
      
      <form action="myProfile.php" method="POST">
        <div class="modal-group">
          <label>Current Password</label>
          <input type="password" name="currentPass" placeholder="••••••••" required>
        </div>
        <div class="modal-group">
          <label>New Password</label>
          <input type="password" name="newPass" placeholder="Minimum 8 characters" required>
        </div>
        <div class="modal-group">
          <label>Confirm New Password</label>
          <input type="password" name="confirmPass" placeholder="Retype new password" required>
        </div>
        
        <div class="modal-footer-btns">
          <button type="button" class="btn-action btn-modal-cancel" onclick="togglePasswordModal(false)">Cancel</button>
          <button type="submit" name="update_password_btn" class="btn-action btn-teal">Update Password</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // FIXED: Automatic 3-Second Fade-out Listener for Alert Banners
    document.addEventListener("DOMContentLoaded", function() {
        const successBanner = document.getElementById('successBanner');
        
        if (successBanner) {
            // Setup base style parameters for smooth transitional drifting
            successBanner.style.opacity = "1";
            successBanner.style.transition = "opacity 0.5s ease-out, transform 0.5s ease-out";
            
            // Wait exactly 3 seconds, then start animation fading sequence
            setTimeout(function() {
                successBanner.style.opacity = "0";
                successBanner.style.transform = "translateY(-10px)"; // Upward slide animation
                
                // Clear block space from display window completely after transition ends
                setTimeout(function() {
                    successBanner.style.display = "none";
                }, 500); 
            }, 3000);
        }
    });

    function toggleEditProfileModal(shouldOpen) {
        const modal = document.getElementById('editProfileModal');
        modal.style.display = shouldOpen ? 'flex' : 'none';
    }

    function togglePasswordModal(shouldOpen) {
        const modal = document.getElementById('passwordModal');
        modal.style.display = shouldOpen ? 'flex' : 'none';
    }

    function previewImage(event) {
        const fileReader = new FileReader();
        fileReader.onload = function() {
            const previewTarget = document.getElementById('avatarPreview');
            previewTarget.src = fileReader.result;
        }
        if(event.target.files[0]) {
            fileReader.readAsDataURL(event.target.files[0]);
        }
    }
  </script>
</body>
</html>