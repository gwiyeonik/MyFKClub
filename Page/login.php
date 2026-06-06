<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyFKClub Login</title>

  <link rel="stylesheet" href="../CSS/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
  <div class="page-shell">
    <main class="login-stage">
      <div class="login-card">

        <div class="login-card-header">
          <img class="header-logo" src="../Image/fkclub.jpg" alt="FKClub logo">
          <h1>MyFKClub</h1>
        </div>

        <?php if (isset($_GET['error'])): ?>
          <div class="error-msg">
            <?php echo htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>

        <form class="login-form" action="auth.php" method="post">

          <label class="form-group">
            <span>Username</span>
            <input 
              type="text" 
              id="userName" 
              name="userName" 
              placeholder="Enter your User Name..." 
              required>
          </label>

          <label class="form-group">
            <span>Password</span>

            <div class="password-container">
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Password..."
                autocomplete="current-password"
                required>

              <span class="toggle-password" onclick="togglePassword()">
                <i id="eyeIcon" class="fa-solid fa-eye"></i>
              </span>
            </div>
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

          <button type="submit" name="login_btn" class="primary-button">
            Login
          </button>

          <div class="utility-row">
            <a href="#" class="text-link">Forgot Password?</a>
          </div>
        </form>

        <div class="signup-row">
          <a href="adminsignup.php" class="signup-link">Sign Up (Admin)</a>
          
        </div>

      </div>
    </main>
  </div>

  <script>
    function togglePassword() {
      const password = document.getElementById("password");
      const eyeIcon = document.getElementById("eyeIcon");

      if (password.type === "password") {
        password.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
      } else {
        password.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
      }
    }
  </script>
</body>
</html>