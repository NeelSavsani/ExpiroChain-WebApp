<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /exp/login.php");
    exit();
}

$conn = mysqli_connect("localhost","root","","exch_user");

$request_id = $_GET['id'];

mysqli_query($conn,"
UPDATE exchange_requests
SET status='Rejected'
WHERE request_id='$request_id'
");

header("Location: received_requests.php");
exit();
?>
