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

    // FOR PAGINATION
    $sql = "SELECT * FROM ticket";
    include WEB_PAGE_TO_ROOT ."template/pagination.php";
    $sql = "SELECT * FROM ticket WHERE booked_by = '".$_SESSION['username']."' LIMIT " . $page_first_result . ',' . $results_per_page;  
    $result = $GLOBALS["___conn"]->query($sql); 
    $GLOBALS["___conn"]->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bookings</title>
</head>
<?php include WEB_PAGE_TO_ROOT ."template/header-name.php" ?>
<style> 
    table { 
        width: 90%;
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
        text-align: left; 
    } 
    td { 
        font-weight: lighter; 
    } 
</style>
<div style="margin-top:200px;">
    <form style = "width: 80%;">
        <h3 class ="heading">Your Previous Booking Details</h3><br>
        <section>
            <table> 
                <tr> 
                    <th>PNR No</th> 
                    <th>Train Number</th> 
                    <th>Date</th> 
                    <th>Coach</th> 
                    <th>Booked At</th> 
                </tr> 
                <?php 
                    while($rows=$result->fetch_assoc()) 
                    { 
                ?> 
                <tr> 
                    <td><h5><?php echo $rows['pnr_no'];?></h5></td> 
                    <td><h5><?php echo $rows['t_number'];?></h5></td> 
                    <td><h5><?php echo $rows['t_date'];?></h5></td> 
                    <td><h5><?php echo $rows['coach'];?></h5></td>
                    <td><h5><?php echo $rows['booked_at'];?></h5></td>  
                </tr> 
                <?php 
                    } 
                ?> 
            </table> 
        </section> 
        <br><br>
        <?php for($page = 1; $page<= $number_of_page; $page++) {  ?>
        <?php    echo '<a class="register" href = "view-user-booking.php?page=' . $page . '">' . $page . ' </a>'; ?>  
        <?php } ?> 

        <br><br><br>
        <a href="index" class= "register">Back</a>

    </form>
</div>


</html>