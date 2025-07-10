<?php
	require_once("include/default.php");
    require_once("include/class.timeclock.php");
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
$p = ( isset($_SESSION['var2']) ? $_SESSION['var2'] : null );
$u = ( isset($_SESSION['var1']) ? $_SESSION['var1'] : null );


}

$tc->initialize($u,$p);

if ($tc->valid == true) {
	header('location:timeclock.php');
} else {
	header('location:../err.php');
}
?>