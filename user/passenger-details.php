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

    $num_passengers = $_SESSION['num_passengers'];
    $errors = array('validate' => '', 'seats' => '');
    $name = $age = $gender = [];
    for($i = 0; $i < $num_passengers; $i++){
      $name[$i] = $age[$i] = $gender[$i] = '';
    }
   
    if(isset($_POST['check']) && isset($_POST['name']) && isset($_POST['age'])){
  
      $name = $_POST['name'];
      // $name = trim($name);
      // $name = stripslashes($name);
      // $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

      $age = $_POST['age'];
      // $age = trim($age);
      // $age = stripslashes($age);
      // $age = htmlspecialchars($age, ENT_QUOTES, 'UTF-8');

      $gender = $_POST['gender'];
      // $gender = trim($gender);
      // $gender = stripslashes($gender);
      // $gender = htmlspecialchars($gender, ENT_QUOTES, 'UTF-8');

      // Assuming $GLOBALS["___conn"] is your MySQLi connection
      // if (isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) {
      //     $name = mysqli_real_escape_string($GLOBALS["___conn"], $name);
      //     $age = mysqli_real_escape_string($GLOBALS["___conn"], $age);
      //     $gender = mysqli_real_escape_string($GLOBALS["___conn"], $gender);
      // } else {
      //     trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR);
      // }
    
      
      if(in_array('', $name, true) || in_array('', $age, true) || in_array('', $gender, true)){
        $errors['validate'] = 'Please fill details of all the passengers!';
      }

      //IF NO ERRORS THEN CHECK WHETHER SEATS ARE AVAILABLE IN DESIRED COACH
      if(! array_filter($errors)){
        $_SESSION['name'] = $name;  
        $_SESSION['age'] = $age;
        $_SESSION['gender'] = $gender;
        $train_number = $_SESSION['train_number'];
        $date = $_SESSION['date'];
        $coach = $_SESSION['coach'];
        $num_passengers = $_SESSION['num_passengers'];
        $u_name = $_SESSION['username'];
      
      // try {
        // IF AVAILABLE THEN REDIRECT GET TICKET ELSE FAILURE PAGE
        $query1 = "CALL check_seats_availabilty('$train_number', '$date', '$coach', '$num_passengers')";
        if ($GLOBALS["___conn"]->query($query1) === FALSE) {
          $_SESSION['seats_error'] = $GLOBALS["___conn"]->error;
          Redirect('not-available.php');
        }
        else{
          // GENERATE PNR NUMBER & INSERT INTO TICKET
          $query1 = "CALL generate_pnr('".$_SESSION['username']."', @p1, '$coach', '$train_number', '$date'); SELECT @p1 AS pnr_no;";
          if($GLOBALS["___conn"]->multi_query($query1) == FALSE){
            $errors['validate'] = 'Error !';
            // echo $GLOBALS["___conn"]->error;
          }
          $GLOBALS["___conn"]->next_result();
          $result = $GLOBALS["___conn"]->store_result();      
          $pnr_no = $result->fetch_object()->pnr_no;
          $_SESSION['pnr_no'] = $pnr_no;

          // ASSIGN BERTH NO & COACH NO & INSERT INTO PASSENGER
          for($i=0; $i<$num_passengers; $i++){
            $query1 = "CALL assign_berth('$train_number', '$date', '$coach', '$name[$i]', '$age[$i]', '$gender[$i]', '$pnr_no')";
            if ($GLOBALS["___conn"]->query($query1) === FALSE) {
              $errors['validate'] = 'Error !';
              // echo $GLOBALS["___conn"]->error;
            }
          }

          // ASSIGN BERTH NO & COACH NO & INSERT INTO PASSENGER
          // for($i=0; $i<$num_passengers; $i++){
          //   $query1 = assign_berth($train_number, $date, $coach, $name[$i], $age[$i], $gender[$i], $pnr_no);
          //   if ($GLOBALS["___conn"]->query($query1) === FALSE) {
          //     echo $GLOBALS["___conn"]->error;
          //   }
          // }

          Redirect('get-ticket.php');
         
        }

       
          
      // } catch (Exception $e) {
      //     echo "Error: " . $e->getMessage();
      // }

      }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Enter Details</title>
    
</head>
<?php include WEB_PAGE_TO_ROOT ."template/header-name.php" ?>

<div style="margin-top:100px;">
<form method="post" action="passenger-details.php" style="width: 55%;">
  <h3 class="heading"> Enter Details Of Passengers</h3><br>
  <table>
    <tr> 
        <th></th>
        <th>Name</th> 
        <th>Age</th> 
        <th>Gender</th> 
    </tr> 
  <?php for($i = 0; $i < $num_passengers; $i++){ ?>
   <tr>
   <td> Passenger&nbsp<?php echo $i+1 ?>&nbsp&nbsp&nbsp
   </td>
   <td>
	<input type="text" name="name[]" placeholder="Enter name" maxlength="10" size="10" class="input" value = "<?php echo $name[$i] ?>">
	</td>
	<td>
	<input type="number" name="age[]" placeholder="Enter Age" maxlength="3" size="3" class="input" value = "<?php echo $age[$i] ?>">
	</td>
	<td>
	<select name="gender[]" class="input">
    <option value="Female" <?php echo (isset($gender[$i]) && $gender[$i] === 'Female') ? 'selected' : ''; ?>>Female</option>
    <option value="Male" <?php echo (isset($gender[$i]) && $gender[$i] === 'Male') ? 'selected' : ''; ?>>Male</option>
	</select>
	</td>
   </tr>
  <?php } ?>
  </table>
    <br><br>

  <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['validate'])?></p>

  <a href="book-ticket" class="register">Back</a>
  <button type="submit" name="check" value="submit">Check Availability</button>
 </form>
</div>


</html>
