<?php

if( !defined( 'WEB_PAGE_TO_ROOT' ) ) {
	die( 'System error- WEB_PAGE_TO_ROOT undefined' );
	exit;
}

if (!file_exists(WEB_PAGE_TO_ROOT . 'config/config.inc.php')) {
	die ("System error - config file not found. ");
}

// Include configs
require_once WEB_PAGE_TO_ROOT . 'config/config.inc.php';

// Declare the $html variable
if( !isset( $html ) ) {
	$html = "";
}

// Valid security levels
$security_levels = array('impossible');

if ($security_levels == 'impossible') {
	$httponly = true;
	$samesite = true;
}
else {
	$httponly = false;
	$samesite = false;
}

$maxlifetime = 86400;
$secure = false;
$domain = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);

session_set_cookie_params([
	'lifetime' => $maxlifetime,
	'path' => '/',
	'domain' => $domain,
	'secure' => $secure,
	'httponly' => $httponly,
	'samesite' => $samesite
]);
session_start();

if (!array_key_exists ("default_locale", $_RAIL)) {
	$_RAIL[ 'default_locale' ] = "en";
}

LocaleSet( $_RAIL[ 'default_locale' ] );

// Start session functions --

function &SessionGrab() {
	if( !isset( $_SESSION[ 'rail' ] ) ) {
		$_SESSION[ 'rail' ] = array();
	}
	return $_SESSION[ 'rail' ];
}


function PageStartup( $pActions ) {
	if (in_array('authenticated', $pActions)) {
		if( !IsLoggedIn()) {
			Redirect( WEB_PAGE_TO_ROOT . 'index.php' );
		}
	}
}

function Login( $pUsername ) {
	$Session =& SessionGrab();
	$Session[ 'username' ] = $pUsername;
}


function IsLoggedIn() {
	global $_RAIL;

	if (in_array("disable_authentication", $_RAIL) && $_RAIL['disable_authentication']) {
		return true;
	}
	$Session =& SessionGrab();
	return isset( $Session[ 'username' ] );
}


function Logout() {
	$Session =& SessionGrab();
	unset( $Session[ 'username' ] );
    unset( $Session[ 'user' ] );
	unset( $Session[ 'user_id' ] );
	unset( $Session[ 'user_role' ] );
}


function PageReload() {
	Redirect( $_SERVER[ 'PHP_SELF' ] );
}

function CurrentUser() {
	$Session =& SessionGrab();
	return ( isset( $Session[ 'username' ]) ? $Session[ 'username' ] : 'Unknown') ;
}

// -- END (Session functions

// Token functions --
function checkToken( $user_token, $session_token, $returnURL ) {  # Validate the given (CSRF) token
	global $_RAIL;

	if (in_array("disable_authentication", $_RAIL) && $_RAIL['disable_authentication']) {
		return true;
	}

	if( $user_token !== $session_token || !isset( $session_token ) ) {
		echo( 'CSRF token is incorrect' );
		Redirect( $returnURL );
	}
}

function generateSessionToken() {  # Generate a brand new (CSRF) token
	if( isset( $_SESSION[ 'session_token' ] ) ) {
		destroySessionToken();
	}
	$_SESSION[ 'session_token' ] = md5( uniqid() );
}

function destroySessionToken() {  # Destroy any session with the name 'session_token'
	unset( $_SESSION[ 'session_token' ] );
}

function tokenField() {  # Return a field for the (CSRF) token
	return "<input type='hidden' class='input' name='user_token' value='{$_SESSION[ 'session_token' ]}' />";
}
// -- END (Token functions)

function LocaleSet( $pLocale ) {
	$Session =& SessionGrab();
	$locales = array('en', 'zh');
	if( in_array( $pLocale, $locales) ) {
		$Session[ 'locale' ] = $pLocale;
	} else {
		$Session[ 'locale' ] = 'en';
	}
}

function Redirect( $pLocation ) {
	session_commit();
	header( "Location: {$pLocation}" );
	exit;
}

