<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

$sender = "neelsavsani7@gmail.com";
$receiver = "neelsavsani1@gmail.com";
$otp = random_int(100000, 999999);


try {
    $mail->isSMTP();                      // Use SMTP
    $mail->Host       = 'smtp.gmail.com'; // Gmail server
    $mail->SMTPAuth   = true;             // Enable authentication
    $mail->Username   = $sender; // Sender email
    $mail->Password   = 'omvp qlqo hntk lrra';        // App password
    $mail->SMTPSecure = 'tls';             // Encryption
    $mail->Port       = 587;               // TLS port
    $mail->setFrom($sender, 'EXPIROCHAIN');
    $mail->addAddress($receiver);
    $mail->isHTML(true);
    $mail->Subject = 'Welcome to EXPIROCHAIN';
    $mail->Body = "
        <h2>Registration Successful 🎉</h2>
        <p>You can now log in and start managing medicine expiry efficiently.</p>
        <br>
        <p>Regards,<br><b>Team EXPIROCHAIN</b></p>
    ";
    $mail->send();
    echo "Mail sent successfully!, otp will be $otp";
} catch (Exception $e) {
    echo "Mail Error: {$mail->ErrorInfo}";
}

// <?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';

// $mail = new PHPMailer(true);

// try {
//     $mail->isSMTP();
//     $mail->Host = 'smtp.gmail.com';
//     $mail->SMTPAuth = true;
//     $mail->Username = 'yourgmail@gmail.com';
//     $mail->Password = 'YOUR_APP_PASSWORD';
//     $mail->SMTPSecure = 'tls';
//     $mail->Port = 587;

//     $mail->setFrom('yourgmail@gmail.com', 'EXPIROCHAIN');
//     $mail->addAddress('receiver@gmail.com');

//     $mail->isHTML(true);
//     $mail->Subject = 'Test Mail';
//     $mail->Body = '<h3>Mail sent successfully 🎉</h3>';

//     $mail->send();
//     echo "Mail sent successfully!";
// } catch (Exception $e) {
//     echo "Mailer Error: " . $mail->ErrorInfo;
// }
// ?>
