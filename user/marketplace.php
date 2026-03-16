<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /exp/login.php");
    exit();
}

$conn = mysqli_connect("localhost","root","","exch_user");

if(!$conn){
    die("Database connection failed");
}

$user_id = $_SESSION['user_id'];

/* BASE QUERY */

$query = "SELECT * FROM marketplace_listings 
          WHERE status='Active' 
          ORDER BY exp_date ASC";

$result = mysqli_query($conn,$query);

/* GET REQUESTED LISTINGS */

$requested = [];

$q = "SELECT listing_id FROM exchange_requests WHERE from_firm_id='$user_id'";
$res = mysqli_query($conn,$q);

while($r = mysqli_fetch_assoc($res)){
    $requested[] = $r['listing_id'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Marketplace | EXPIROCHAIN</title>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<link rel="shortcut icon" href="/exp/images/favicon/android-chrome-192x192.png" />
<link rel="stylesheet" href="/exp/css/home.css">
<link rel="stylesheet" href="/exp/user/css/marketplace.css">

</head>

<body>

<?php include "layout.php"; ?>

<div class="marketplace-container">

<h2 class="market-title">Medicine Marketplace</h2>

<!-- SEARCH BARS -->

<div class="search-container">

<div class="product-search">

<input type="text" id="medicineSearch" class="main-search" placeholder="Search medicine name...">

<button class="search-btn" onclick="clearMedicine()">
Clear
</button>

</div>

<div class="firm-search">

<input type="text" id="firmSearch" class="secondary-search" placeholder="Search city or firm...">

<button class="search-btn small-btn" onclick="clearFirm()">
Clear
</button>

</div>

</div>

<!-- MARKETPLACE TABLE -->

<table id="marketTable" class="data-table">

<thead>

<tr>
<th style="text-align:center;">ID</th>
<th style="text-align:center;">Medicine</th>
<th style="text-align:center;">Batch</th>
<th style="text-align:center;">Qty</th>
<th style="text-align:center;">Expiry</th>
<th style="text-align:center;">Total Rate</th>
<th style="text-align:center;">Firm</th>
<th style="text-align:center;">Listed At</th>
<th style="text-align:center;">Action</th>
</tr>

</thead>

<tbody>

<?php
if(mysqli_num_rows($result) > 0){

$sr = 1;

while($row = mysqli_fetch_assoc($result)){
?>

<tr>

<td><?php echo $sr++; ?></td>

<td><?php echo htmlspecialchars($row['prod_name']); ?></td>

<td><?php echo htmlspecialchars($row['batch_no']); ?></td>

<td><?php echo (int)$row['qty']; ?></td>

<td>
<?php
if($row['exp_date']=="0000-00-00"){
echo "-";
}else{
echo $row['exp_date'];
}
?>
</td>

<td>
₹<?php echo number_format((float)$row['total_rate'],2); ?>
</td>

<td><?php echo htmlspecialchars($row['firm_name']); ?></td>

<td>
<?php echo date("d M Y, h:i A", strtotime($row['listed_at'])); ?>
</td>

<td>

<?php if($row['firm_id'] == $user_id){ ?>

<button class="requested-btn" disabled>
Your Listing
</button>

<?php } elseif(in_array($row['listing_id'],$requested)){ ?>

<button class="requested-btn" disabled>
Requested
</button>

<?php } else { ?>

<form action="send_request.php" method="POST">

<input type="hidden" name="listing_id" value="<?php echo $row['listing_id']; ?>">
<input type="hidden" name="prod_name" value="<?php echo htmlspecialchars($row['prod_name']); ?>">
<input type="hidden" name="batch_no" value="<?php echo htmlspecialchars($row['batch_no']); ?>">
<input type="hidden" name="to_firm_id" value="<?php echo $row['firm_id']; ?>">

<button class="request-btn">Request</button>

</form>

<?php } ?>

</td>

</tr>

<?php
}
}
else{
?>

<tr>
<td colspan="9" class="no-data">
No medicines available
</td>
</tr>

<?php
}
?>

</tbody>

</table>

</div>

<script>

$(document).ready(function(){

var table = $('#marketTable').DataTable({

pageLength:10,
lengthMenu:[10,25,50,100],
ordering:true,
searching:true,
info:true,
paging:true,
dom:'lrtip'   // hides default search bar

});

/* LIVE MEDICINE SEARCH */

$('#medicineSearch').on('keyup', function(){

table.column(1).search(this.value).draw();

});

/* LIVE FIRM SEARCH */

$('#firmSearch').on('keyup', function(){

table.column(6).search(this.value).draw();

});

});

/* CLEAR BUTTONS */

function clearMedicine(){

document.getElementById("medicineSearch").value="";

$('#marketTable').DataTable().column(1).search("").draw();

}

function clearFirm(){

document.getElementById("firmSearch").value="";

$('#marketTable').DataTable().column(6).search("").draw();

}

</script>

</body>
</html>