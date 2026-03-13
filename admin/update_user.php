<?php

include "../dbconnect.php";

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$id = $_POST['id'];
$action = $_POST['action'];


/* ---------------- GET USER DATABASE NAME ---------------- */

$q = "SELECT v.dbname, u.email_id, u.owner_name, u.firm_name
      FROM user_verification v
      JOIN user_table u ON v.user_id = u.user_id
      WHERE v.user_id = $id";

$r = mysqli_query($conn,$q);
$data = mysqli_fetch_assoc($r);

$dbname = $data['dbname'];
$email = $data['email_id'];
$owner = $data['owner_name'];
$firm  = $data['firm_name'];


/* ---------------- APPROVE USER ---------------- */

if($action == "approve"){

    /* update approval status */

    mysqli_query($conn,"
        UPDATE user_verification
        SET isApproved = 1,
            approved_at = CURRENT_TIMESTAMP
        WHERE user_id = $id
    ");


    /* create database for that firm */

    mysqli_query($conn,"CREATE DATABASE IF NOT EXISTS `$dbname`");


    /* switch to that database */

    mysqli_select_db($conn,$dbname);


    /* ---------------- PRODUCT TABLE ---------------- */

    $prod_table = "
    CREATE TABLE IF NOT EXISTS prod_table(

        prod_id INT AUTO_INCREMENT PRIMARY KEY,

        barcode VARCHAR(50) UNIQUE,

        prod_name VARCHAR(30) NOT NULL,

        category ENUM('Medicine','Cosmetic','Other') NOT NULL,

        manufacturer VARCHAR(30),

        expiry_applicable BOOLEAN DEFAULT 1,

        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

        INDEX idx_barcode(barcode),
        INDEX idx_prod_name(prod_name),
        INDEX idx_category(category)

    ) ENGINE=InnoDB
    ";

    mysqli_query($conn,$prod_table);


    /* ---------------- STOCK TABLE ---------------- */

    $stock_table = "
    CREATE TABLE IF NOT EXISTS stock_table(

        stock_id INT AUTO_INCREMENT PRIMARY KEY,

        prod_id INT NOT NULL,

        prod_name VARCHAR(50) NOT NULL,

        batch_no VARCHAR(50) NOT NULL,

        exp_date DATE NULL,

        qty INT NOT NULL,

        added_at DATETIME DEFAULT CURRENT_TIMESTAMP,

        INDEX idx_prod_id(prod_id),
        INDEX idx_batch(batch_no),

        CONSTRAINT fk_stock_product
        FOREIGN KEY (prod_id)
        REFERENCES prod_table(prod_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

    ) ENGINE=InnoDB
    ";

    mysqli_query($conn,$stock_table);


    /* ---------------- ACTION LOG TABLE ---------------- */

    $action_log = "
    CREATE TABLE IF NOT EXISTS action_log(

        actn_id INT AUTO_INCREMENT PRIMARY KEY,

        stock_id INT NOT NULL,

        action_type ENUM('Transfer','Donate','Dispose','Sale','Adjust') NOT NULL,

        qty INT NOT NULL,

        to_firm_name VARCHAR(100),

        remarks VARCHAR(250),

        days_left_at_action INT,

        risk_score_at_action INT,

        action_date DATETIME DEFAULT CURRENT_TIMESTAMP,

        performed_by VARCHAR(100),

        INDEX idx_stock_id(stock_id),
        INDEX idx_action_type(action_type),

        CONSTRAINT fk_action_stock
        FOREIGN KEY (stock_id)
        REFERENCES stock_table(stock_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

    ) ENGINE=InnoDB
    ";

    mysqli_query($conn,$action_log);



    /* ---------------- SEND APPROVAL EMAIL ---------------- */

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_USER, APP_NAME);

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = "Account Approved - " . APP_NAME;

        $mail->Body = "
        <h2>Account Approved ✅</h2>

        <p>Dear <b>$owner</b>,</p>

        <p>Your organization <b>$firm</b> has been successfully <b>approved by the admin</b>.</p>

        <p>You can now log in to the <b>" . APP_NAME . "</b> platform using your login credentials.</p>

        <br>

        <p>
        <a href='https://51ae-2409-40c1-2f-d78e-bc84-d6f4-eeb5-eb05.ngrok-free.app/exp/login.php'
        style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>
        Login Now
        </a>
        </p>

        <br>

        <p>Regards,<br>
        <b>Team " . APP_NAME . "</b></p>
        ";

        $mail->send();

    } catch (Exception $e) {
        // ignore mail error
    }

}


/* ---------------- REJECT USER ---------------- */

if($action == "reject"){

    mysqli_query($conn,"
        UPDATE user_verification
        SET isApproved = 0
        WHERE user_id = $id
    ");

}


/* ---------------- REDIRECT ---------------- */

header("Location: admin_vrf.php");
exit();

?>