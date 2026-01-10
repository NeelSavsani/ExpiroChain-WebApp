<?php
session_start();
include 'dbconnect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'config.php';


/* ---------------- SESSION SAFETY CHECK ---------------- */
if (!isset($_SESSION['reg_data'], $_SESSION['reg_files'])) {
    die("Unauthorized access");
}

$data  = $_SESSION['reg_data'];
$files = $_SESSION['reg_files'];

/* ---------------- INSERT USER ---------------- */
$insert_user = "
INSERT INTO $user_table
(firm_name, owner_name, user_type, email_id, phn_no, gstno, dl1, dl2, address, user_pass, registered_at)
VALUES
(
    '{$data['firm_name']}',
    '{$data['owner_name']}',
    '{$data['user_type']}',
    '{$data['email_id']}',
    '{$data['phn_no']}',
    '{$data['gstno']}',
    '{$data['dl1']}',
    '{$data['dl2']}',
    '{$data['address']}',
    '{$data['user_pass']}',
    CURRENT_TIMESTAMP()
)";
if (!mysqli_query($conn, $insert_user)) {
    die("User registration failed");
}

$user_id = mysqli_insert_id($conn);

/* ---------------- CREATE FOLDER ---------------- */
$firm_slug = strtolower(trim($data['firm_name']));
$firm_slug = preg_replace('/\s+/', '_', $firm_slug);
$firm_slug .= "_" . $user_id;

$final_dir = "uploads/" . $firm_slug . "/";
if (!is_dir($final_dir)) {
    mkdir($final_dir, 0777, true);
}


/* ---------------- MOVE FILES ---------------- */
$temp_dir = "temp_uploads/" . session_id() . "/";
rename($temp_dir.$files['gst_file'], $final_dir.$files['gst_file']);
rename($temp_dir.$files['dl1_file'], $final_dir.$files['dl1_file']);
rename($temp_dir.$files['dl2_file'], $final_dir.$files['dl2_file']);
@rmdir($temp_dir);

/* ---------------- VERIFICATION TABLE ---------------- */
$gst_path = $final_dir.$files['gst_file'];
$dl1_path = $final_dir.$files['dl1_file'];
$dl2_path = $final_dir.$files['dl2_file'];

mysqli_query($conn, "
INSERT INTO $verification_table
(user_id, firm_name, gst_proof_path, dl1_proof_path, dl2_proof_path, dbname, registered_at)
VALUES
('$user_id','{$data['firm_name']}','$gst_path','$dl1_path','$dl2_path','$firm_slug',CURRENT_TIMESTAMP())
");

/* ---------------- SEND REGISTRATION SUCCESS EMAIL ---------------- */
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


    $mail->setFrom('neelsavsani7@gmail.com', 'EXPIROCHAIN');
    $mail->addAddress($data['email_id']);

    $mail->isHTML(true);
    $mail->Subject = 'Welcome to EXPIROCHAIN';
    $mail->Body = "
        <h2>Registration Successful 🎉</h2>
        <p>Dear <b>{$data['owner_name']}</b>,</p>
        <p>Your organization <b>{$data['firm_name']}</b> has been successfully registered on the <b>EXPIROCHAIN</b> platform.</p>
        <p>You can now log in and start managing medicine expiry efficiently.</p>
        <br>
        <p>Regards,<br><b>Team EXPIROCHAIN</b></p>
    ";

    $mail->send();

} catch (Exception $e) {
    // Even if mail fails, registration is complete
}

/* ---------------- PRESERVE DASHBOARD SESSION ---------------- */
$_SESSION['firm_name'] = $data['firm_name'];
$_SESSION['user_id']   = $user_id;

unset($_SESSION['reg_data'], $_SESSION['reg_files']);

/* ---------------- REDIRECT TO HOME PAGE ---------------- */
echo "<script>
    alert('Registration successful! Welcome to EXPIROCHAIN');
    window.location.href = 'index.php';
</script>";

