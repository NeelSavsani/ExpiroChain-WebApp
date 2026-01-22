<?php
session_start();
include 'dbconnect.php';

/* ---------------- SAFETY CHECK ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access");
}

/* ---------------- GET INPUT ---------------- */
$username = trim($_POST['username']);   // ✅ FIXED
$password = trim($_POST['user_pass']);

if (empty($username) || empty($password)) {
    die("<script>alert('All fields are required'); window.history.back();</script>");
}

/* ---------------- IDENTIFY TYPE ---------------- */
if (ctype_digit($username)) {
    // Numeric → phone number
    $where = "phn_no = '$username'";
} else {
    // Email
    $where = "email_id = '$username'";
}

/* ---------------- FETCH USER ---------------- */
$query = "
    SELECT user_id, firm_name, user_pass
    FROM $user_table
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

/* ---------------- LOGIN SUCCESS ---------------- */
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['firm_name'] = $user['firm_name'];

/* ---------------- REDIRECT ---------------- */
header("Location: home.php");
exit();
