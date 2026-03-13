<?php
session_start();
include 'dbconnect.php';

/* ---------------- SAFETY CHECK ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access");
}

/* ---------------- GET INPUT ---------------- */
$username = trim($_POST['username']);
$password = trim($_POST['user_pass']);

if (empty($username) || empty($password)) {
    die("<script>alert('All fields are required'); window.history.back();</script>");
}

/* ---------------- ADMIN LOGIN CHECK ---------------- */
if($username == "admin" && $password == "exp123"){
    
    $_SESSION['admin_logged_in'] = true;

    header("Location: admin/admin.php");
    exit();
}

/* ---------------- IDENTIFY TYPE ---------------- */
if (ctype_digit($username)) {
    // Numeric → phone number
    $where = "u.phn_no = '$username'";
} else {
    // Email
    $where = "u.email_id = '$username'";
}

/* ---------------- FETCH USER ---------------- */
$query = "
    SELECT u.user_id, u.firm_name, u.user_pass, v.isApproved
    FROM $user_table u
    JOIN $verification_table v ON u.user_id = v.user_id
    WHERE $where
    LIMIT 1
";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    die("<script>alert('Invalid credentials'); window.history.back();</script>");
}

$user = mysqli_fetch_assoc($result);

/* ---------------- PASSWORD CHECK ---------------- */
if ($password !== $user['user_pass']) {
    die("<script>alert('Invalid credentials'); window.history.back();</script>");
}

/* ---------------- APPROVAL CHECK ---------------- */
if ($user['isApproved'] != 1) {
    die("<script>alert('Your account is not approved by admin yet'); window.history.back();</script>");
}

/* ---------------- LOGIN SUCCESS ---------------- */
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['firm_name'] = $user['firm_name'];

/* ---------------- REDIRECT ---------------- */
header("Location: home.php");
exit();
?>