function DatabaseConnect() {
	global $_RAIL;
	global $DBMS;
	global $db;

	if( $DBMS == 'MySQL' ) {
		if( !@($GLOBALS["___conn"] = mysqli_connect( $_RAIL[ 'db_server' ],  $_RAIL[ 'db_user' ],  $_RAIL[ 'db_password' ], "", $_RAIL[ 'db_port' ] ))
		|| !@((bool)mysqli_query($GLOBALS["___conn"], "USE " . $_RAIL[ 'db_database' ])) ) {
			// die( $DBMS_connError );
			Logout();
			// dvwaMessagePush( 'Unable to connect to the database.<br />' . mysqli_error($GLOBALS["___mysqli_ston"]));
			Redirect('index.php' );
		}
		// MySQL PDO Prepared Statements (for impossible levels)
		$db = new PDO('mysql:host=' . $_RAIL[ 'db_server' ].';dbname=' . $_RAIL[ 'db_database' ].';port=' . $_RAIL['db_port'] . ';charset=utf8', $_RAIL[ 'db_user' ], $_RAIL[ 'db_password' ]);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $GLOBALS["___db"] = $db;
	}
	
	else {
		die ( "Unknown {$DBMS} selected." );
	}

	
}

// -- END (Database Management)

function get_browsers($browser){
	if(strpos($browser, 'MSIE') !== FALSE)
   		$browser='Internet explorer';
 	elseif(strpos($browser, 'Trident') !== FALSE)
    	$browser='Internet explorer';
 	elseif(strpos($browser, 'Firefox') !== FALSE)
   		$browser='Mozilla Firefox';
 	elseif(strpos($browser, 'Chrome') !== FALSE)
   		$browser='Google Chrome';
 	elseif(strpos($browser, 'Opera Mini') !== FALSE)
   		$browser="Opera Mini";
 	elseif(strpos($browser, 'Opera') !== FALSE)
   		$browser="Opera";
 	elseif(strpos($browser, 'Safari') !== FALSE)
   		$browser="Safari";
 	else
   		$browser='Something else';
	return $browser;
}

function get_now_time(){	
	$now=date("Y-m-d H:i:s", time());
	return $now;
}

function action_logs($table, $id, $action, $present_data = ''){
    $login = ($table == "login") ? 1 : 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    $browser = get_browsers($_SERVER['HTTP_USER_AGENT']);
    $date= get_now_time();

    $data = $GLOBALS["___db"]->prepare( 'INSERT INTO site_activity ( user_id, table_name, action_type, login, table_id, date, ip, browser, present_data, previous_data ) VALUES ( :user_id, :table_name, :action_type, :login, :table_id, :date, :ip, :browser, :present_data, :previous_data );' );
    $data->bindParam( ':user_id', $id, PDO::PARAM_STR );
    $data->bindParam( ':table_name', $table, PDO::PARAM_STR );
    $data->bindParam( ':action_type', $action, PDO::PARAM_STR );
    $data->bindParam( ':login', $login, PDO::PARAM_STR );
    $data->bindParam( ':table_id', $id, PDO::PARAM_STR );
    $data->bindParam( ':date', $date, PDO::PARAM_STR );
    $data->bindParam( ':ip', $ip, PDO::PARAM_STR );
    $data->bindParam( ':browser', $browser, PDO::PARAM_STR );
	$previous_data='';
    $data->bindParam(':present_data', $present_data, PDO::PARAM_STR);
    $data->bindParam(':previous_data', $previous_data, PDO::PARAM_STR);
    $data->execute();

	$logs = '('. 'user_id => '. $id . "," .'table_name => '. $table . ",". 'action_type => '. $action . ",". 'login => '. $login . ",". 'table_id => '. $id . ",". 'date => '. $date . ",". 'ip => '. $ip . ",". 'browser => '. $browser .')';
    writeLog(hash('sha256', hash('sha256', $logs)));
    return $data->rowCount() == 1 ? 1 : 0;
    
}

// Function to write logs
function writeLog($message) {
    $logFile =  'logs/logfile.log';
    $timestamp = get_now_time();       
    $logMessage = "[{$timestamp}] {$message}\n";  
    file_put_contents($logFile, $logMessage, FILE_APPEND);  
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}


function checkPermissions($user_id, $permission_id) {    

	try {

		$sql  = 'select
				 count(*) as total_permissions
				 from system_permission_to_roles
				 left join system_users_to_roles
				 on system_permission_to_roles.role_id = system_users_to_roles.role_id
				 where system_users_to_roles.user_id = :user_id
				 and permission_id = :permission_id
				'; 

		 $data = [
				 'user_id'       => $user_id,
				 'permission_id' => $permission_id
				 ];  

		 $stmt = $GLOBALS["___db"]->prepare($sql);
		 $stmt->execute($data);
		 $row  = $stmt->fetch();

		 $authorized = ''; 

		 if ($row['total_permissions'] > 0) {
			 $authorized = "true";

		 } else {
			 $authorized = "false";
		 }

		 return $authorized;

	} catch (Exception $e) {
		echo $e->getMessage();
	}
}


	
?>
