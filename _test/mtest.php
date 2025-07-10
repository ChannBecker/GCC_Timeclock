<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'Utility/phpmailer/Exception.php'; // Adjust the path
require 'Utility/phpmailer/PHPMailer.php'; // Adjust the path
require 'Utility/phpmailer/SMTP.php'; // Adjust the path

try {

$mail = new PHPMailer(true);
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Enable verbose debug output
    $mail->isSMTP(); // Send using SMTP
    $mail->Host = 'smtp.office365.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'NoReply@senvoy.com'; // SMTP username
    $mail->Password = 'DoNotReply!'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port = 587; // TCP port to connect to, use 587 for TLS

    //Recipients
    $mail->setFrom('NoReply@senvoy.com', 'Do Not Reply');

    
        $mail->addAddress('cbecker@senvoy.com');
    
//    $mail->addAddress('bwiggins@gccmgt.com'); // Add a recipient
    $body = "This is a test message generated on timeclock.";
    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'TEST Mail Message from Timeclock'; //. trim($emp['employee_number']). " - ". $emp['fname']. " ". $emp['lname'];
    $mail->Body = $body;
    $mail->AltBody = $body;

    $mail->send();
    echo "email sent"; sleep(10);
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

    ?>