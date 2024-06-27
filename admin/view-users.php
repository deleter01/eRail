<?php
define( 'WEB_PAGE_TO_ROOT', '../' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';

PageStartup( array( 'authenticated' ) );
    
DatabaseConnect();
if (checkPermissions($_SESSION['user_id'], 1) == "false") {
    header("HTTP/1.0 403 Forbidden");
    require_once WEB_PAGE_TO_ROOT . '404.php';
    exit();
}
    
    // FOR PAGINATION
    $sql = "SELECT * FROM users";
    
    include WEB_PAGE_TO_ROOT ."template/pagination.php";

    $sql = "SELECT *FROM users LIMIT " . $page_first_result . ',' . $results_per_page;  
    $result = $GLOBALS["___conn"]->query($sql); 
    $GLOBALS["___conn"]->close();  

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Users</title>
</head>
<?php include WEB_PAGE_TO_ROOT ."template/header-name.php" ?>
<style> 
    table { 
        width: 100%;
        margin: 0 auto; 
        font-size: large; 
        border: 2px solid rgb(120, 120, 120); 
    }
    td { 
        background-color: #E4F5D4; 
        border: 2px solid rgb(120, 120, 120); 
    } 

    th, td { 
        font-weight: bold; 
        border: 2px dotted rgb(120, 120, 120); 
        padding: 10px; 
        text-align: center; 
    } 
    td { 
        font-weight: lighter; 
    } 
</style> 

<div style="margin-top:100px;">
<form style="width: 60%;">
    <h3 class="heading">Registered Users</h3> <br>
    <section>
        <table> 
            <tr> 
                <th>Username</th> 
                <th>Name</th> 
                <th>Email</th> 
                <th>Address</th> 
            </tr> 
            <?php 
                while($rows=$result->fetch_assoc()) 
                { 
             ?> 
            <tr> 
                <td><?php echo $rows['user'];?></td> 
                <td><?php echo $rows['name'];?></td> 
                <td><?php echo $rows['email'];?></td> 
                <td><?php echo $rows['address'];?></td> 
            </tr> 
            <?php 
                } 
             ?> 
        </table> 
    </section> 
    <br><br>
    <?php for($page = 1; $page<= $number_of_page; $page++) {  ?>
    <?php    echo '<a class="register" href = "view-users.php?page=' . $page . '">' . $page . ' </a>'; ?>  
    <?php } ?> 
     
    <br><br><br>
    <a href="index" class="register">Back</a>
</form>
</div>


</html>