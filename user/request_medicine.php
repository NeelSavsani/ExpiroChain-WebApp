<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    exit();
}

$conn = mysqli_connect("localhost","root","","exch_user");

$user_id = $_SESSION['user_id'];

$listing_id = $_POST['listing_id'];

/* get listing info */

$q = "SELECT firm_id, qty FROM marketplace_listings WHERE listing_id='$listing_id'";
$r = mysqli_query($conn,$q);

$data = mysqli_fetch_assoc($r);

$to_firm = $data['firm_id'];

/* insert request */

$query = "INSERT INTO exchange_requests
(listing_id, qty_requested, from_firm_id, to_firm_id, status)
VALUES
('$listing_id','1','$user_id','$to_firm','Pending')";

mysqli_query($conn,$query);

/* redirect back */

header("Location: marketplace.php");
exit();
?>