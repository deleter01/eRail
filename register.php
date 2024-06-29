<?php
define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';
require_once WEB_PAGE_TO_ROOT . 'include/send_otp.php';

Logout();
PageStartup( array( ) );

DatabaseConnect();

    $errors = array('name' => '', 'username' => '', 'email' => '', 'address' => '', 'password' => '', 'confirmp' => '', 'error' => '');
    $success = array('sucess' => '');
    $name = $username = $email = $address = $password = $confirmp = $error = $sucess= '';

    if(isset($_POST['register']) && isset ($_POST['name']) && isset ($_POST['email']) && isset ($_POST['password']) && isset ($_POST['confirmp'])){

        // checkToken( $_REQUEST[ 'user_token' ], $session_token, 'register.php' );


        // Sanitise  input
        $name = $_POST[ 'name' ];
        $name = trim( $name );
        $name = stripslashes( $name );
        $name = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $name ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if (strlen($name) >= 20) {
            $errors['error'] = "Invalid Fullname Length.";
        }

        $username = $_POST['username'];
        $username = trim( $username );
        $username = stripslashes( $username );
        $username = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $username ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if(!preg_match('/^[a-zA-Z]+$/', $username)){
            $errors['username'] .= 'Username must consist of letters only';
        }
        if (strlen($username) >= 20) {
            $errors['error'] = "Invalid Username Length.";
        }

        $email = $_POST['email'];
        $email = trim( $email );
        $email = stripslashes( $email );
        $email = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $email ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Email must be a valid email address';
        }
        if (strlen($email) >= 30) {
            $errors['error'] = "Invalid Email Length.";
        }

        $address = $_POST['address'];
        $address = trim( $address );
        $address = stripslashes( $address );
        $address = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $address ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if (strlen($address) >= 20) {
            $errors['error'] = "Invalid Address Length.";
        }

        $password = $_POST['password'];
        $password = trim( $password );
        $password = stripslashes( $password );
        $password = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $password ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        
        if (strlen($password) >= 50) {
            $errors['error'] = "Invalid Password Length.";
        }
        // if(!isValidPassword($password)){
        //     $errors['error'] = 'Password must be between 8 and 10 characters long, and contain at least one number, one uppercase letter, and one lowercase letter.';
        // }

        $confirmp = $_POST['confirmp'];
        $confirmp = trim( $confirmp );
        $confirmp = stripslashes( $confirmp );
        $confirmp = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $confirmp ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

        if (strlen($confirmp) >= 50) {
            $errors['error'] = "Invalid Confirm Password Length.";
        }

        if (empty($name)) {
            $errors['name'] = 'Name is required';
        }
    
        if (empty($username) || !preg_match('/^[a-zA-Z]+$/', $username)) {
            $errors['username'] = 'Username must consist of letters only';
        }
    
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid email address';
        }
    
        if (empty($address)) {
            $errors['address'] = 'Address is required';
        }
    
        if (empty($password) || strlen($password) < 3) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if($confirmp != $password){
            $errors['confirmp'] = 'Confirm your password again';
        } 
        $options = [ 'cost' => 12,];
        $password = password_hash($password, PASSWORD_BCRYPT, $options);

    try {
        // Check the database (Check user information)
        $data = $db->prepare( 'SELECT user, email FROM users WHERE user = (:user) OR email = (:email) LIMIT 1;' );
        $data->bindParam( ':user', $username, PDO::PARAM_STR );
        $data->bindParam( ':email', $email, PDO::PARAM_STR );
        $data->execute();
        $row = $data->fetch();

        if( ( $data->rowCount() == 1 ) && ( $row[ 'user' ] == $username || $row[ 'email' ] == $email) )  {
            $errors['error'] = 'Username or Email Exits';
        }

        if(! array_filter($errors)){
            $otp = generateOTP();

            $result = sendOTP($email, $otp);

            $role = 4;
            // Update database
            $data = $db->prepare( 'INSERT INTO users ( user, name, email, token, address, role, password ) VALUES ( :user, :name, :email, :token, :address, :role, :password );' );
            $data->bindParam( ':user', $username, PDO::PARAM_STR );
            $data->bindParam( ':name', $name, PDO::PARAM_STR );
            $data->bindParam( ':email', $email, PDO::PARAM_STR );
            $data->bindParam( ':token', $otp, PDO::PARAM_STR );
            $data->bindParam( ':address', $address, PDO::PARAM_STR );
            $data->bindParam( ':role', $role, PDO::PARAM_STR );
            $data->bindParam( ':password', $password, PDO::PARAM_STR );

            if ( $data->execute() ) {
                $result = sendOTP($email, $otp);
                if ($result === false) {
                    $errors['error'] =  'Failed to send OTP. ';
                }
                if ( $data->rowCount() == 1) {

                    //Get Last ID
                    $last_id = $db->lastInsertId();

                    // user to role
                    try {
                        $data = $db->prepare( 'INSERT INTO system_users_to_roles ( user_id, role_id ) VALUES ( :user_id, :role );' );
                        $data->bindParam( ':user_id', $last_id, PDO::PARAM_STR );
                        $data->bindParam( ':role', $role, PDO::PARAM_STR );
                        $data->execute();
                    } catch (PDOException $e) {
                        $errors['error'] = "Error: " . $e->getMessage();
                    } 

                    $db = NULL;
                    $success['sucess'] = "User Has Been Registed And OTP has been sent to your Email";

                    // $table = 'users';
                    // $data = '('. 'user => '. $username . "," .'name => '. $name . ",". 'email => '. $email . ",". 'address => '. $address .')';
                    // $presant_data = hash('sha256', hash('sha256', $data));
                    // action_logs($table,$_SESSION['user'], "Register User",$presant_data);
                    sleep(6);
                    Redirect('activate');
                } else {

                    // $table = 'users';
                    // $data = '('. 'user => '. $username . "," .'name => '. $name . ",". 'email => '. $email . ",". 'address => '. $address . ')';
                    // $presant_data = hash('sha256', hash('sha256', $data));
                    // action_logs($table,$_SESSION['user'], "Fail to Register User",$presant_data);
                    $errors['error'] = "Error inserting user.";
                }
            } else {

                // $table = 'users';
                // $data = '('. 'user => '. $username . "," .'name => '. $name . ",". 'email => '. $email . ",". 'address => '. $address .')';
                // $presant_data = hash('sha256', hash('sha256', $data));
                // action_logs($table,$_SESSION['user'], "Fail to Register User",$presant_data);
                $errors['error'] = "Error inserting user.";
            }
        }
    } catch (\Throwable $th) {
        $errors['error'] = "Error inserting user.";
    }
    }

    // function isValidPassword($password) {
    //     // Password length between 8 and 10 characters
    //     if (strlen($password) < 8 || strlen($password) > 10) {
    //         return false;
    //     }
    
    //     // Password must contain at least one number
    //     if (!preg_match('/[0-9]/', $password)) {
    //         return false;
    //     }
    
    //     // Password must contain at least one uppercase letter
    //     if (!preg_match('/[A-Z]/', $password)) {
    //         return false;
    //     }
    
    //     // Password must contain at least one lowercase letter
    //     if (!preg_match('/[a-z]/', $password)) {
    //         return false;
    //     }
    
    //     // All requirements met
    //     return true;
    // }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
