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

    $errors = array('train_number' => '', 'date' => '', 'num_ac' => '', 'num_sleeper' => '', 'checks' => '', 'error' => '');
    $train_number = $date = $num_ac = $num_sleeper = $checks = $error = ''; 
    $admin_name = $_SESSION['username'];
   
    if(isset($_POST['release']) && isset ($_POST['train_number']) && isset ($_POST['date']) && isset ($_POST['num_ac']) && isset ($_POST['num_sleeper'])){

        // Check Anti-CSRF token
	    checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

        $train_number = $_POST['train_number'];
        $train_number = trim( $train_number );
        $train_number = stripslashes( $train_number );
        $train_number = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $train_number ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if(!preg_match('/^[0-9]+$/', $train_number)){
            $errors['error'] .= 'Train No must consist of Number only';
        }

        $date = $_POST['date'];
        $date = trim($date);
        $date = stripslashes($date);
        $date = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $date ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        // if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        //     $errors['error'] .= 'Date must be in the format YYYY-MM-DD';
        // }

        $num_ac = $_POST['num_ac'];
        $num_ac = trim($num_ac);
        $num_ac = stripslashes($num_ac);
        $num_ac = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $num_ac ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if (!preg_match('/^[0-9]+$/', $num_ac)) {
            $errors['error'] .= 'AC coaches must consist of numbers only';
        }

        $num_sleeper = $_POST['num_sleeper'];
        $num_sleeper = trim($num_sleeper);
        $num_sleeper = stripslashes($num_sleeper);
        $num_sleeper = ((isset($GLOBALS["___conn"]) && is_object($GLOBALS["___conn"])) ? mysqli_real_escape_string($GLOBALS["___conn"],  $num_sleeper ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
        if (!preg_match('/^[0-9]+$/', $num_sleeper)) {
            $errors['error'] .= 'Sleeper coaches must consist of numbers only';
        }

        // Check the database (Check train information)
        $data = $db->prepare( 'SELECT t_number, t_date FROM trains WHERE t_number = (:train_number) AND t_date = (:date) LIMIT 1;' );
        $data->bindParam(':train_number', $train_number, PDO::PARAM_STR);
        $data->bindParam(':date', $date, PDO::PARAM_STR);
        $data->execute();
        $row = $data->fetch();

        if( ( $data->rowCount() == 1 ) && ( $row[ 't_number' ] == $train_number ) && ( $row[ 't_date' ] == $date) )  {
            $errors['error'] = 'This Train Already Released on that day';
        }
 
        if (! array_filter($errors)) {
            
            $data = $db->prepare('INSERT INTO trains (t_number, t_date, num_ac, num_sleeper, released_by) VALUES (:train_number, :date, :num_ac, :num_sleeper, :released)');
            $data->bindParam(':train_number', $train_number, PDO::PARAM_STR);
            $data->bindParam(':date', $date, PDO::PARAM_STR);
            $data->bindParam(':num_ac', $num_ac, PDO::PARAM_INT);
            $data->bindParam(':num_sleeper', $num_sleeper, PDO::PARAM_INT);
            $data->bindParam(':released', $admin_name, PDO::PARAM_INT);
        
            if ($data->execute()) {
                if ($data->rowCount() == 1) {

                    $table='trains';
                    $data = '('. 'train_number => '. $train_number . "," .'date => '. $date . ",". 'num_ac => '. $num_ac . ",". 'num_sleeper => '. $num_sleeper . ",". 'released => '. $admin_name . ')';
                    $presant_data = hash('sha256', hash('sha256', $data));
                    action_logs($table,$_SESSION['user'], "Release a Train",$data);

                    $data = $db->prepare('INSERT INTO trains_status (t_number, t_date, seats_b_ac, seats_b_sleeper) VALUES (:train_number, :date, 0, 0)');
                    $data->bindParam(':train_number', $train_number, PDO::PARAM_STR);
                    $data->bindParam(':date', $date, PDO::PARAM_STR);

                    if ($data->execute()) {
                        if ($data->rowCount() == 1) {

                            $table='trains_status';
                            $data = '('. 'train_number => '. $train_number . "," .'date => '. $date . ')';
                            $presant_data = hash('sha256', hash('sha256', $data));
                            action_logs($table,$_SESSION['user'], "Train Status",$data);
                            
                            $db = NULL;
                            $errors['error'] = 'sucess';
                            $success['success'] .= "Train has been registered.";
                            sleep(12);
                            Redirect('index.php');
                            exit(); 
                        }
                    }

                    
                } else {
                    $table='trains_status';
                    $data = '('. 'train_number => '. $train_number . "," .'date => '. $date . ')';
                    $presant_data = hash('sha256', hash('sha256', $data));
                    action_logs($table,$_SESSION['user'], "Fail to Enter Train Status",$data);
                    $errors['error'] .= "Error inserting train.";
                }
            } else {

                $table='trains';
                $data = '('. 'train_number => '. $train_number . "," .'date => '. $date . ",". 'num_ac => '. $num_ac . ",". 'num_sleeper => '. $num_sleeper . ",". 'released => '. $admin_name . ')';
                $presant_data = hash('sha256', hash('sha256', $data));
                action_logs($table,$_SESSION['user'], "Fail to Release a Train",$data);
                $errors['error'] .= "Error inserting train.";
            }
        }

    }
    $welcome_name = $_SESSION['username'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Release Train</title>
</head>
<?php include WEB_PAGE_TO_ROOT ."template/header-name.php" ?>

<div style="margin-top:100px;">
<form action="<?php  $_SERVER['PHP_SELF'] ?>" method="POST">
    <h3 class="heading">Release New Train</h3> <br>
    <label>
    <p class="label-txt">TRAIN NUMBER</p>
    <input type="number" class="input" min=0 size = "10" name="train_number" value="<?php echo htmlspecialchars($train_number) ?>">
    <div class="line-box">
        <div class="line"></div>
    </div>
    <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['train_number'])?></p>
    </label>
    <label>
    <p class="label-txt">DATE</p>
    <input type="date" class="input" name="date" size = "10" value="<?php echo htmlspecialchars($date) ?>">
    <div class="line-box">
        <div class="line"></div>
    </div>
    <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['date'])?></p>
    </label>
    <label>
    <p class="label-txt">NUMBER OF AC COACHES</p>
    <input type="number" class="input" min=0 name="num_ac" size = "10" value="<?php echo htmlspecialchars($num_ac) ?>">
    <div class="line-box">
        <div class="line"></div>
    </div>
    <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['num_ac'])?></p>
    </label>
    <label>
    <p class="label-txt">NUMBER OF SLEEPER COACHES</p>
    <input type="number" class="input" name="num_sleeper" size = "10" min=0 value="<?php echo htmlspecialchars($num_sleeper) ?>">
    <div class="line-box">
        <div class="line"></div>
    </div>
    <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['num_sleeper'])?></p>
    </label>
    <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['checks'])?></p>
    <p class= "bg-danger text-white"><?php echo htmlspecialchars($errors['error'])?></p>
    <a href="index" class="register">Back</a>
    <button type="submit" name="release" value="submit">Release</button>
    <input type="hidden" class="input" name="user_token" value="<?php echo $_SESSION[ 'session_token' ] ?>" />
</form>
</div>


</html>
