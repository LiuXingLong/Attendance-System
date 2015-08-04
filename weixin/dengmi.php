<?php
include_once 'GLOBALS.php';

define ( "NO_STATUS", 0 );
define ( "EXIT_ORDER", "exit" );
define("CDM_1_1", 111);
define("CDM_1_2", 112);


$wechatObj = new \Dm\DengmiCallbackapi ();
if (isset ( $_GET ['echostr'] )) {
	$wechatObj->valid ();
} else {
	$wechatObj->responseMsg ();
}
?>