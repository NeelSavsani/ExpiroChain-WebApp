<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /exp/login.php");
    exit();
}

$conn = mysqli_connect("localhost","root","","exch_user");

$request_id = $_GET['id'];

/* GET REQUEST DETAILS */

$q = "SELECT * FROM exchange_requests WHERE request_id='$request_id'";
$r = mysqli_query($conn,$q);
$request = mysqli_fetch_assoc($r);

$listing_id = $request['listing_id'];
$qty_requested = $request['qty_requested'];

/* GET MARKETPLACE LISTING */

$q2 = "SELECT qty FROM marketplace_listings WHERE listing_id='$listing_id'";
$r2 = mysqli_query($conn,$q2);
$listing = mysqli_fetch_assoc($r2);

$current_qty = $listing['qty'];

/* CASE 1: BUY ALL STOCK */

if($qty_requested >= $current_qty){

    mysqli_query($conn,"
    UPDATE marketplace_listings
    SET status='Sold', qty=0
    WHERE listing_id='$listing_id'
    ");

}

/* CASE 2: PARTIAL PURCHASE */

else{

    $new_qty = $current_qty - $qty_requested;

    mysqli_query($conn,"
    UPDATE marketplace_listings
    SET qty='$new_qty'
    WHERE listing_id='$listing_id'
    ");

}

/* APPROVE SELECTED REQUEST */

mysqli_query($conn,"
UPDATE exchange_requests
SET status='Approved'
WHERE request_id='$request_id'
");

/* REJECT OTHER PENDING REQUESTS */

mysqli_query($conn,"
UPDATE exchange_requests
SET status='Rejected'
WHERE listing_id='$listing_id'
AND status='Pending'
AND request_id != '$request_id'
");

/* REDIRECT */

header("Location: received_requests.php");
exit();
?>
