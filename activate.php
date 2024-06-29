<?php

define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';
Logout();
PageStartup( array( ) );

DatabaseConnect();

  $errors = array('authenticate' => '', 'success' => '');
 
if(isset($_POST['login']) && isset ($_POST['otp']) && isset ($_POST['email'])){
  try {
    // Anti-CSRF
    if (array_key_exists ("session_token", $_SESSION)) {
      $session_token = $_SESSION[ 'session_token' ];
    } else {
      $session_token = "";
    }

    checkToken( $_REQUEST[ 'user_token' ], $session_token, 'activate.php' );

     // Sanitise username input
     $email = $_POST[ 'email' ];
     $email = stripslashes( $email );
     $email = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $email ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")); 

    // Sanitise username input
    $otp = $_POST[ 'otp' ];
	  $otp = stripslashes( $otp );
    $otp = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $otp ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

    // Default values
    $id = '';
    $total_failed_login = 3;
    $lockout_time       = 3;
    $account_locked     = false;

    // Check the database (Check user information)
    $data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE email= (:email) LIMIT 1;' );
    $data->bindParam( ':email', $user, PDO::PARAM_STR );
    $data->execute();
    $row = $data->fetch();

    // Check to see if the user has been locked out.
    if( ( $data->rowCount() == 1 ) && ( $row[ 'failed_login' ] >= $total_failed_login ) )  {
      // $errors['authenticate'] = "Please try again in {$lockout_time} minutes";

      // Calculate when the user would be allowed to login again
      $last_login = strtotime( $row[ 'last_login' ] );
      $timeout    = $last_login + ($lockout_time * 60);
      $timenow    = time();


      // Check to see if enough time has passed, if it hasn't locked the account
      if( $timenow < $timeout ) {
        $account_locked = true;
        $errors['authenticate'] = "Please try again in {$lockout_time} minutes";
      }
    }

    // Check the database (if username matches the password)
    $data = $db->prepare( 'SELECT * FROM users WHERE email = (:email) AND token = (:token) LIMIT 1;' );
    $data->bindParam( ':email', $email, PDO::PARAM_STR);
    $data->bindParam( ':token', $otp, PDO::PARAM_STR );
    $data->execute();
    $row = $data->fetch();

    // If its a valid login...
    if( ( $data->rowCount() == 1 ) && ( $account_locked == false ) ) {
      
      $lock         = $row[ 'enable_account' ];
      $id           = $row[ 'id' ];

      if($lock == true){
        $errors['authenticate'] = "Account has been enabled, Countinoue to Login ";
      } else {
        
        $table="login";
        action_logs($table, $id, "success to Activate Account", " - ");
        $logs = $presant_data;

        // Reset bad login count
        $data = $db->prepare( 'UPDATE users SET failed_login = "0", enable_account = "1" WHERE email = (:email) LIMIT 1;' );
        $data->bindParam( ':email', $email, PDO::PARAM_STR );
        $data->execute();
        $errors['authenticate'] = "Account is Activated, Countinoue to Login ";
        sleep( rand( 2, 4 ) );
        // Redirect('activate');
        
     }
    } else {
      // Login failed
      sleep( rand( 2, 4 ) );

      $errors['authenticate'] = "Incorrect Input.";

      // Update bad login count
      $data = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1) WHERE email = (:email) LIMIT 1;' );
      $data->bindParam( ':email', $email, PDO::PARAM_STR );
      $data->execute();
      $table='login';
      $presant_data = hash('sha256', hash('sha256', $user. " ". $pass));
      action_logs($table, $id, "Fail to Activate Account",$presant_data);

      // Redirect('index.php');
    }

    $data = $db->prepare( 'UPDATE users SET last_login = now() WHERE email = (:email) LIMIT 1;' );
    $data->bindParam( ':email', $email, PDO::PARAM_STR );
    $data->execute();

  } catch (PDOException $e) {
    $errors['authenticate'] = "Error: ";
  }

  }

      // Prevent caching
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    // Header( 'Cache-Control: no-cache, must-revalidate');    // HTTP/1.1
    Header( 'Content-Type: text/html;charset=utf-8' );      // TODO- proper XHTML headers...
    Header( 'Expires: Tue, 23 Jun 2024 12:00:00 GMT' );     // Date in the past
    
    // Anti-CSRF
    generateSessionToken();



echo "
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <title>Activate Account</title>
</head>";

  include "template/header.php";
echo "
<div style=\"margin-top:100px;\">
<form action=". $_SERVER['PHP_SELF'] ." method=\"POST\">
    <h3 class=\"heading\">Activate Account</h3>
    <p class=\"bg-success text-white\"> Enter OTP that has been sent to your email </p>
    <label>
    <p class=\"label-txt\">ENTER YOUR EMAIL</p>
    <input type=\"text\" class=\"input\" name=\"email\" maxlength=\"30\" size=\"30\" required>
    <div class=\"line-box\">
      <div class=\"line\"></div>
    </div>
  </label>
  <label>
    <p class=\"label-txt\">ENTER YOUR OTP</p>
    <input type=\"number\" class=\"input\" name=\"otp\" maxlength=\"6\" size=\"6\" required>
    <div class=\"line-box\">
      <div class=\"line\"></div>
    </div>
  </label>
  <p class=\"bg-danger text-white\"> ". htmlspecialchars($errors['authenticate']) . " </p>
  <p class=\"bg-success text-white\"> ". htmlspecialchars($errors['success']) . " </p>
  <button type=\"submit\" name=\"login\" value=\"submit\">Activate</button>
  <a href=\"index\" class=\"register\">Back</a>

  " . tokenField() . "

</form>
</div>

</html>";
?>
