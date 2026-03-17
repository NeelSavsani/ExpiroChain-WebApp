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

$request_id = (int)$_GET['id'];

/* 🔒 START TRANSACTION (VERY IMPORTANT) */
mysqli_begin_transaction($conn);

try {

    /* STEP 1: GET REQUEST DETAILS */
    $q = "SELECT * FROM exchange_requests WHERE request_id='$request_id' FOR UPDATE";
    $r = mysqli_query($conn,$q);

    if(mysqli_num_rows($r) == 0){
        throw new Exception("Request not found");
    }

    $request = mysqli_fetch_assoc($r);

    $listing_id = (int)$request['listing_id'];
    $qty_requested = (int)$request['qty_requested'];

    /* STEP 2: GET MARKETPLACE LISTING */
    $q2 = "SELECT * FROM marketplace_listings WHERE listing_id='$listing_id' FOR UPDATE";
    $r2 = mysqli_query($conn,$q2);

    if(mysqli_num_rows($r2) == 0){
        throw new Exception("Listing not found");
    }

    $listing = mysqli_fetch_assoc($r2);

    $current_qty = (int)$listing['qty'];

    /* 🚫 PREVENT INVALID APPROVAL */
    if($listing['status'] != 'Active'){
        throw new Exception("Listing already sold/removed");
    }

    if($qty_requested > $current_qty){
        throw new Exception("Requested qty exceeds available stock");
    }

    /* STEP 3: HANDLE STOCK */

    if($qty_requested == $current_qty){

        /* ✅ FULL SALE → MARK SOLD */
        $updateListing = "
        UPDATE marketplace_listings
        SET status='Sold', qty=0
        WHERE listing_id='$listing_id'
        ";

    } else {

        /* ✅ PARTIAL SALE → REDUCE QTY */
        $new_qty = $current_qty - $qty_requested;

        $updateListing = "
        UPDATE marketplace_listings
        SET qty='$new_qty'
        WHERE listing_id='$listing_id'
        ";
    }

    if(!mysqli_query($conn,$updateListing)){
        throw new Exception("Failed to update listing");
    }

    /* STEP 4: APPROVE SELECTED REQUEST */
    $approve = "
    UPDATE exchange_requests
    SET status='Approved'
    WHERE request_id='$request_id'
    ";

    if(!mysqli_query($conn,$approve)){
        throw new Exception("Failed to approve request");
    }

    /* STEP 5: REJECT OTHER REQUESTS (IMPORTANT 🔥) */
    $reject = "
    UPDATE exchange_requests
    SET status='Rejected'
    WHERE listing_id='$listing_id'
    AND status='Pending'
    AND request_id != '$request_id'
    ";

    if(!mysqli_query($conn,$reject)){
        throw new Exception("Failed to reject other requests");
    }

    /* ✅ COMMIT ALL */
    mysqli_commit($conn);

    header("Location: received_requests.php");
    exit();

} catch (Exception $e){

    /* ❌ ROLLBACK IF ANY ERROR */
    mysqli_rollback($conn);

    echo "Error: " . $e->getMessage();
}
?>