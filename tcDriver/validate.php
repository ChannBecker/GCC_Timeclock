<?php
    //ini_set('display_errors','on');
    //echo "TCDriver<br>";
    //die("<pre>". print_r($_SESSION,1). "</pre>");

	//ini_set("display_errors","on");
	require_once("include/default.php");
    require_once("include/class.timeclock.php");
    //require_once('GCCL/HumanResources/class_timeclock.php3');

//if  ($_SESSION['tc_valid'] != 1) {
	//header("location:../index.php");
//}

$tc = new timeclock();

//
// Need to take the vars and validate the user against the database
//

if (isset($_SESSION['tc_fromV1']) && $_SESSION['tc_fromV1'] == true) {
	$u = $_SESSION['tc_var1'];
	$p = $_SESSION['tc_var2'];
} else {
$u = (isset($_POST['var1']) ? $_POST['var1'] : null);
$p = (isset($_POST['var2']) ? $_POST['var2'] : null);
}

$tc->initialize($u,$p);
//die("<pre>". print_r($tc,1). "</pre>");

if ($tc->valid == true) {
	//unset($_SESSION['tc_var1']);
	//unset($_SESSION['tc_var2']);
	header('location:timeclock.php');
} else {
    //echo "<pre>". print_r($_SESSION,1). "</pre>";
    //echo "<pre>". print_r($_POST,1). "</pre>";
    //echo "<pre>". print_r($tc,1);
    //die('die');
	header('location:../err.php');
}
?>