<?php
define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';
    
DatabaseConnect();

    $errors = array('pnr' => '');
    $pnr = '';
    if(isset($_POST['submit']) && isset($_POST['pnr'])){
        checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'view-ticket.php' );

        $pnr = $_POST['pnr'];
        $pnr = trim( $pnr );
        $pnr = stripslashes( $pnr );
        $pnr = htmlspecialchars( $pnr );

        if (strlen($pnr) !== 12) {
            $errors['pnr'] = "Invalid input.";
        } else {
             //CHECK VALID PNR
            $data = $db->prepare( 'SELECT pnr_no FROM ticket WHERE pnr_no = (:pnr) LIMIT 1;' );
            $data->bindParam( ':pnr', $pnr, PDO::PARAM_STR );
            $data->execute();
            $row = $data->fetch();

            if( $data->rowCount() == 1 ){
                $_SESSION['view_pnr'] = $row['pnr_no'];
                Redirect('view-pnr-details.php');
            } else {
                $errors['pnr'] = "No Data Found";
            }

        }
    }
generateSessionToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Ticket</title>
</head>
<?php 
    include "template/header.php";
?>


<div style="margin-top:200px;">
    <form style="padding:50px;" action="view-ticket.php" method=POST>
        <label>
            <h3 class = "heading">ENTER YOUR PNR NUMBER</h3>
            <input type="text" class="input" name="pnr" id="pnr" pattern=".{12}" minlength="12" maxlength="12" required oninput="validateSearchBox(this)" value="<?php echo htmlspecialchars($pnr) ?>"> 
            <input type="hidden" name="user_token" value="<?php echo $_SESSION[ 'session_token' ]?>" />
            <div class="line-box">
            <div class="line"></div>
            </div>
            <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['pnr'])?></p>
        </label>
        <button type="submit" name="submit" value="submit">View Ticket</button>
    </form>
</div>
<script>
    function validateSearchBox(input) {
        if (input.value.length !== 12) {
            input.setCustomValidity("Input must be exactly 12 characters long.");
        } else {
            input.setCustomValidity("");
        }
    }
</script>

</html>