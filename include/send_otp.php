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
        $mail->isSMTP();
        $mail->Host = 'ssl://smtp.gmail.com';
        $mail->Port = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAuth = true;
        $mail->Username = 'deleterali1@gmail.com';
        $mail->Password = 'ctxpyzandmejyouo';
        

        // Recipients
        $mail->setFrom('deleterali1@gmail.com', 'eRail');
        $mail->addAddress($email);                 // Add a recipient

        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = '
         <html>
        <head>
            <style>
                .container {
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    padding: 20px;
                    border: 1px solid #ddd;
                }
                .header {
                    background-color: #007bff;
                    color: #fff;
                    padding: 10px;
                    text-align: center;
                }
                .content {
                    padding: 20px;
                }
                .footer {
                    padding: 10px;
                    text-align: center;
                    font-size: 12px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>eRail</h2>
                </div>
                <div class="content">
                    <p>Dear User,</p>
                    <p>Your OTP code is: <strong>' . $otp . '</strong></p>
                    <p>Please use this code to complete your registration.</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date("Y") . ' eRail. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        $mail->AltBody = 'Your OTP code is: ' . $otp;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>
