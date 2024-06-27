<?php

    include "config/config.inc.php";
	  
function get_now_time(){	
	$now=date("Y-m-d H:i:s", time());
	return $now;
}

function start_session() {

	$security_level = 'impossible';
	if ($security_level == 'impossible') {
		$httponly = true;
		$samesite = "Strict";
	}
	else {
		$httponly = false;
		$samesite = "";
	}

	$maxlifetime = 86400;
	$secure = false;
	$domain = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);


	if (session_status() == PHP_SESSION_ACTIVE) {
		session_write_close();
	}

	session_set_cookie_params([
		'lifetime' => $maxlifetime,
		'path' => '/',
		'domain' => $domain,
		'secure' => $secure,
		'httponly' => $httponly,
		'samesite' => $samesite
	]);

	
	if ($security_level == 'impossible') {
		session_start();
		session_regenerate_id(); // force a new id to be generated
	}
	else {
		if (isset($_COOKIE[session_name()])) // if a session id already exists
			session_id($_COOKIE[session_name()]); // we keep the same id
		session_start(); // otherwise a new one will be generated here
	}
}

if (array_key_exists ("Login", $_POST) && $_POST['Login'] == "Login") {
	start_session();
} else {
	if (!session_id()) {
		session_start();
	}
}


function DatabaseConnect() {
	global $_RAIL;
	global $DBMS;
	//global $DBMS_connError;
	global $db;
	global $sqlite_db_connection;

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
	}
	
	else {
		die ( "Unknown {$DBMS} selected." );
	}

	
}

// -- END (Database Management)


// Start session functions --

    //local devlopment

     $servername = "localhost";
     $username = "root";
     $password = "";
     $database = "rdb";
    
    $conn = new mysqli($servername, $username, $password, $database);
    if($conn -> connect_error){
        die("Connection failed: " . $conn -> connect_error);
    }

    function Redirect( $pLocation ) {
        session_commit();
        header( "Location: {$pLocation}" );
        exit;
    }


// Token functions --
function checkToken( $user_token, $session_token, $returnURL ) {  # Validate the given (CSRF) token
	global $_RAIL;

	if (in_array("disable_authentication", $_RAIL) && $_RAIL['disable_authentication']) {
		return true;
	}

	if( $user_token !== $session_token || !isset( $session_token ) ) {
		echo( 'CSRF token is incorrect' );
		// Redirect( $returnURL );
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
			Redirect( DVWA_WEB_PAGE_TO_ROOT . 'login.php' );
		}
	}
}

function Login( $pUsername ) {
	$railSession =& SessionGrab();
	$railSession[ 'username' ] = $pUsername;
}


function IsLoggedIn() {
	global $_RAIL;

	if (in_array("disable_authentication", $_RAIL) && $_RAIL['disable_authentication']) {
		return true;
	}
	$railSession =& SessionGrab();
	return isset( $railSession[ 'username' ] );
}


function Logout() {
	$raildvwaSession =& SessionGrab();
	unset( $railSession[ 'username' ] );
}


function PageReload() {
	if  ( array_key_exists( 'HTTP_X_FORWARDED_PREFIX' , $_SERVER )) {
		Redirect( $_SERVER[ 'HTTP_X_FORWARDED_PREFIX' ] . $_SERVER[ 'PHP_SELF' ] );
	}
	else {
		Redirect( $_SERVER[ 'PHP_SELF' ] );
	}
}

function CurrentUser() {
	$railSession =& SessionGrab();
	return ( isset( $raukSession[ 'username' ]) ? $railSession[ 'username' ] : 'Unknown') ;
}

// -- END (Session functions)
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

function action_logs(){
	if($flag==1 && $table!="chat" && $table!="result" && $table!="student_attendence" && $table!="site_activity" && $this->login_user!=""){
		$activity=array();
		$table_id=($action=="insert")?$res:$info['id'];
		$login=($table=="login")?1:0;
		$activity['user_id']=$this->login_user;
		$activity['table_name']=$table;
		$activity['action_type']=$action;
		$activity['login']=$login;
		$activity['table_id']=$table_id;
		$activity['date']=$this->date();
		$activity['ip']=$this->ip; 
		$activity['browser']=$this->browser;
		$activity['present_data']=$present_data;
		$activity['previous_data']=$previous_data;
	
		$this->sql_action("site_activity","insert",$activity,"no");
	  }
}
?>