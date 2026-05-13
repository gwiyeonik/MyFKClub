<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyFKClub Login</title>
  <link rel="stylesheet" href="../CSS/login.css">
  <style>
    .error-msg { color: #d9534f; background: #f2dede; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
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

        <!-- Display error message if redirect contains 'error' -->
        <?php if (isset($_GET['error'])): ?>
          <div class="error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <!-- Form action points to auth.php -->
        <form class="login-form" action="auth.php" method="post">
          <label class="form-group">
            <span>User ID</span>
            <input type="text" name="userId" placeholder="User ID..." autocomplete="username" required>
          </label>

          <label class="form-group">
            <span>Password</span>
            <input type="password" name="password" placeholder="Password..." autocomplete="current-password" required>
          </label>

          <label class="form-group select-group">
            <span>Select role</span>
            <select name="role" required>
              <option value="" disabled selected>Select role</option>
              <option value="admin">Admin</option>
              <option value="student">Student</option>
              <option value="committee">Committee</option>
            </select>
          </label>

          <button type="submit" name="login_btn" class="primary-button">Login</button>

          <div class="utility-row">
            <a href="#" class="text-link">Forgot Password?</a>
          </div>
        </form>

        <div class="signup-row">
          <a href="adminsignup.php" class="signup-link">Sign Up (Admin)</a>
          <span class="separator">|</span>
          <a href="studentsignup.php" class="signup-link">Sign Up (Student)</a>
        </div>
      </div>
    </main>
  </div>
</body>
</html>