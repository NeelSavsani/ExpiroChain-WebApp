<?php

header("Content-Type: application/json");
include "../dbconnect.php";

/* CHECK USER */

if(!isset($_GET['user_id'])){
    echo json_encode([
        "status"=>"error",
        "message"=>"user_id required"
    ]);
    exit();
}

$user_id = intval($_GET['user_id']);

/* CONNECT MAIN DATABASE */

$main_conn = mysqli_connect("localhost","root","","exch_user");

if(!$main_conn){
    echo json_encode([
        "status"=>"error",
        "message"=>"Main DB connection failed"
    ]);
    exit();
}

/* GET USER DATABASE */

$q = "SELECT dbname FROM user_verification WHERE user_id='$user_id'";
$res = mysqli_query($main_conn,$q);

if(mysqli_num_rows($res) == 0){
    echo json_encode([
        "status"=>"error",
        "message"=>"User database not found"
    ]);
    exit();
}

$row = mysqli_fetch_assoc($res);
$dbname = $row['dbname'];

/* CONNECT FIRM DATABASE */

$conn = mysqli_connect("localhost","root","",$dbname);

if(!$conn){
    echo json_encode([
        "status"=>"error",
        "message"=>"Firm DB connection failed"
    ]);
    exit();
}

/* GET LISTED ITEMS */

$listed_items = [];

$list_query = "
SELECT stock_id 
FROM marketplace_listings 
WHERE firm_id='$user_id' 
AND status='Active'
";

$list_res = mysqli_query($main_conn,$list_query);

while($l = mysqli_fetch_assoc($list_res)){
    $listed_items[] = $l['stock_id'];
}

/* FETCH STOCK */

$query = "
SELECT stock_id, prod_name, batch_no, qty, exp_date,
DATEDIFF(exp_date, CURDATE()) AS days_left
FROM stock_table
WHERE exp_date IS NOT NULL
AND exp_date != '0000-00-00'
ORDER BY exp_date ASC
";

$result = mysqli_query($conn,$query);

$soon = [];
$expired = [];

$today = new DateTime();

while($row = mysqli_fetch_assoc($result)){

    $exp = new DateTime($row['exp_date']);
    $days_left = $today->diff($exp)->days;

    $row['listed'] = in_array($row['stock_id'],$listed_items);

    if($exp < $today){

        $expired[] = [
            "stock_id"=>$row['stock_id'],
            "prod_name"=>$row['prod_name'],
            "batch_no"=>$row['batch_no'],
            "qty"=>$row['qty'],
            "exp_date"=>$row['exp_date']
        ];

    }
    elseif($days_left <= 30){

        $soon[] = [
            "stock_id"=>$row['stock_id'],
            "prod_name"=>$row['prod_name'],
            "batch_no"=>$row['batch_no'],
            "qty"=>$row['qty'],
            "exp_date"=>$row['exp_date'],
            "days_left"=>$days_left,
            "listed"=>$row['listed']
        ];

    }
}

/* RETURN JSON */

echo json_encode([
    "status"=>"success",
    "expiring_soon"=>$soon,
    "expired"=>$expired
]);

?>