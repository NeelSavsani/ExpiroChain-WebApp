<?php

session_start();

$conn = mysqli_connect("localhost","root","","exch_user");

$request_id = $_GET['id'];

$user_id = $_SESSION['user_id'];

mysqli_query($conn,"
DELETE FROM exchange_requests
WHERE request_id='$request_id'
AND from_firm_id='$user_id'
AND status='Pending'
");

header("Location: received_requests.php");
exit();

?>
