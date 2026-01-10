<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'config.php';


/* ---------------- BASIC SAFETY CHECK ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access");
}

/* ---------------- BASIC VALIDATION ---------------- */
if (empty($_POST['email_id']) || empty($_POST['firm_name'])) {
    die("<script>alert('Required fields missing'); window.history.back();</script>");
}

/* ---------------- PASSWORD CHECK ---------------- */
if (
    empty($_POST['user_pass']) ||
    $_POST['user_pass'] !== $_POST['re_password']
) {
    die("<script>alert('Password mismatch'); window.history.back();</script>");
}

/* ---------------- STORE FORM DATA IN SESSION ---------------- */
$_SESSION['reg_data'] = [
    'firm_name'  => $_POST['firm_name'],
    'owner_name' => $_POST['owner_name'],
    'user_type'  => $_POST['user_type'],
    'email_id'   => $_POST['email_id'],
    'phn_no'     => $_POST['phn_no'],
    'gstno'      => $_POST['gstno'],
    'dl1'        => $_POST['dl1'],
    'dl2'        => $_POST['dl2'],
    'address'    => $_POST['address'],
    'user_pass'  => $_POST['user_pass']   //  plain password
];


/* ---------------- TEMP FILE STORAGE ---------------- */
$temp_dir = "temp_uploads/" . session_id() . "/";
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

function moveTempFile($file, $name, $temp_dir) {
    if (!isset($file) || $file['error'] !== 0) return false;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = $name . "." . $ext;
    move_uploaded_file($file['tmp_name'], $temp_dir . $new_name);
    return $new_name;
}

$_SESSION['reg_files'] = [
    'gst_file' => moveTempFile($_FILES['gst_file'], "gst", $temp_dir),
    'dl1_file' => moveTempFile($_FILES['dl1_file'], "dl1", $temp_dir),
    'dl2_file' => moveTempFile($_FILES['dl2_file'], "dl2", $temp_dir)
];

/* ---------------- FILE VALIDATION ---------------- */
if (
    !$_SESSION['reg_files']['gst_file'] ||
    !$_SESSION['reg_files']['dl1_file'] ||
    !$_SESSION['reg_files']['dl2_file']
) {
    array_map('unlink', glob($temp_dir . "*"));
    rmdir($temp_dir);
    session_destroy();
    die("<script>alert('All documents are mandatory'); window.history.back();</script>");
}

/* ---------------- GENERATE OTP ---------------- */
$_SESSION['otp'] = random_int(100000, 999999);
$_SESSION['otp_time'] = time();

/* ---------------- SEND OTP MAIL ---------------- */
$email  = $_SESSION['reg_data']['email_id'];

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();                 // ✅ REQUIRED
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;          // ✅ REQUIRED
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->Port = SMTP_PORT;
    $mail->SMTPSecure = SMTP_SECURE;

    $mail->setFrom(SMTP_USER, 'EXPIROCHAIN');  // ✅ from config
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'EXPIROCHAIN Email Verification';
    $mail->Body = "
        <h2>Email Verification</h2>
        <p>Your OTP is:</p>
        <h1>{$_SESSION['otp']}</h1>
        <p>This OTP is valid for <b>5 minutes</b>.</p>
        <p>Do not share this OTP.</p>
    ";

    $mail->send();
    header("Location: verify_otp.php");
    exit();

} catch (Exception $e) {
    array_map('unlink', glob($temp_dir . "*"));
    rmdir($temp_dir);
    session_destroy();
    die("Mail Error: " . $mail->ErrorInfo);
}
