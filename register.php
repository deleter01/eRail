<?php
define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';

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

        $username = $_POST['username'];
        $username = trim( $username );
        $username = stripslashes( $username );
        $username = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $username ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if(!preg_match('/^[a-zA-Z]+$/', $username)){
            $errors['username'] .= 'Username must consist of letters only';
        }

        $email = $_POST['email'];
        $email = trim( $email );
        $email = stripslashes( $email );
        $email = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $email ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Email must be a valid email address';
        }

        $address = $_POST['address'];
        $address = trim( $address );
        $address = stripslashes( $address );
        $address = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $address ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

        $password = $_POST['password'];
        $password = trim( $password );
        $password = stripslashes( $password );
        $password = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $password ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        $password = sha1( $password );

        $confirmp = $_POST['confirmp'];
        $confirmp = trim( $confirmp );
        $confirmp = stripslashes( $confirmp );
        $confirmp = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $confirmp ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        $confirmp = sha1( $confirmp );

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
            // Update database
            $data = $db->prepare( 'INSERT INTO users ( user, name, email, address, password ) VALUES ( :user, :name, :email, :address, :password );' );
            $data->bindParam( ':user', $username, PDO::PARAM_STR );
            $data->bindParam( ':name', $name, PDO::PARAM_STR );
            $data->bindParam( ':email', $email, PDO::PARAM_STR );
            $data->bindParam( ':address', $address, PDO::PARAM_STR );
            $data->bindParam( ':password', $password, PDO::PARAM_STR );

            if ( $data->execute() ) {
                if ( $data->rowCount() == 1 ) {

                    $db = NULL;
                    $success['sucess'] = "User Has Been Registed.";

                    $table = 'users';
                    $data = '('. 'user => '. $username . "," .'name => '. $name . ",". 'email => '. $email . ",". 'address => '. $address . ",". 'released => '. $admin_name. ')';
                    $presant_data = hash('sha256', hash('sha256', $data));
                    action_logs($table,$_SESSION['user'], "Register User",$presant_data);
                    sleep(6);
                    // Redirect('index.php');
                } else {

                    $table = 'users';
                    $data = '('. 'user => '. $username . "," .'name => '. $name . ",". 'email => '. $email . ",". 'address => '. $address . ",". 'released => '. $admin_name. ')';
                    $presant_data = hash('sha256', hash('sha256', $data));
                    action_logs($table,$_SESSION['user'], "Fail to Register User",$presant_data);
                    $errors['error'] = "Error inserting user.";
                }
            } else {

                $table = 'users';
                $data = '('. 'user => '. $username . "," .'name => '. $name . ",". 'email => '. $email . ",". 'address => '. $address . ",". 'released => '. $admin_name. ')';
                $presant_data = hash('sha256', hash('sha256', $data));
                action_logs($table,$_SESSION['user'], "Fail to Register User",$presant_data);
                $errors['error'] = "Error inserting user.";
            }
        }

    }
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
        <input type="text" class="input" name="name" value="<?php echo htmlspecialchars($name) ?>">
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['name'])?></p>
    </label>
    <label>
        <p class="label-txt">USERNAME</p>
        <input type="text" class="input" name="username" size = "20" required value="<?php echo htmlspecialchars($username) ?>">
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['username'])?></p>
    </label>
    <label>
        <p class="label-txt">EMAIL</p>
        <input type="email" class="input" name="email" size = "20" required value="<?php echo htmlspecialchars($email) ?>">
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['email'])?></p>
    </label>
    <label>
        <p class="label-txt">ADDRESS</p>
        <input type="text" class="input" name="address" size = "20" required value="<?php echo htmlspecialchars($address) ?>">
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['address'])?></p>
    </label>
    <label>
        <p class="label-txt">PASSWORD</p>
        <input type="password" class="input" name="password" size = "20" required>
        <div class="line-box">
            <div class="line"></div>
        </div>
        <p class="bg-danger text-white"><?php echo htmlspecialchars($errors['password'])?></p>
    </label>
    <label>
        <p class="label-txt">CONFIRM PASSWORD</p>
        <input type="password" class="input" name="confirmp" size = "20" required>
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


</html>
