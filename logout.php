<?php
define( 'WEB_PAGE_TO_ROOT', '' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';

PageStartup( array( ) );

if( !IsLoggedIn() ) {	
	Redirect( 'index.php' );
}

Logout();
Redirect( 'index.php' );

?>