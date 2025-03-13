<?php
	if (session_status() == PHP_SESSION_NONE) { session_start(); }
	require_once('Lib/detectMobile.php3');
	$ajaxHandler = explode("/",substr($_SERVER['SCRIPT_NAME'],0,-4). "_ajax.php");
	$ajaxHandler = $ajaxHandler[count($ajaxHandler) - 1];
	echo "<script type='text/javascript'>var ajaxHandler='". $ajaxHandler. "';</script>";
?>