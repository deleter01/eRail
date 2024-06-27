<?php

define( 'WEB_PAGE_TO_ROOT', '../' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';


PageStartup( array( 'authenticated' ) );
DatabaseConnect();

if (checkPermissions($_SESSION['user_id'], 2) == "false") {
  header("HTTP/1.0 403 Forbidden");
  require_once WEB_PAGE_TO_ROOT . '404.php';
  exit();
}


    $welcome_name = $_SESSION['username'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Page</title>
</head>
<?php include WEB_PAGE_TO_ROOT ."template/header-name.php" ?>

<div style="margin-top:100px;">
<form> 
    <h3 class="heading">Welcome <?php echo $welcome_name ?></h3> <br>
    <div class="line-box">
      <div class="line"></div>
    </div><br><br>
    <a href="book-ticket" class="register"> Book New Ticket </a><br><br><br>
    <a href="view-trains" class="register">View All Trains</a><br><br><br>
    <a href="view-status" class="register">View Train Status</a><br><br><br>
    <a href="view-user-booking" class="register">View Past Bookings</a><br><br><br>
    <div class="line-box">
      <div class="line"></div>
    </div>

    <? tokenField(); ?>
</form>
</div>


</html>