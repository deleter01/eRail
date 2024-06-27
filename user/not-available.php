<?php
define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';
    
DatabaseConnect();
if (checkPermissions($_SESSION['user_id'], 2) == "false") {
    header("HTTP/1.0 403 Forbidden");
    require_once WEB_PAGE_TO_ROOT . '404.php';
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Not Available</title>
</head>
<?php include WEB_PAGE_TO_ROOT ."template/header-name.php" ?>

<div style="margin-top:100px;">
<form style= "width: 60%;">
<h3><?php echo $_SESSION['username'] ?>, we regret the inconvenience caused.</h3>
<h5 ><?php if (isset($_SESSION['seats_error'])) echo $_SESSION['seats_error'];?></h5><br>
<a href = "passenger-details" class="register">Go Back</a>
</form>
</div>


</html>