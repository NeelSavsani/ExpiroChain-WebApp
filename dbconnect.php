<?php
$database = "exch_user";
$user_table = "user_table";
$verification_table = "user_verification";

$conn = mysqli_connect("127.0.0.1", "root", "", $database);

if(!$conn){
    die("Error".mysqli_connect_error());
}
else{
    $sql = "CREATE DATABASE IF NOT EXISTS `$database`";
    $result = mysqli_query($conn, $sql);
    if(!$result){
        echo "Creation of table was failed<br>";
    }else{
        $sql = "CREATE TABLE IF NOT EXISTS `$database`.`$user_table`(
        `user_id` INT(10) NOT NULL AUTO_INCREMENT, 
        `firm_name` VARCHAR(30) NOT NULL, 
        `owner_name` VARCHAR(30) NOT NULL, 
        `user_type` VARCHAR(7) NOT NULL, 
        `user_pass` VARCHAR(20) NOT NULL, 
        `gstno` VARCHAR(15) NOT NULL UNIQUE, 
        `dl1` VARCHAR(30) NOT NULL UNIQUE, 
        `dl2` VARCHAR(30) NOT NULL UNIQUE, 
        `phn_no` VARCHAR(10) NOT NULL, 
        `email_id` VARCHAR(50) NOT NULL, 
        `address` VARCHAR(100) NOT NULL, 
        `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
        PRIMARY KEY(`user_id`)
        ) ENGINE = InnoDB ";
        $result = mysqli_query($conn, $sql);
        if(!$result){
            echo "Creation of user table is failed<br>";
        }else{
            $sql = "CREATE TABLE IF NOT EXISTS `$database`.`$verification_table`(
            `vrf_id` INT(10) NOT NULL AUTO_INCREMENT, 
            `user_id` INT(10) NOT NULL, 
            `firm_name` VARCHAR(30) NOT NULL, 
            `status` VARCHAR(8) NOT NULL DEFAULT 'Pending', 
            `gst_proof_path` VARCHAR(255) UNIQUE, 
            `dl1_proof_path` VARCHAR(255) UNIQUE, 
            `dl2_proof_path` VARCHAR(255) UNIQUE, 
            `dbname` VARCHAR(35) NOT NULL UNIQUE, 
            `remarks` VARCHAR(255), 
            `registered_at` DATETIME NOT NULL, 
            `approved_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
            PRIMARY KEY(`vrf_id`),
            INDEX (`user_id`),

            CONSTRAINT `fk_verification_user`
                FOREIGN KEY (`user_id`)
                REFERENCES `user_table` (`user_id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE   
            ) ENGINE = InnoDB";
            $result = mysqli_query($conn, $sql);
            if(!$result){
                echo "Creation of veification table is faied<br>";
            }
        }
    }
}

?>