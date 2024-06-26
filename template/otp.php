<?php  
$name = '';
if(isset($_SESSION['username'])){
    $name = $_SESSION['username'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activate Your Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

    <!-- I don't know why I couldn't use an external style sheet file -->
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #f5f5f5;
            text-align: center;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #212121;
            color: #fff;
            padding: 10px;
            text-align: center;
            border-radius: 5px;

        }

        h1 {
            font-size: 28px;
            margin: 0;
            text-align: center;
            font-weight: 600;
        }

        p {
            font-size: 12px;
            margin: 10px 0 10px 0;
        }

        .btn {
            background-color: #2196f3;
            border: none;
            color: #fff;
            padding: 10px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        .btn:hover {
            background-color: #0b7dda;
        }

    </style>
</head>

<body>
<div class="container">
    <div class="header">
        <h1>Account Activation</h1>
    </div>
    <form action="{{activationLink}}" method="POST">
        <p>Hello <span class="user-name"><?php  echo $name; ?></span>,</p>
        <p>Thank you for signing up for our service. <br> To activate your account, please click the button below:</p>
        <button type="submit" class="btn">Activate Your Account Now</button>
        <input type="hidden" name="activationLink" value="{{activationLink}}"/>
    </form>
    <p>The activation link will expire in 5 minutes.</p>
    <div class="footer">
        <p>If you did not sign up for our service, please ignore this email.</p>
        <p>&copy; 2024 Our Group.   All rights reserved.</p>
    </div>
</div>
</body>
</html>