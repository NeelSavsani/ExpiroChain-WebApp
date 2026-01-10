<?php
session_start();

/* ---------------- SESSION SAFETY CHECK ---------------- */
if (!isset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['reg_data'])) {
    die("Session expired. Please register again.");
}

/* ---------------- HANDLE OTP SUBMISSION ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_otp = $_POST['otp'];

    // Check expiry (5 minutes)
    if (time() - $_SESSION['otp_time'] > 300) {
        session_destroy();
        die("<script>alert('OTP expired. Please register again.');</script>");
    }

    if ($user_otp == $_SESSION['otp']) {
        unset($_SESSION['otp'], $_SESSION['otp_time']);
        header("Location: save_user.php");
        exit();
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP | EXPIROCHAIN</title>
    <link rel="stylesheet" href="CSS/verify_otp.css">
</head>

<body>

<div class="otp-card">
    <h2>Email Verification 🔐</h2>
    <p>Enter the 6-digit OTP sent to your email</p>

    <form method="post" onsubmit="combineOTP()">
        <div class="otp-input">
            <input type="text" maxlength="1" oninput="moveNext(this,1)">
            <input type="text" maxlength="1" oninput="moveNext(this,2)">
            <input type="text" maxlength="1" oninput="moveNext(this,3)">
            <input type="text" maxlength="1" oninput="moveNext(this,4)">
            <input type="text" maxlength="1" oninput="moveNext(this,5)">
            <input type="text" maxlength="1" oninput="moveNext(this,6)">
        </div>

        <input type="hidden" name="otp" id="otp">

        <button class="verify-btn" type="submit">Verify OTP</button>
    </form>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <div class="footer-text">
        OTP is valid for 5 minutes
    </div>
</div>

<script>
function moveNext(el, index) {
    if (el.value.length === 1) {
        let next = el.parentElement.children[index];
        if (next) next.focus();
    }
}

function combineOTP() {
    let inputs = document.querySelectorAll('.otp-input input');
    let otp = '';
    inputs.forEach(input => otp += input.value);
    document.getElementById('otp').value = otp;
}
</script>

</body>
</html>
