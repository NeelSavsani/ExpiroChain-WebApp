<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /exp/login.php");
    exit();
}

$conn = mysqli_connect("localhost","root","","exch_user");

$medicine = "";
$firm = "";

if(isset($_GET['medicine'])){
    $medicine = mysqli_real_escape_string($conn,$_GET['medicine']);
}

if(isset($_GET['firm'])){
    $firm = mysqli_real_escape_string($conn,$_GET['firm']);
}

$query = "SELECT * FROM marketplace_listings WHERE status='Active'";

if($medicine != ""){
    $query .= " AND prod_name LIKE '%$medicine%'";
}

if($firm != ""){
    $query .= " AND firm_name LIKE '%$firm%'";
}

$query .= " ORDER BY exp_date ASC";

$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Marketplace | EXPIROCHAIN</title>

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

<!-- PRODUCT SEARCH -->

<form method="GET" class="product-search">

<input
type="text"
name="medicine"
class="main-search"
placeholder="Search medicine name..."
value="<?php echo htmlspecialchars($medicine); ?>"
>

<button class="search-btn">Search Medicine</button>

</form>


<!-- FIRM / CITY SEARCH -->

<form method="GET" class="firm-search">

<input
type="text"
name="firm"
class="secondary-search"
placeholder="Search city or firm..."
value="<?php echo htmlspecialchars($firm); ?>"
>

<button class="search-btn small-btn">Search</button>

</form>

</div>

<!-- MARKETPLACE TABLE -->

<table id="marketTable" class="data-table">

<thead>

<tr>
<th>ID  </th>
<th>Medicine</th>
<th>Batch</th>
<th>Qty</th>
<th>Expiry</th>
<th>Firm</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php
if(mysqli_num_rows($result) > 0){

$sr = 1;
$user_id = $_SESSION['user_id'];

$requested = [];

$q = "SELECT listing_id 
FROM exchange_requests 
WHERE from_firm_id = '$user_id'";

$res = mysqli_query($conn,$q);

while($r = mysqli_fetch_assoc($res)){
    $requested[] = $r['listing_id'];
}

while($row = mysqli_fetch_assoc($result)){
?>

<tr>

<td><?php echo $sr++; ?></td>

<td><?php echo $row['prod_name']; ?></td>

<td><?php echo $row['batch_no']; ?></td>

<td><?php echo $row['qty']; ?></td>

<td><?php echo $row['exp_date']; ?></td>

<td><?php echo $row['firm_name']; ?></td>
<td>

<?php if($row['firm_id'] == $_SESSION['user_id']){ ?>

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
<input type="hidden" name="prod_name" value="<?php echo $row['prod_name']; ?>">
<input type="hidden" name="batch_no" value="<?php echo $row['batch_no']; ?>">
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
<td colspan="6" class="no-data">
No medicines available
</td>
</tr>

<?php
}
?>

</tbody>

</table>

</div>

</body>
</html>