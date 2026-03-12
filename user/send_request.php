<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /exp/login.php");
    exit();
}

$conn = mysqli_connect("localhost","root","","exch_user");

$from_firm_id = $_SESSION['user_id'];

$listing_id = $_POST['listing_id'];
$prod_name = $_POST['prod_name'];
$batch_no = $_POST['batch_no'];
$to_firm_id = $_POST['to_firm_id'];

$qty_requested = 1;

/* STEP 1: CHECK FOR DUPLICATE REQUEST */

$check = mysqli_query($conn,"
SELECT * 
FROM exchange_requests 
WHERE listing_id='$listing_id'
AND from_firm_id='$from_firm_id'
");

if(mysqli_num_rows($check) > 0){

    /* request already exists */
    header("Location: marketplace.php");
    exit();
}
if($from_firm_id == $to_firm_id){
die("You cannot request your own listing.");
}

/* STEP 2: INSERT NEW REQUEST */

$query = "
INSERT INTO exchange_requests
(listing_id, prod_name, batch_no, qty_requested, from_firm_id, to_firm_id, status)
VALUES
('$listing_id','$prod_name','$batch_no','$qty_requested','$from_firm_id','$to_firm_id','Pending')
";

mysqli_query($conn,$query);

/* STEP 3: RETURN TO MARKETPLACE */

header("Location: marketplace.php");
exit();

?>
