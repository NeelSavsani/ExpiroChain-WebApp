<?php
session_start();
include "../dbconnect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /exp/login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* FETCH RECEIVED REQUESTS */

$query = "
SELECT r.*, u.firm_name, 
       ml.total_rate, 
       ml.qty AS total_qty
FROM exch_user.exchange_requests r
JOIN exch_user.user_table u
ON r.from_firm_id = u.user_id
JOIN exch_user.marketplace_listings ml
ON r.listing_id = ml.listing_id
WHERE r.to_firm_id = '$user_id'
ORDER BY r.request_date DESC
";
$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html>

<head>

<title>Received Requests | EXPIROCHAIN</title>

<link rel="shortcut icon" href="/exp/images/favicon/android-chrome-192x192.png" />
<link rel="stylesheet" href="/exp/user/css/received_requests.css">
<link rel="stylesheet" href="/exp/css/home.css">
<link rel="stylesheet" href="/exp/user/css/products.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

</head>

<body>

<?php include "layout.php"; ?>

<div class="container">

<div class="products-card">

<div class="products-header">

<h2>Received Requests</h2>

<button class="my-request-btn" onclick="openRequestPopup()">

My Requests
</button>

</div>

<?php if(mysqli_num_rows($result) > 0){ ?>

<table id="requestTable" class="data-table">

<thead>
<tr>
<th>ID</th>
<th>Medicine</th>
<th>Batch</th>
<th>Qty</th>
<th>Requested Price</th>
<th>Requested By</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['request_id']; ?></td>

<td><?php echo htmlspecialchars($row['prod_name']); ?></td>

<td><?php echo htmlspecialchars($row['batch_no']); ?></td>

<td><?php echo $row['qty_requested']; ?></td>

<?php
$unit_price = ($row['total_qty'] > 0) ? 
              ($row['total_rate'] / $row['total_qty']) : 0;

$original_for_requested = $unit_price * $row['qty_requested'];
?>

<td>
₹<?php echo number_format($row['requested_rate'],2); ?>

<br>

<small style="color:#6b7280;">
Original: ₹<?php echo number_format($original_for_requested,2); ?>
</small>
</td>

<td><?php echo htmlspecialchars($row['firm_name']); ?></td>

<td>
<?php
if($row['status']=="Pending"){
echo "<span style='color:#f59e0b;font-weight:bold;'>Pending</span>";
}
elseif($row['status']=="Approved"){
echo "<span style='color:#16a34a;font-weight:bold;'>Approved</span>";
}
else{
echo "<span style='color:#dc2626;font-weight:bold;'>Rejected</span>";
}
?>
</td>

<td>

<?php if($row['status']=="Pending"){ ?>

<a class="approve-btn" href="approve_request.php?id=<?php echo $row['request_id']; ?>">

Approve
</a>

<a class="reject-btn" href="reject_request.php?id=<?php echo $row['request_id']; ?>">
Reject
</a>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<?php } else { ?>

<p style="text-align:center;padding:25px;color:#6b7280;font-size:16px;">
No requests received yet.
</p>

<?php } ?>

</div>

</div>


<!-- MY REQUESTS POPUP -->

<div id="requestPopup">


<div class="popup-card">

<h3>My Requests</h3>

<?php

$q2 = "
SELECT r.*, u.firm_name
FROM exch_user.exchange_requests r
JOIN exch_user.user_table u
ON r.to_firm_id = u.user_id
WHERE r.from_firm_id = '$user_id'
ORDER BY r.request_date DESC
";

$res2 = mysqli_query($conn,$q2);

if(mysqli_num_rows($res2) > 0){

?>

<table id="request-popup-table" class="data-table">

<thead>
<tr>
<th>ID</th>
<th>Medicine</th>
<th>Batch</th>
<th>Qty</th>
<th>Requested Price</th>
<th>Firm</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($req = mysqli_fetch_assoc($res2)){ ?>

<tr>

<td><?php echo $req['request_id']; ?></td>
<td><?php echo htmlspecialchars($req['prod_name']); ?></td>
<td><?php echo htmlspecialchars($req['batch_no']); ?></td>
<td><?php echo $req['qty_requested']; ?></td>
<td>₹<?php echo number_format($req['requested_rate'],2); ?></td>
<td><?php echo htmlspecialchars($req['firm_name']); ?></td>

<td>

<?php
if($req['status']=="Pending"){
echo "<span class='status-pending'>Pending</span>";
}
elseif($req['status']=="Approved"){
echo "<span class='status-approved'>Approved</span>";
}
else{
echo "<span class='status-rejected'>Rejected</span>";
}
?>

</td>

<td>

<?php if($req['status']=="Pending"){ ?>

<a class="withdraw-btn"
href="withdraw_request.php?id=<?php echo $req['request_id']; ?>">
Withdraw
</a>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<?php } else { ?>

<p style="text-align:center;padding:25px;color:#6b7280;">
You have not sent any requests yet.
</p>

<?php } ?>

<br>

<button class="popup-close" onclick="closeRequestPopup()">

Close
</button>

</div>

</div>


<script>

$(document).ready(function(){

$('#requestTable').DataTable({
pageLength:10,
order:[[0,'desc']]
});

});

function openRequestPopup(){
document.getElementById("requestPopup").style.display="flex";
}

function closeRequestPopup(){
document.getElementById("requestPopup").style.display="none";
}

</script>

</body>
</html>
