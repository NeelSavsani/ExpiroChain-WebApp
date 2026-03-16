<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /exp/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* CONNECT MAIN DATABASE */

$main_conn = mysqli_connect("localhost","root","","exch_user");

if(!$main_conn){
    die("Connection failed: " . mysqli_connect_error());
}

/* GET LISTED ITEMS */

$listed_items = [];

$list_query = "SELECT stock_id FROM marketplace_listings 
WHERE firm_id='$user_id' AND status='Active'";

$list_res = mysqli_query($main_conn,$list_query);

while($l = mysqli_fetch_assoc($list_res)){
    $listed_items[] = $l['stock_id'];
}

/* GET USER DATABASE */

$q = "SELECT dbname FROM user_verification WHERE user_id='$user_id'";
$res = mysqli_query($main_conn,$q);

$row = mysqli_fetch_assoc($res);

$dbname = $row['dbname'];

/* CONNECT FIRM DATABASE */

$conn = mysqli_connect("localhost","root","",$dbname);

if(!$conn){
    die("Firm DB connection failed: " . mysqli_connect_error());
}

$soon = [];
$expired = [];

/* FETCH STOCK WITH TIME */

$query = "
SELECT
stock_id,
prod_name,
batch_no,
qty,
exp_date,
TIMESTAMPDIFF(SECOND, NOW(), CONCAT(exp_date,' 23:59:59')) AS seconds_left
FROM stock_table
WHERE exp_date IS NOT NULL
AND exp_date != '0000-00-00'
ORDER BY exp_date ASC
";

$result = mysqli_query($conn,$query);

if(!$result){
    die(mysqli_error($conn));
}

