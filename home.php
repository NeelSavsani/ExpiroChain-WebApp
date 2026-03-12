<?php
session_start();
include "dbconnect.php";

if (!isset($_SESSION['user_id'], $_SESSION['firm_name'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$firm_name = $_SESSION['firm_name'];

/* GET USER DATABASE */

$q = "SELECT dbname FROM user_verification WHERE user_id = $user_id";
$r = mysqli_query($conn,$q);
$data = mysqli_fetch_assoc($r);

if(!$data){
    die("Database not found");
}

$dbname = $data['dbname'];

/* SWITCH DATABASE */

mysqli_select_db($conn,$dbname);


/* TOTAL PRODUCTS */

$q_products = "SELECT COUNT(*) AS total_products FROM prod_table";
$r_products = mysqli_query($conn,$q_products);
$total_products = mysqli_fetch_assoc($r_products)['total_products'];


/* TOTAL STOCK */

$q_stock = "SELECT COUNT(*) AS total_stock FROM stock_table";
$r_stock = mysqli_query($conn,$q_stock);
$total_stock = mysqli_fetch_assoc($r_stock)['total_stock'];


/* TOTAL MEDICINES */

$q_medicine = "SELECT COUNT(*) AS total_medicine 
FROM prod_table 
WHERE category='Medicine'";
$r_medicine = mysqli_query($conn,$q_medicine);
$total_medicine = mysqli_fetch_assoc($r_medicine)['total_medicine'];


/* TOTAL COSMETICS */

$q_cosmetic = "SELECT COUNT(*) AS total_cosmetic 
FROM prod_table 
WHERE category='Cosmetic'";
$r_cosmetic = mysqli_query($conn,$q_cosmetic);
$total_cosmetic = mysqli_fetch_assoc($r_cosmetic)['total_cosmetic'];


/* TOTAL OTHERS */

$q_other = "SELECT COUNT(*) AS total_other 
FROM prod_table 
WHERE category='Other'";
$r_other = mysqli_query($conn,$q_other);
$total_other = mysqli_fetch_assoc($r_other)['total_other'];
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8" />

<title>Dashboard | EXPIROCHAIN</title>

<link rel="shortcut icon" href="/exp/images/favicon/android-chrome-192x192.png" />
<link rel="stylesheet" href="/exp/css/home.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<?php include "user/layout.php"; ?>

<div class="dashboard">

<div class="welcome-card">

<h2>
Welcome, <span><?php echo htmlspecialchars($firm_name); ?></span>
</h2>

<p>
Manage medicine expiry, reduce waste, and stay compliant — all in one place.
</p>

</div>

<br>

<div class="stats">

<div class="stat-box">
<h3>Total Products</h3>
<p><?php echo $total_products; ?></p>
</div>

<div class="stat-box">
<h3>Total Stock</h3>
<p><?php echo $total_stock; ?></p>
</div>

<div class="stat-box">
<h3>Total Medicines</h3>
<p><?php echo $total_medicine; ?></p>
</div>

<div class="stat-box">
<h3>Total Cosmetics</h3>
<p><?php echo $total_cosmetic; ?></p>
</div>

</div>

<br>

<div class="stats">

<div class="stat-box">
<h3>Total Others</h3>
<p><?php echo $total_other; ?></p>
</div>

<div class="stat-box">
<h3>Near Expiry</h3>
<p>0</p>
</div>

<div class="stat-box">
<h3>Total Near Expiry Sold</h3>
<p>0</p>
</div>

<div class="stat-box">
<h3>Expired</h3>
<p>0</p>
</div>

</div>

<div class="add-product-container">
<a href="/exp/user/add_product.php" class="add-product-btn">
<i class="fa-solid fa-plus"></i> Add Product
</a>
</div>

<!-- CHART SECTION -->

<div class="charts-container">

<div class="chart-box">
<h3>Product Categories</h3>
<canvas id="categoryChart" height="300"></canvas>
</div>

<div class="chart-box">
<h3>Inventory Overview</h3>
<canvas id="inventoryChart" height="300"></canvas>
</div>

</div>

</div>

</div>

<footer>
© <?php echo date('Y'); ?> EXPIROCHAIN and Team
</footer>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

const medicines = <?php echo $total_medicine; ?>;
const cosmetics = <?php echo $total_cosmetic; ?>;
const others = <?php echo $total_other; ?>;

const products = <?php echo $total_products; ?>;
const stock = <?php echo $total_stock; ?>;


/* CATEGORY PIE CHART */

const categoryCtx = document.getElementById("categoryChart").getContext("2d");

new Chart(categoryCtx, {

type: "pie",

data: {

labels: [
"Medicines",
"Cosmetics",
"Others"
],

datasets: [{
data: [
medicines,
cosmetics,
others
],

backgroundColor: [
"#2563eb",
"#16a34a",
"#f59e0b"
]
}]

},

options: {

responsive: true,

plugins: {

legend: { position: "bottom" },

title: {
display: true,
text: "Product Categories"
}

}

}

});


/* INVENTORY BAR CHART */

const inventoryCtx = document.getElementById("inventoryChart").getContext("2d");

new Chart(inventoryCtx, {

type: "bar",

data: {

labels: [
"Products",
"Stock"
],

datasets: [{

label: "Inventory",

data: [
products,
stock
],

backgroundColor: [
"#2563eb",
"#10b981"
],

borderRadius: 6

}]

},

options: {

responsive: true,

plugins: {

legend: { display:false },

title: {
display:true,
text:"Inventory Overview"
}

},

scales:{
y:{
beginAtZero:true
}
}

}

});

});
</script>

</body>
</html>