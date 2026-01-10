<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Login | EXPIROCHAIN</title>
    <link
      rel="shortcut icon"
      href="images/favicon/android-chrome-192x192.png"
    />
    <link rel="stylesheet" href="CSS/register.css" />
  </head>

  <body>
    <!-- HEADER -->
    <header class="app-header">
      <div class="header-left">
        <img src="Images/Logo.png" alt="EXPIROCHAIN Logo" />
      </div>
      <div class="header-right">
        <a href="register.php" class="login-btn">Register</a>
      </div>
    </header>

    <!-- PAGE TITLE -->
    <div class="page-title">
      <h2>Welcome Back</h2>
      <p>Login to manage medicine expiry and compliance</p>
    </div>

    <!-- PAGE WRAPPER -->
    <div class="page-wrapper">
      <div class="register-card">
        <!-- FORM -->
        <form method="post" action="login_process.php">
          <!-- EMAIL -->
          <div class="field">
            <label>Username</label>
            <input
              type="text"
              name="username"
              placeholder="Enter email or phone number"
              required
            />
          </div>

          <!-- PASSWORD -->
          <div class="field">
            <label>Password</label>

            <div class="password-wrapper">
              <input type="password" name="user_pass" id="password" placeholder="Enter password" required />
              <span class="toggle-eye">👁</span>
            </div>
          </div>

          <!-- SUBMIT -->
          <button type="submit" class="register-btn">Login</button>

          <!-- EXTRA -->
          <div class="login-text">
            <p>
              Don't have an account?
              <a href="register.php">Register here</a>
            </p>
          </div>
        </form>
      </div>
    </div>

    <!-- JS (Reuse existing) -->
    <script src="JS/login.js"></script>
  </body>
</html>
