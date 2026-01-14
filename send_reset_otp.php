<?php
session_start();

require_once 'config.php';
require_once 'dbconnect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* ---------------- BASIC SAFETY CHECK ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access");
}

/* ---------------- INPUT VALIDATION ---------------- */
if (empty($_POST['user_identity'])) {
    die("<script>alert('Email or mobile number is required'); window.history.back();</script>");
}

$identity = trim($_POST['user_identity']);

/* ---------------- DETECT EMAIL OR MOBILE ---------------- */
$is_email  = filter_var($identity, FILTER_VALIDATE_EMAIL);
$is_mobile = preg_match('/^[6-9]\d{9}$/', $identity); // Indian mobile format

if (!$is_email && !$is_mobile) {
    die("<script>alert('Enter a valid email or mobile number'); window.history.back();</script>");
}

/* ---------------- CHECK USER EXISTS ---------------- */
$identity_safe = mysqli_real_escape_string($conn, $identity);

$query = $is_email
    ? "SELECT user_id, email_id FROM $user_table WHERE email_id='$identity_safe'"
    : "SELECT user_id, phn_no FROM $user_table WHERE phn_no='$identity_safe'";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    die("<script>alert('No account found with this detail'); window.history.back();</script>");
}

$user = mysqli_fetch_assoc($result);

/* ---------------- GENERATE OTP ---------------- */
$otp = random_int(100000, 999999);

$_SESSION['reset_otp']       = $otp;
$_SESSION['reset_identity']  = $identity;
$_SESSION['reset_otp_time']  = time();

/* ---------------- SEND OTP ---------------- */
if ($is_email) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_USER, APP_NAME);
        $mail->addAddress($identity);

        $mail->isHTML(true);
        $mail->Subject = APP_NAME . " - Password Reset OTP";
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>Your OTP for password reset is:</p>
            <h1>$otp</h1>
            <p>This OTP is valid for <b>5 minutes</b>.</p>
            <p>If you didn’t request this, please ignore this email.</p>
            <br>
            <p>Regards,<br><b>Team " . APP_NAME . "</b></p>
        ";

        $mail->send();

    } catch (Exception $e) {
        die("Mail Error: " . $mail->ErrorInfo);
    }

} else {
    /*
     Mobile OTP section
     (Integrate SMS gateway later)
    */
    // For now, just simulate success
}

/* ---------------- REDIRECT TO OTP PAGE ---------------- */
header("Location: verify_reset_otp.php");
exit();
