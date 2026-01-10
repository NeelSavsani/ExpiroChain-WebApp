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

    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .otp-card {
            background: #ffffff;
            width: 380px;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            text-align: center;
        }

        .otp-card h2 {
            margin: 0;
            color: #0f172a;
        }

        .otp-card p {
            color: #475569;
            font-size: 14px;
            margin-top: 8px;
        }

        .otp-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 25px 0;
        }

        .otp-input input {
            width: 45px;
            height: 50px;
            font-size: 22px;
            text-align: center;
            border: 2px solid #cbd5f5;
            border-radius: 8px;
            outline: none;
            transition: 0.2s;
        }

        .otp-input input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.2);
        }

        .verify-btn {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .verify-btn:hover {
            background: #1d4ed8;
        }

        .error {
            color: #dc2626;
            margin-top: 15px;
            font-size: 14px;
        }

        .footer-text {
            margin-top: 20px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
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
