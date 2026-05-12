<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Committee Sign Up | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/login.css">
  <style>
    .msg { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
    .error { color: #d9534f; background: #f2dede; border: 1px solid #d9534f; }
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
          <h2>Committee Registration</h2>
          <p>Enter your ID to verify your details</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
          <div id="error-msg" class="msg error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form class="login-form" action="register_committee.php" method="post">
          <label class="form-group">
            <span>Committee ID</span>
            <input type="text" name="userId" id="userId" placeholder="Enter your ID (e.g. 1)" required>
          </label>

          <label class="form-group">
            <span>Committee Name</span>
            <input type="text" name="userName" id="userName" placeholder="Auto-filled" readonly required>
          </label>

          <label class="form-group">
            <span>Committee Email</span>
            <input type="email" name="userEmail" id="userEmail" placeholder="Auto-filled" readonly required>
          </label>

          <label class="form-group">
            <span>Create Password</span>
            <input type="password" name="password" placeholder="Password" required>
          </label>

          <label class="form-group">
            <span>Confirm Password</span>
            <input type="password" name="confirmPassword" placeholder="Confirm Password" required>
          </label>

          <button type="submit" name="signup_btn" class="primary-button">Confirm Registration</button>
          <a href="login.php" class="text-link">← Back</a>
        </form>
      </div>
    </main>
  </div>

<script>
// AJAX to fetch Name and Email based on ID
document.getElementById('userId').addEventListener('blur', function() {
    var userId = this.value;
    if (userId !== "") {
        fetch('fetch_user_data.php?id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('userName').value = data.userName;
                    document.getElementById('userEmail').value = data.userEmail;
                } else {
                    alert("User ID not found in database.");
                    document.getElementById('userName').value = "";
                    document.getElementById('userEmail').value = "";
                }
            });
    }
});
</script>
</body>
</html>