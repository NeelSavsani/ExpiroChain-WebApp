<?php

$database = "exch_user";
$user_table = "user_table";
$verification_table = "user_verification";

/* CONNECT */

$conn = mysqli_connect("localhost", "root", "");

if(!$conn){
    die("Connection Error: " . mysqli_connect_error());
}

/* CREATE DATABASE */

$sql = "CREATE DATABASE IF NOT EXISTS `$database`";
$result = mysqli_query($conn, $sql);

if(!$result){
    die("Database creation failed");
}

/* SELECT DATABASE */

mysqli_select_db($conn, $database);

/* CREATE USER TABLE */

$sql = "CREATE TABLE IF NOT EXISTS `$user_table`(

    `user_id` INT(10) NOT NULL AUTO_INCREMENT,
    `firm_name` VARCHAR(30) NOT NULL,
    `owner_name` VARCHAR(30) NOT NULL,
    `user_type` VARCHAR(20) NOT NULL,
    `user_pass` VARCHAR(50) NOT NULL,
    `gstno` VARCHAR(15) NOT NULL UNIQUE,
    `dl1` VARCHAR(30) NOT NULL UNIQUE,
    `dl2` VARCHAR(30) NOT NULL UNIQUE,
    `phn_no` VARCHAR(10) NOT NULL,
    `email_id` VARCHAR(50) NOT NULL,
    `address` VARCHAR(100) NOT NULL,

    `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY(`user_id`),

    INDEX `idx_email` (`email_id`),
    INDEX `idx_phone` (`phn_no`),
    INDEX `idx_firm` (`firm_name`)

) ENGINE=InnoDB";

$result = mysqli_query($conn, $sql);

if(!$result){
    echo "Creation of user table failed<br>";
}


/* CREATE VERIFICATION TABLE */

$sql = "CREATE TABLE IF NOT EXISTS `$verification_table`(

    `vrf_id` INT(10) NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) NOT NULL,
    `firm_name` VARCHAR(30) NOT NULL,

    `isApproved` TINYINT(1) NOT NULL DEFAULT 0,

    `gst_proof_path` VARCHAR(255) UNIQUE,
    `dl1_proof_path` VARCHAR(255) UNIQUE,
    `dl2_proof_path` VARCHAR(255) UNIQUE,

    `dbname` VARCHAR(35) NOT NULL UNIQUE,
    `remarks` VARCHAR(255),

    `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `approved_at` DATETIME DEFAULT NULL,

    PRIMARY KEY(`vrf_id`),

    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_isApproved` (`isApproved`),
    INDEX `idx_firm_name` (`firm_name`),

    CONSTRAINT `fk_verification_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `$user_table` (`user_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB";

$result = mysqli_query($conn, $sql);

if(!$result){
    echo "Creation of verification table failed<br>";
}


/* CREATE MARKETPLACE TABLE */

$sql = "CREATE TABLE IF NOT EXISTS marketplace_listings (

    listing_id INT(11) NOT NULL AUTO_INCREMENT,
    stock_id INT(11) DEFAULT NULL,
    prod_name VARCHAR(100) DEFAULT NULL,
    batch_no VARCHAR(50) DEFAULT NULL,
    qty INT(11) DEFAULT NULL,
    total_rate FLOAT(5) NOT NULL,
    exp_date DATE DEFAULT NULL,

    firm_id INT(11) DEFAULT NULL,
    firm_name VARCHAR(150) DEFAULT NULL,

    listed_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    status ENUM('Active','Sold','Removed') DEFAULT 'Active',

    PRIMARY KEY (listing_id),
    KEY firm_id (firm_id)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci";

$result = mysqli_query($conn, $sql);

if(!$result){
    echo "Creation of marketplace listing table failed<br>";
}

/*CREATE EXCHANGE REQUESTS TABLE */
$sql = "CREATE TABLE IF NOT EXISTS exchange_requests (

request_id INT AUTO_INCREMENT PRIMARY KEY,

listing_id INT NOT NULL,
prod_name VARCHAR(100) NOT NULL,
batch_no VARCHAR(50) NOT NULL,
qty_requested INT NOT NULL,

from_firm_id INT NOT NULL,
to_firm_id INT NOT NULL,

status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',

request_date DATETIME DEFAULT CURRENT_TIMESTAMP,

-- INDEXES

INDEX idx_listing_id (listing_id),
INDEX idx_from_firm (from_firm_id),
INDEX idx_to_firm (to_firm_id),
INDEX idx_status (status),
INDEX idx_request_date (request_date)

) ENGINE=InnoDB;";
$result = mysqli_query($conn, $sql);
if(!$result){
    echo "Creation of exchange requests failed<br>";
}
?>