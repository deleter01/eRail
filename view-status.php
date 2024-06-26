<?php
define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';
    
DatabaseConnect();

    $errors = array('number' => '', 'date' => '', 'final' => '');
    $number = $date = '';
    $avail_ac = $avail_sleeper = '';
    if(isset($_POST['submit'])){
        $number = $_POST['number'];
        $number = trim( $number );
        $number = stripslashes( $number );
        $number = htmlspecialchars( $number );
        $number = $GLOBALS["___conn"]->real_escape_string($number);
        (preg_match('/^\d{0,19}$/', $number))? $number : $errors['number'] = 'Train Number is required';

        $date = $_POST['date'];
        $date = $GLOBALS["___conn"]->real_escape_string($date);
        if (!validateDate($date)) {
            $errors['date'] = 'Invalid date format.';
        } else if (strtotime($date) < time()) {
            $errors['date'] = 'Past Date.';
        } 
        
        if(empty($number)){
			$errors['number'] = 'Train Number is required';
        }
        if(empty($date)){
            $errors['date'] = 'Date is required';
        }  

        if(! array_filter($errors)){ 
            //CHECK VALID TRAIN
            $query1 = "SELECT * FROM trains_status WHERE t_number = '$number' AND t_date = '$date'";
            $result = $GLOBALS["___conn"]->query($query1);
            $query2 = "SELECT * FROM trains WHERE t_number = '$number' AND t_date = '$date'";
            $result2 = $GLOBALS["___conn"]->query($query2);
            //IF TUPLE FOUND IN TABLE PRINT STATUS
            if($result->num_rows > 0){
                $row = $result->fetch_object();
                $row1 = $result2->fetch_object();
                if($row1->num_ac == 0){
                    $avail_ac = 0;
                }
                else{
                    $avail_ac = $row1->num_ac*18 - $row->seats_b_ac;
                }
                if($row1->num_sleeper == 0){
                    $avail_sleeper = 0;
                }
                else{
                    $avail_sleeper = $row1->num_sleeper*24 - $row->seats_b_sleeper;
                }
            }
            else{
                $errors['final'] = 'Train has not been released'; 
            }
        }    
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Check Train Status</title>
</head>
<?php include WEB_PAGE_TO_ROOT ."template/header.php" ?>
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
<div style="margin-top:50px;">
    <form style="padding:50px;" action="view-status.php" method=POST>
        <h3 class = "heading">Check Seats Available For Booking</h3>
            <label>
                <p class="label-txt">TRAIN NUMBER</p>
                <input type="number" class="input" name="number" pattern="\d{0,19}" maxlength="19" minlength="7" required value="<?php echo htmlspecialchars($number) ?>" oninput="validatePNR(this)">
                <div class="line-box">
                    <div class="line"></div>
                </div>
                <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['number'])?></p>
            </label>
            <label>
                <p class="label-txt">DATE</p>
                <input type="date" class="input" name="date" required value="<?php echo htmlspecialchars($date) ?>"  oninput="validateDate(this)">
                <div class="line-box">
                    <div class="line"></div>
                </div>
                <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['date'])?></p>
            </label>
            <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['final'])?></p>
  
        <button type="submit" name="submit" value="submit">Check</button>
        <br><br>
        
        <table>
            <tr>
                <td><h5> Seats In AC Coach </h5></td>
                <td><h5><?php echo $avail_ac ?></h5></td>
            </tr>
            <tr>
                <td><h5> Seats In Sleeper Coach</h5></td>
                <td><h5><?php echo $avail_sleeper?></h5></td>
            </tr>
        </table>
        
    </form>
</div>

<script>
    function validatePNR(input) {
        const value = input.value;
            if (!/^\d{0,19}$/.test(value)) {
                input.setCustomValidity("Input must be less than 20 digits.");
            } else {
                input.setCustomValidity("");
            }
    }

    function validateDate(input) {
            const date = new Date(input.value);
            const currentDate = new Date();
            if (isNaN(date.getTime())) {
                input.setCustomValidity("Invalid date format.");
            } else if (date < currentDate) {
                input.setCustomValidity("Past Date.");
            } else {
                input.setCustomValidity("");
            }
        }
</script>


</html>