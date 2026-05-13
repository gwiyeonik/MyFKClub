<?php
// 1. Establish connection to fetch the next available ID
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) { 
    die("Connection failed: " . mysqli_connect_error()); 
}

// 2. Get the current auto_increment status
$result = mysqli_query($link, "SHOW TABLE STATUS LIKE 'user'");
$row = mysqli_fetch_assoc($result);
$nextID = $row['Auto_increment'] ?? 1;

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sign Up | MyFKClub</title>
  <!-- Main CSS -->
  <link rel="stylesheet" href="../CSS/login.css">
  <!-- New Specific Admin CSS -->
  <link rel="stylesheet" href="../CSS/admin.css">
</head>
<body>
  <div class="page-shell">
    <main class="login-stage">
      <div class="login-card">
        <div class="login-card-header">
          <img class="header-logo" src="../Image/fkclub.jpg" alt="FKClub logo">
          <h1>MyFKClub</h1>
        </div>

        <div class="signup-heading">
          <h2>Admin First-Time Registration</h2>
          <p>Verify your details and upload a profile photo</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
          <div class="msg error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form class="login-form" action="register_admin.php" method="post" enctype="multipart/form-data">
          
          <label class="form-group">
            <span>User ID</span>
            <input type="text" name="userId" value="<?php echo $nextID; ?>" readonly required>
          </label>

          <label class="form-group">
            <span>User Name</span>
            <input type="text" name="userName" placeholder="Full Name" required>
          </label>

          <label class="form-group">
            <span>User Email</span>
            <input type="email" name="userEmail" placeholder="user@gmail.com" required>
          </label>

          <label class="form-group">
            <span>User Contact</span>
            <input type="text" name="userContact" placeholder="Phone Number" required>
          </label>

          <label class="form-group">
            <span>Profile Photo</span>
            <input type="file" name="userProfile" accept="image/*" required>
          </label>

          <label class="form-group">
            <span>Password</span>
            <input type="password" name="password" placeholder="Create Password" required>
          </label>

          <label class="form-group">
            <span>Confirm Password</span>
            <input type="password" name="confirmPassword" placeholder="Repeat Password" required>
          </label>

          <button type="submit" name="signup_btn" class="primary-button">Confirm Admin Details</button>
          <a href="login.php" class="text-link">← Back</a>
        </form>
      </div>
    </main>
  </div>
</body>
</html>