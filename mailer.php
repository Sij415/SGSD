<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Include Composer's autoloader
require '../vendor/autoload.php'; // Adjust the path if needed

// Load the .env file from the current directory
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mail = new PHPMailer(true);

// Server settings
$mail->SMTPDebug = SMTP::DEBUG_SERVER;
$mail->isSMTP();
$mail->Host = $_ENV['SMTP_HOST']; // Use the SMTP host from the .env file
$mail->SMTPAuth = true;
$mail->Username = $_ENV['SMTP_USERNAME']; // Use the SMTP username from the .env file
$mail->Password = $_ENV['SMTP_PASSWORD']; // Use the SMTP password from the .env file
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = $_ENV['SMTP_PORT']; // Use the SMTP port from the .env file
$mail->isHtml(true);

// Send the email (you should add your email configuration here)

return $mail;
?>
