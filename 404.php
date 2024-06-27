<?php
$page= '../index.php';
if($_SESSION['user_role'] === 1){
    $page= '../admin/';
} else if($_SESSION['user_role'] == 2){
    $page= '../user/';
} else if($_SESSION['user_role'] == 3){
    $page= '../index.php';
} else if($_SESSION['user_role'] === 4){
    $page= '../index.php';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Access Denied</title>
</head>
<?php  include WEB_PAGE_TO_ROOT ."template/header-name.php" ?>

<div style="margin-top:100px;">
<form style= "width: 60%;">
<h3><?php echo $_SESSION['username'];?>, You do not have Permissions to Access this Page.</h3>
<br>
<a href = "<?php echo $page; ?>" class="register">Go Back</a>
</form>
</div>


</html>