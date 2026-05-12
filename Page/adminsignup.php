<?php
// 1. Establish connection to fetch the next available ID
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) { 
    die("Connection failed: " . mysqli_connect_error()); 
}

// 2. Get the current auto_increment status for the user table
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
  <link rel="stylesheet" href="../CSS/login.css">
  <style>
    /* Styling for error/success messages */
    .msg { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
    .error { color: #d9534f; background: #f2dede; border: 1px solid #d9534f; }
    .success { color: #3c763d; background: #dff0d8; border: 1px solid #3c763d; }
    
    /* Making the ID field look locked but readable */
    input[readonly] { background-color: #f4f4f4; cursor: not-allowed; color: #777; }
  </style>
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
          <p>Verify your admin details to begin username registration</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
          <div class="msg error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
          <div class="msg success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <form class="login-form" action="register_admin.php" method="post">
          <label class="form-group">
            <span>User ID</span>
            <input type="text" name="userId" value="<?php echo $nextID; ?>" readonly required>
          </label>

          <label class="form-group">
            <span>User Name</span>
            <input type="text" name="userName" placeholder="User Name" required>
          </label>

          <label class="form-group">
            <span>User Email</span>
            <input type="email" name="userEmail" placeholder="User Email" required>
          </label>

          <label class="form-group">
            <span>Password</span>
            <input type="password" name="password" placeholder="Password" required>
          </label>

          <label class="form-group">
            <span>Confirm Password</span>
            <input type="password" name="confirmPassword" placeholder="Confirm Password" required>
          </label>

          <button type="submit" name="signup_btn" class="primary-button">Confirm Admin Details</button>
          <a href="login.php" class="text-link">← Back</a>
        </form>
      </div>
    </main>
  </div>
</body>
</html>