</head>
<?php include "template/header.php" ?>

<div style="margin-top:100px;">
  <form action="register.php" method="POST">
    <h3 class="heading">Register on eRail</h3>
    <label>
        <p class="label-txt">FULL NAME</p>
        <input type="text" class="input" name="name" maxlength="30" size = "30" onchange="validateBox('name')" value="<?php echo htmlspecialchars($name) ?>">
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['name'])?></p>
    </label>
    <label>
        <p class="label-txt">USERNAME</p>
        <input type="text" class="input" name="username" maxlength="20" size = "20" required value="<?php echo htmlspecialchars($username) ?>">
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['username'])?></p>
    </label>
    <label>
        <p class="label-txt">EMAIL</p>
        <input type="email" class="input" name="email" maxlength="30" size = "30" required value="<?php echo htmlspecialchars($email) ?>">
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['email'])?></p>
    </label>
    <label>
        <p class="label-txt">ADDRESS</p>
        <input type="text" class="input" name="address" maxlength="30" size = "30" required value="<?php echo htmlspecialchars($address) ?>">
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['address'])?></p>
    </label>
    <label>
        <p class="label-txt">PASSWORD</p>
        <input type="password" class="input" name="password" maxlength="50" minlength="8" size = "50" required>
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['password'])?></p>
    </label>
    <label>
        <p class="label-txt">CONFIRM PASSWORD</p>
        <input type="password" class="input" name="confirmp" maxlength="50" minlength="8" size = "50" required>
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['confirmp'])?></p>
    </label>
    <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['error'])?></p>
    <p class="bg-success text-white"><?php echo htmlspecialchars($success['sucess'])?></p>
    <a href="index.php" class="register">Back</a>
    <button type="submit" name="register" value="submit">Register</button>
</form>
</div>

<script>
    function validateBox(input) {
        var name = document.getElementById("name").value;
        if (name.value.length >= 20) {
            input.setCustomValidity("Input must be not be greater than 20  characters long.");
        } else {
            input.setCustomValidity("");
        }
    }
</script>

</html>
