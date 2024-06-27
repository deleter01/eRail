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
    $sql = "SELECT * FROM trains ORDER BY t_date DESC";

    include WEB_PAGE_TO_ROOT ."template/pagination.php";  
    
    $sql = "SELECT *FROM trains ORDER BY t_date DESC LIMIT " . $page_first_result . ',' . $results_per_page;  
    $result = $GLOBALS["___conn"]->query($sql); 
    $GLOBALS["___conn"]->close();  
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Trains</title>
</head>

<?php 
    if(isset($_SESSION['username']))
        include WEB_PAGE_TO_ROOT ."template/header-name.php";
    else
        include WEB_PAGE_TO_ROOT ."template/header.php";
?>

<style> 
    table { 
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
<form style="width: 70%;">
    <h3 class="heading">All Released Trains</h3> <br>
    <section>
        <table> 
            <tr> 
                <th>Train Number</th> 
                <th>Journey Date</th> 
                <th>Number Of AC Coaches</th> 
                <th>Number Of Sleeper Coaches </th> 
            </tr> 
            <?php 
                while($rows=$result->fetch_assoc()) 
                { 
             ?> 
            <tr> 
                <td><?php echo $rows['t_number'];?></td> 
                <td><?php echo $rows['t_date'];?></td> 
                <td><?php echo $rows['num_ac'];?></td> 
                <td><?php echo $rows['num_sleeper'];?></td> 
            </tr> 
            <?php 
                } 
             ?> 
        </table> 
    </section> 
    <br><br>

    <?php for($page = 1; $page<= $number_of_page; $page++) {  ?>
    <?php    echo '<a class="register" href = "view-trains.php?page=' . $page . '">' . $page . ' </a>'; ?>  
    <?php } ?> 

    <br><br><br>
    <a href="index" class="register">Back</a>
</form>
</div>


</html>