while($row = mysqli_fetch_assoc($result)){

    $seconds_left = intval($row['seconds_left']);

    /* EXPIRED */

    if($seconds_left <= 0){

        $expired[] = $row;

    }

    else{

        $days_left = floor($seconds_left / 86400);

        $hours = floor(($seconds_left % 86400) / 3600);
        $minutes = floor(($seconds_left % 3600) / 60);
        $seconds = $seconds_left % 60;

        $time_left = sprintf("%02d:%02d:%02d",$hours,$minutes,$seconds);

        if($days_left <= 30){

            $row['days_left'] = $days_left;
            $row['time_left'] = $time_left;

            $soon[] = $row;

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Expiry Tracker | EXPIROCHAIN</title>

<link rel="shortcut icon" href="/exp/images/favicon/android-chrome-192x192.png" />
<link rel="stylesheet" href="/exp/css/home.css" />
<link rel="stylesheet" href="/exp/user/css/expiry_tracker.css" />

</head>

<body>

<?php include "layout.php"; ?>

<br><br><br>

<!-- EXPIRING SOON -->

<div class="register-card">

<h3 class="section-heading">Expiring Within 30 Days</h3>

<table class="admin-table">

<?php if(!empty($soon)){ ?>

<thead>

<tr>
<th>Name</th>
<th>Batch</th>
<th>Qty</th>
<th>Expiry</th>
<th>Days Left</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php foreach($soon as $row){

$days = $row['days_left'];

$row_class = ($days <= 7) ? "critical-expiry" : "warning-expiry";

?>

<tr class="<?php echo $row_class; ?>">

<td><?php echo $row['prod_name']; ?></td>

<td><?php echo $row['batch_no']; ?></td>

<td><?php echo $row['qty']; ?></td>

<td><?php echo $row['exp_date']; ?></td>

<td>

<?php

if($days <= 1){

echo "<span class='risk-badge critical countdown' data-seconds='".$row['seconds_left']."'>".$row['time_left']."</span>";

}
elseif($days <= 7){

echo "<span class='risk-badge critical'>".$days." days</span>";

}
else{

echo "<span class='risk-badge warning'>".$days." days</span>";

}

?>

</td>

<td>

<?php if(in_array($row['stock_id'],$listed_items)){ ?>

<button class="listed-btn" disabled>
Listed
</button>

<?php } else { ?>

<button
class="sale-btn"
data-stock="<?php echo $row['stock_id']; ?>"
data-name="<?php echo htmlspecialchars($row['prod_name']); ?>"
data-batch="<?php echo $row['batch_no']; ?>"
data-qty="<?php echo $row['qty']; ?>"
data-exp="<?php echo $row['exp_date']; ?>"
>
List For Sale
</button>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

<?php } else { ?>

<tbody>

<tr>

<td colspan="6" style="text-align:center;padding:20px;">
No medicines expiring within 30 days
</td>

</tr>

</tbody>

<?php } ?>

</table>

</div>

<!-- EXPIRED -->

<div class="register-card">

<h3 class="section-heading">Expired Medicines</h3>

<table class="admin-table">

<?php if(!empty($expired)){ ?>

<thead>

<tr>
<th>Name</th>
<th>Batch</th>
<th>Qty</th>
<th>Expired On</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php foreach($expired as $row){ ?>

<tr>

<td><?php echo $row['prod_name']; ?></td>
<td><?php echo $row['batch_no']; ?></td>
<td><?php echo $row['qty']; ?></td>
<td><?php echo $row['exp_date']; ?></td>

<td>
<button>Dispose</button>
</td>

</tr>

<?php } ?>

</tbody>

<?php } else { ?>

<tbody>

<tr>

<td colspan="5" style="text-align:center;padding:20px;">
No expired medicines
</td>

</tr>

</tbody>

<?php } ?>

</table>

</div>

<!-- SALE POPUP -->

<div id="salePopup" class="popup">

<div class="popup-content">

<h3>List Medicine For Sale</h3>

<form id="saleForm">

<input type="hidden" id="stock_id" name="stock_id">

<label>Product</label>
<input type="text" id="prod_name" name="prod_name" readonly>

<label>Batch</label>
<input type="text" id="batch_no" name="batch_no" readonly>

<label>Quantity to List</label>
<input type="number" id="qty" name="qty" required>

<label>Total Rate</label>
<input type="number" id="total_rate" name="total_rate" required>

<label>Expiry</label>
<input type="text" id="exp_date" name="exp_date" readonly>

<br><br>

<div class="popup-buttons">
<button type="submit">Confirm Listing</button>
<button type="button" onclick="closePopup()">Cancel</button>
</div>

</form>

</div>

</div>

<script>

let currentButton = null;

/* OPEN POPUP */

document.querySelectorAll(".sale-btn").forEach(btn => {

btn.addEventListener("click", function(){

currentButton = this;

document.getElementById("salePopup").style.display="flex";

document.getElementById("stock_id").value = this.dataset.stock;
document.getElementById("prod_name").value = this.dataset.name;
document.getElementById("batch_no").value = this.dataset.batch;
document.getElementById("qty").value = this.dataset.qty;
document.getElementById("exp_date").value = this.dataset.exp;

});

});

/* CLOSE POPUP */

function closePopup(){
document.getElementById("salePopup").style.display="none";
}

/* SUBMIT */

document.getElementById("saleForm").addEventListener("submit", function(e){

e.preventDefault();

let formData = new FormData(this);

fetch("list_for_sale.php",{
method:"POST",
body:formData
})
.then(res => res.text())
.then(data => {

if(data.includes("success")){

closePopup();

if(currentButton){
currentButton.innerText="Listed";
currentButton.disabled=true;
currentButton.classList.remove("sale-btn");
currentButton.classList.add("listed-btn");
}

}

else if(data.trim() === "already_listed"){
alert("Already listed in marketplace");
}

else{
alert("Error listing medicine");
}

});

});

</script>

<script>

function formatTime(seconds){

    let hrs = Math.floor(seconds / 3600);
    let mins = Math.floor((seconds % 3600) / 60);
    let secs = seconds % 60;

    return String(hrs).padStart(2,'0') + ":" +
           String(mins).padStart(2,'0') + ":" +
           String(secs).padStart(2,'0');
}

function startCountdown(){

    const timers = document.querySelectorAll(".countdown");

    timers.forEach(timer => {

        let seconds = parseInt(timer.dataset.seconds);

        setInterval(() => {

            if(seconds <= 0){
                timer.innerText = "Expired";
                return;
            }

            seconds--;

            timer.innerText = formatTime(seconds);

        },1000);

    });

}

startCountdown();

</script>

</body>
</html>