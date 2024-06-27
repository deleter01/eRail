<?php
define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';
    
DatabaseConnect();

    $pnr = $_SESSION['view_pnr'];
    $data = $db->prepare( 'SELECT * FROM passengers WHERE pnr_no = (:pnr);' );
    $data->bindParam( ':pnr', $pnr, PDO::PARAM_STR );
    $data->execute();

    if( $data->rowCount() != 1 ){
        $errors['pnr'] = "No Data Found";
    } 

    // $query1 = "SELECT * FROM passenger WHERE pnr_no = '".$_SESSION['view_pnr']."' ";
    // if($conn->query($query1) == FALSE){
    //     echo $conn->error;
    // }
    // $result1 = $conn->query($query1);

    $sql = $db->prepare( 'SELECT * FROM ticket WHERE pnr_no = (:pnr) LIMIT 1;' );
    $sql->bindParam( ':pnr', $pnr, PDO::PARAM_STR );
    $sql->execute();

    if( $data->rowCount() != 1 ){
        $errors['pnr'] = "No Ticket Found";
    } 


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>PNR Details</title>
</head>
<?php include "template/header.php" ?>
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
<div style="margin-top:100px;">
<form style = "width: 60%;">

    <h5>Booking Details </h5>
    <table>
        <tr>
            <td><h5> PNR NUMBER </h5></td>
            <td><h5><?php echo $_SESSION['view_pnr'] ?></h5></td>
        </tr>
        <?php
            // $row = $result2->fetch_object();
            $row = $sql->fetch(PDO::FETCH_OBJ);
        ?>
        <tr>
            <td><h5> Coach Type</h5></td>
            <td><h5><?php echo $row->coach ?></h5></td>
        </tr>
        <tr>
            <td><h5> Train Number</h5></td>
            <td><h5><?php echo $row->t_number ?></h5></td>
        </tr>
        <tr>
            <td><h5> Date Of Journey</h5></td>
            <td><h5><?php echo $row->t_date ?></h5></td>
        </tr>
        <tr>
            <td><h5> Booked By</h5></td>
            <td><h5><?php echo $row->booked_by ?></h5></td>
        </tr>
    </table><br><br>
    <h5> Passenger Details </h5>
    <section>
        <table> 
            <tr> 
                <th>Name</th> 
                <th>Berth Number</th> 
                <th>Berth Type</th> 
                <th>Coach Number</th> 
            </tr> 
            <?php 
                while ($rows = $data->fetch(PDO::FETCH_OBJ)) {
             ?> 
            <tr> 
                <td><h5><?php echo $rows->name;?></h5></td> 
                <td><h5><?php echo $rows->berth_no;?></h5></td> 
                <td><h5><?php echo $rows->berth_type;?></h5></td> 
                <td><h5><?php echo $rows->coach_no;?></h5></td> 
            </tr> 
            <?php } ?> 
        </table> 
    </section> 
    <br>

    <a href="view-ticket.php" class= "register">Back</a>
    <button onclick="window.print()">Print Ticket</button>
    
</form>
</div>

</html>
