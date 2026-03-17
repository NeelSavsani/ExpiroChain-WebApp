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
<link rel="stylesheet" href="/exp/user/css/expiry_tracker.css">     <!--for popup-->

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

<button
class="request-btn open-request-popup"
data-id="<?php echo $row['listing_id']; ?>"
data-firm-id="<?php echo $row['firm_id']; ?>"
data-firm-name="<?php echo htmlspecialchars($row['firm_name']); ?>"
data-name="<?php echo htmlspecialchars($row['prod_name']); ?>"
data-batch="<?php echo $row['batch_no']; ?>"
data-exp="<?php echo $row['exp_date']; ?>"
data-maxqty="<?php echo $row['qty']; ?>"
data-rate="<?php echo $row['total_rate']; ?>"
data-unit-price="<?php echo $row['total_rate'] / $row['qty']; ?>"
>
Request
</button>

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

<!-- REQUEST POPUP -->

<div id="requestPopup" class="popup">

<div class="popup-content">

<h3>Request Medicine</h3>

<form id="requestForm">

<input type="hidden" id="listing_id" name="listing_id">

<label>Firm Name</label>
<input type="text" id="firm_name" readonly>

<label>Product Name</label>
<input type="text" id="req_prod_name" readonly>

<label>Batch Number</label>
<input type="text" id="req_batch_no" readonly>

<label>Expiry Date</label>
<input type="text" id="req_exp_date" readonly>

<label>Quantity</label>
<input type="number" id="req_qty" name="qty" min="1" required>

<label>Total Rate</label>
<input type="number" id="req_total_rate" name="total_rate" required>

<br><br>

<div class="popup-buttons">

<button type="submit">Request</button>

<button type="button" onclick="closeRequestPopup()">Cancel</button>

</div>

</form>

</div>

</div>

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

<script>

let currentRequestBtn = null;
let selectedFirmId = 0;
let unitPrice = 0;
let isManualEdit = false;

/* =========================
   OPEN POPUP
========================= */

document.addEventListener("click", function(e){

if(e.target.classList.contains("open-request-popup")){

    currentRequestBtn = e.target;

    document.getElementById("requestPopup").style.display = "flex";

    let maxQty = parseInt(e.target.dataset.maxqty);

    selectedFirmId = e.target.dataset.firmId;
    unitPrice = parseFloat(e.target.dataset.unitPrice);

    isManualEdit = false;   // reset negotiation mode

    // DEBUG (optional)
    console.log("Unit Price:", unitPrice);

    document.getElementById("listing_id").value = e.target.dataset.id;
    document.getElementById("firm_name").value = e.target.dataset.firmName;
    document.getElementById("req_prod_name").value = e.target.dataset.name;
    document.getElementById("req_batch_no").value = e.target.dataset.batch;
    document.getElementById("req_exp_date").value = e.target.dataset.exp;

    let qtyInput = document.getElementById("req_qty");
    let totalInput = document.getElementById("req_total_rate");

    /* SET QTY */
    qtyInput.value = maxQty;
    qtyInput.max = maxQty;
    qtyInput.min = 1;
    qtyInput.dataset.maxqty = maxQty;

    /* 🔥 INITIAL AUTO PRICE */
totalInput.value = Math.ceil(maxQty * unitPrice);
}
});

/* =========================
   REAL-TIME PRICE UPDATE
========================= */

document.getElementById("req_qty").addEventListener("input", function(){

let max = parseInt(this.dataset.maxqty);
let qty = parseInt(this.value);

/* validation */
if(isNaN(qty) || qty < 1){
    qty = 1;
    this.value = 1;
}

if(qty > max){
    alert("Only " + max + " quantity available");
    qty = max;
    this.value = max;
}

/* 🔥 AUTO CALCULATION */
if(!isManualEdit){
    let total = qty * unitPrice;
    document.getElementById("req_total_rate").value = Math.ceil(total); 
}

});

/* =========================
   MANUAL NEGOTIATION MODE
========================= */

document.getElementById("req_total_rate").addEventListener("input", function(){
    isManualEdit = true;   // stop auto overwrite
});

/* =========================
   CLOSE POPUP
========================= */

function closeRequestPopup(){
document.getElementById("requestPopup").style.display = "none";
}

/* =========================
   SUBMIT REQUEST
========================= */

document.getElementById("requestForm").addEventListener("submit", function(e){

e.preventDefault();

let formData = new FormData(this);

/* ADD REQUIRED FIELDS */

formData.append("to_firm_id", selectedFirmId);
formData.append("prod_name", document.getElementById("req_prod_name").value);
formData.append("batch_no", document.getElementById("req_batch_no").value);

/* AJAX */

fetch("send_request.php",{
method:"POST",
body:formData
})
.then(res => res.text())
.then(data => {

/* SUCCESS */

if(data.includes("success") || data.trim() === ""){

closeRequestPopup();

/* UPDATE BUTTON */

if(currentRequestBtn){

currentRequestBtn.innerText = "Requested";
currentRequestBtn.disabled = true;
currentRequestBtn.classList.remove("request-btn");
currentRequestBtn.classList.add("requested-btn");

}

}else{

alert("Request failed");

}

})
.catch(err => {
console.error(err);
alert("Network error");
});

});

</script>

</body>
</html>