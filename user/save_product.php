<?php
session_start();
include "../dbconnect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /exp/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------------- GET USER DATABASE ---------------- */

$q = "SELECT dbname FROM user_verification WHERE user_id = $user_id";
$r = mysqli_query($conn,$q);
$data = mysqli_fetch_assoc($r);

if(!$data){
    die("Database not found");
}

$dbname = $data['dbname'];

/* ---------------- SWITCH DATABASE ---------------- */

mysqli_select_db($conn,$dbname);

/* ---------------- GET FORM DATA ---------------- */

$barcode = $_POST['barcode'];
$prod_name = $_POST['prod_name'];
$category = $_POST['category'];
$manufacturer = $_POST['manufacturer'];

$expiry_applicable = isset($_POST['expiry_applicable']) ? 1 : 0;

/* ---------------- VALIDATION ---------------- */

if(empty($barcode) || empty($prod_name) || empty($category)){
    die("<script>alert('Required fields missing');history.back();</script>");
}

/* ---------------- DUPLICATE BARCODE CHECK ---------------- */

$check = mysqli_query($conn,"
SELECT prod_id 
FROM prod_table 
WHERE barcode = '$barcode'
");

if(mysqli_num_rows($check) > 0){
    echo "<script>
    alert('Product with this barcode already exists');
    window.history.back();
    </script>";
    exit();
}

/* ---------------- INSERT PRODUCT ---------------- */

$sql = "
INSERT INTO prod_table
(barcode, prod_name, category, manufacturer, expiry_applicable)
VALUES
('$barcode','$prod_name','$category','$manufacturer','$expiry_applicable')
";

$result = mysqli_query($conn,$sql);

if(!$result){
    die("Product insert failed");
}

/* ---------------- SUCCESS ---------------- */

echo "<script>
alert('Product added successfully');
window.location.href='/exp/user/add_product.php';
</script>";

?>