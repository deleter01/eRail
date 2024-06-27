<?php

define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';
Logout();
PageStartup( array( ) );

DatabaseConnect();

  $errors = array('authenticate' => '');
 
if(isset($_POST['login']) && isset ($_POST['username']) && isset ($_POST['password'])){
  try {
    // Anti-CSRF
    if (array_key_exists ("session_token", $_SESSION)) {
      $session_token = $_SESSION[ 'session_token' ];
    } else {
      $session_token = "";
    }

    checkToken( $_REQUEST[ 'user_token' ], $session_token, 'index.php' );

    // Sanitise username input
    $user = $_POST[ 'username' ];
	  $user = stripslashes( $user );
    $user = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $user ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

    // Sanitise password input
    $pass = $_POST[ 'password' ];
    $pass = stripslashes( $pass );
    $pass = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $pass ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
    // $pass = hash('sha256', $pass);
    $pass = sha1( $pass );

    // Default values
    $id = '';
    $total_failed_login = 3;
    $lockout_time       = 3;
    $account_locked     = false;

    // Check the database (Check user information)
    $data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
    $data->bindParam( ':user', $user, PDO::PARAM_STR );
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
    $data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
    $data->bindParam( ':user', $user, PDO::PARAM_STR);
    $data->bindParam( ':password', $pass, PDO::PARAM_STR );
    $data->execute();
    $row = $data->fetch();

    // If its a valid login...
    if( ( $data->rowCount() == 1 ) && ( $account_locked == false ) ) {
      
      $failed_login = $row[ 'failed_login' ];
      $last_login   = $row[ 'last_login' ];
      $user         = $row[ 'user'];
      $role         = $row[ 'role'];
      $id           = $row[ 'id' ];

      // Login successful
      $_SESSION['username'] = $user;
      $_SESSION['user'] = $id;
      $_SESSION['user_id'] = $id;
      $_SESSION['user_role'] = $role;
      Login( $user );
      
      $table="login";
      action_logs($table, $id, "success to log in", " - ");
      $logs = $presant_data;

      // Reset bad login count
      $data = $db->prepare( 'UPDATE users SET failed_login = "0" WHERE user = (:user) LIMIT 1;' );
      $data->bindParam( ':user', $user, PDO::PARAM_STR );
      $data->execute();

      if($role === 1){
        Redirect('admin/index');
      } else if($role === 2){
        Redirect('user/');
      }
    } else {
      // Login failed
      sleep( rand( 2, 4 ) );

      $errors['authenticate'] = "Incorrect username or password.";

      // Update bad login count
      $data = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1) WHERE user = (:user) LIMIT 1;' );
      $data->bindParam( ':user', $user, PDO::PARAM_STR );
      $data->execute();
      $table='login';
      $presant_data = hash('sha256', hash('sha256', $user. " ". $pass));
      action_logs($table, $id, "Fail to log in",$presant_data);

      // Redirect('index.php');
    }

    $data = $db->prepare( 'UPDATE users SET last_login = now() WHERE user = (:user) LIMIT 1;' );
    $data->bindParam( ':user', $user, PDO::PARAM_STR );
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
    <title>Home Page</title>
</head>";

  include "template/header.php";
echo "
<div style=\"margin-top:100px;\">
<form action=". $_SERVER['PHP_SELF'] ." method=\"POST\">
    <h3 class=\"heading\">Welcome To eRail</h3>
  <label>
    <p class=\"label-txt\">ENTER YOUR USERNAME</p>
    <input type=\"text\" class=\"input\" name=\"username\" size=\"10\" required>
    <div class=\"line-box\">
      <div class=\"line\"></div>
    </div>
  </label>
  <label>
    <p class=\"label-txt\">ENTER YOUR PASSWORD</p>
    <input type=\"password\" class=\"input\" name=\"password\" AUTOCOMPLETE=\"off\" size=\"20\" required>
    <div class=\"line-box\">
      <div class=\"line\"></div>
    </div>
  </label>
  <p class=\"bg-danger text-white\"> ". htmlspecialchars($errors['authenticate']) . " </p>
  <button type=\"submit\" name=\"login\" value=\"submit\">Sign-In</button>
  <a href=\"register.php\" class=\"register\">Not A Member? Register</a>

  " . tokenField() . "

</form>
</div>

</html>";
?>
