<?php
// Include PHPMailer library files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Function to generate OTP
function generateOTP($length = 6) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[rand(0, $charactersLength - 1)];
    }
    return $otp;
}

// Function to send OTP email
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Enable verbose debug output
        $mail->isSMTP();                           // Send using SMTP
        $mail->Host       = 'smtp.example.com';    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = 'your_email@example.com';     // SMTP username
        $mail->Password   = 'your_email_password';        // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                    // TCP port to connect to

        // Recipients
        $mail->setFrom('your_email@example.com', 'Your Name');
        $mail->addAddress($email);                 // Add a recipient

        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = 'Your OTP code is: <b>' . $otp . '</b>';
        $mail->AltBody = 'Your OTP code is: ' . $otp;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Usage example
$email = 'recipient@example.com'; // Replace with recipient email
$otp = generateOTP();
$result = sendOTP($email, $otp);

if ($result === true) {
    echo 'OTP has been sent successfully.';
} else {
    echo 'Failed to send OTP. Error: ' . $result;
}
?>
