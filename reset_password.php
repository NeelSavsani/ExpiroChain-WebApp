<?php
session_start();

require_once 'dbconnect.php';

/* ---------------- SESSION SAFETY CHECK ---------------- */
if (
    !isset($_SESSION['reset_verified'], $_SESSION['reset_identity'])
    || $_SESSION['reset_verified'] !== true
) {
    die("Unauthorized access");
}

/* ---------------- HANDLE FORM SUBMIT ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
        die("<script>alert('All fields are required'); window.history.back();</script>");
    }

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        die("<script>alert('Passwords do not match'); window.history.back();</script>");
    }

    // ⚠️ STORE PASSWORD AS PLAIN TEXT
    $plain_password = mysqli_real_escape_string($conn, $_POST['new_password']);

    // Identify user (email or phone)
    $identity = mysqli_real_escape_string($conn, $_SESSION['reset_identity']);

    if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
        $query = "
            UPDATE $user_table 
            SET user_pass = '$plain_password'
            WHERE LOWER(TRIM(email_id)) = LOWER(TRIM('$identity'))
            LIMIT 1
        ";
    } else {
        $query = "
            UPDATE $user_table 
            SET user_pass = '$plain_password'
            WHERE phn_no LIKE '%$identity'
            LIMIT 1
        ";
    }

    if (!mysqli_query($conn, $query)) {
        die("Password update failed");
    }

    // Cleanup session
    unset(
        $_SESSION['reset_verified'],
        $_SESSION['reset_identity'],
        $_SESSION['reset_otp'],
        $_SESSION['reset_otp_time']
    );

    echo "<script>
        alert('Password reset successful. Please login.');
        window.location.href = 'login.php';
    </script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | EXPIROCHAIN</title>
    <link rel="stylesheet" href="CSS/register.css">
</head>
<body>

<header class="app-header">
    <img src="images/logo.png" alt="EXPIROCHAIN Logo">
</header>

<div class="page-wrapper">
    <div class="register-card">

        <div class="page-title">
            <h2>Reset Password</h2>
            <p>Create a new password for your account</p>
        </div>

        <form method="post">

            <div class="field">
                <label>New Password</label>
                <div class="password-wrapper">
                    <input type="password" name="new_password" required>
                    <span class="toggle-eye">👁</span>
                </div>
            </div>

            <div class="field">
                <label>Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" required>
                    <span class="toggle-eye">👁</span>
                </div>
            </div>

            <button type="submit" class="register-btn">
                Reset Password
            </button>

        </form>
    </div>
</div>

<script>
document.querySelectorAll(".toggle-eye").forEach(eye => {
    eye.addEventListener("click", () => {
        const input = eye.previousElementSibling;
        input.type = input.type === "password" ? "text" : "password";
        eye.textContent = input.type === "password" ? "👁" : "🙈";
    });
});
</script>

</body>
</html>
