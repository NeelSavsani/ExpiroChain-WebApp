<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "login_required";
    exit();
}

/* CONNECT CENTRAL DATABASE */

$conn = mysqli_connect("localhost","root","","exch_user");

if(!$conn){
    echo "db_error";
    exit();
}

$user_id = $_SESSION['user_id'];

/* RECEIVE POPUP DATA */

$stock_id   = $_POST['stock_id'];
$prod_name  = $_POST['prod_name'];
$batch_no   = $_POST['batch_no'];
$qty        = $_POST['qty'];
$exp_date   = $_POST['exp_date'];
$total_rate = $_POST['total_rate'];   // ✅ NEW FIELD

/* CHECK IF ALREADY LISTED */

$check = "
SELECT listing_id
FROM marketplace_listings
WHERE stock_id='$stock_id'
AND firm_id='$user_id'
AND status='Active'
";

$res = mysqli_query($conn,$check);

if(mysqli_num_rows($res) > 0){

    echo "already_listed";
    exit();

}

/* GET FIRM NAME */

$q = "SELECT firm_name FROM user_table WHERE user_id='$user_id'";
$r = mysqli_query($conn,$q);
$f = mysqli_fetch_assoc($r);

$firm_name = $f['firm_name'];

/* INSERT LISTING */

$query = "
INSERT INTO marketplace_listings
(stock_id, prod_name, batch_no, qty, exp_date, total_rate, firm_id, firm_name)
VALUES
('$stock_id','$prod_name','$batch_no','$qty','$exp_date','$total_rate','$user_id','$firm_name')
";

if(mysqli_query($conn,$query)){

    echo "success";

}else{

    echo "error";

}

exit();
?>