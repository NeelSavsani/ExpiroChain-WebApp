<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "login_required";
    exit();
}

/* DB CONNECT */

$conn = mysqli_connect("localhost","root","","exch_user");

if(!$conn){
    echo "db_error";
    exit();
}

$from_firm_id = $_SESSION['user_id'];

/* GET DATA FROM POPUP */

$listing_id = $_POST['listing_id'];
$prod_name  = $_POST['prod_name'];
$batch_no   = $_POST['batch_no'];
$to_firm_id = $_POST['to_firm_id'];

$qty_requested   = $_POST['qty'];          // ✅ FIXED
$requested_rate  = $_POST['total_rate'];   // ✅ NEW FIELD

/* VALIDATION */

if($from_firm_id == $to_firm_id){
    echo "self_request_not_allowed";
    exit();
}

/* CHECK DUPLICATE */

$check = mysqli_query($conn,"
SELECT listing_id
FROM exchange_requests
WHERE listing_id='$listing_id'
AND from_firm_id='$from_firm_id'
AND status='Pending'
");

if(mysqli_num_rows($check) > 0){
    echo "already_requested";
    exit();
}

/* OPTIONAL: CHECK STOCK LIMIT */

$stockCheck = mysqli_query($conn,"
SELECT qty FROM marketplace_listings
WHERE listing_id='$listing_id'
");

$row = mysqli_fetch_assoc($stockCheck);
$available_qty = $row['qty'];

if($qty_requested > $available_qty){
    echo "exceeds_stock";
    exit();
}

/* INSERT REQUEST */

$query = "
INSERT INTO exchange_requests
(listing_id, prod_name, batch_no, qty_requested, requested_rate, from_firm_id, to_firm_id, status)
VALUES
('$listing_id','$prod_name','$batch_no','$qty_requested','$requested_rate','$from_firm_id','$to_firm_id','Pending')
";

if(mysqli_query($conn,$query)){
    echo "success";
}else{
    echo "error";
}

exit();
?>