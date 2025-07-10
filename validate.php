<?php
	//session_start();
	//require_once('Lib/ProgramHeader.php3');
//ini_set('display_errors','on');
require_once('Lib/detectMobile.php3');
DEFINE("TC_NOTICE_LOG",false);

require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
	
$DB = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
$DB->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);

$password = ( isset($_SESSION['var2']) ? $_SESSION['var2'] : null );

$username = ( isset($_SESSION['var1']) ? $_SESSION['var1'] : null );

//$username = (isset($_POST['var1']) ? $_POST['var1'] : null);
//$password = (isset($_POST['var2']) ? $_POST['var2'] : null);

$_SESSION['STC_Valid'] = false;

    //die("<pre>". print_R($_POST,1). "</pre>");
  
	
	$query = "
		SELECT
			fname, lname, id employeeID, hire_hours, hire_miles, mobileAllowed mobile_timeclock, timeClockType, position, category, driver
		FROM employee_data
		WHERE 1=1
			AND employee_number = :employee_number
			AND right(ssn,4) = :password
			AND active <> 0
	";

try {
	$stm = $DB->prepare($query);
	$stm->bindParam(":employee_number",$username);
	$stm->bindParam(":password", $password);
	$result = $stm->execute();
	$data = $stm->fetchall(pdo::FETCH_ASSOC);


} catch (PDOException $e) {
/*
	if ($username == '2148') {
	echo "<pre>". print_r($e,1). "</pre>";
	}
	die(__LINE__. " :: FATAL ERROR");
*/
}


if (!empty($data) && $data[0]['timeClockType'] == 2) {
	$_SESSION['tc_fromV1'] = true;
	$_SESSION['tc_var1'] = $username;
	$_SESSION['tc_var2'] = $password;

    //die("<pre>". print_r($_SESSION,1). "</pre>");
	header('location:./tcDriver/validate.php');
	die('');
}

if (count($data) == 1) {
	$_SESSION['STC_Valid'] = true;
	$_SESSION['STC_fname'] = $data[0]['fname'];
	$_SESSION['STC_lname'] = $data[0]['lname'];
	$_SESSION['STC_employeeNumber'] = $username;
	$_SESSION['STC_employeeID'] = $data[0]['employeeID'];
	$_SESSION['STC_hire_hours'] = (!empty($data[0]['hire_hours']) ? $data[0]['hire_hours'] : 0);
	$_SESSION['STC_hire_miles'] = $data[0]['hire_miles'];
	$_SESSION['STC_mobile_timeclock'] = $data[0]['mobile_timeclock'];
	$_SESSION['STC_inClientIP'] = $_SERVER['REMOTE_ADDR'];
	$_SESSION['STC_position']   = $data[0]['position'];
	$_SESSION['STC_category']   = $data[0]['category'];
    $_SESSION['STC_driver']     = $data[0]['driver'];
  


	// Figure out what type of clock: IN or OUT
	$query = "
		SELECT 
			*
		FROM timeclock
		WHERE
			employeeNumber = :employeeNumber 

		ORDER BY id DESC
	";
	
	try {
		$st = $DB->prepare($query);
		$st->bindParam(":employeeNumber",$username);
		$st->execute();
		$clock = $st->fetch(pdo::FETCH_ASSOC);
	} catch (PDOException $e) {
		echo "<pre>". print_r($e,1). "</pre>";
		die(__LINE__. " :: FATAL ERROR");
	}
	
	if (!empty($clock['inDateTime'])  && !empty($clock['outDateTime'])) {
		// Retrieved record has IN and OUT data, no partial record in the system so this must by a IN
		$_SESSION['STC_clockType'] = "I";
		$_SESSION['STC_dataID']    = null;
	} else if (!empty($clock['inDateTime']) && empty($clock['outDateTime'])) {
		$_SESSION['STC_clockType'] = "O";
		$_SESSION['STC_dataID']    = $clock['id'];
	} else if (empty($clock)) {
		$_SESSION['STC_clockType'] = "I";
		$_SESSION['STC_dataID']    = $clock['id'];
	}
    //die('died: '. __LINE__. " ". $_SERVER['REMOTE_ADDR']. "<br>");				
	// This needs to contain a list of VALID networks to connect from.
	if (  $data[0]['mobile_timeclock'] == 0 && 
        substr($_SERVER['REMOTE_ADDR'],0,7) != '192.168' &&       // Local GC &amp; C Logistics Subnet
        substr($_SERVER['REMOTE_ADDR'],0,5) != '10.1.' &&         // Someother local subnet
        substr($_SERVER['REMOTE_ADDR'],0,9) != '71.238.68' &&     // Verizon Subnet
        substr($_SERVER['REMOTE_ADDR'],0,9) != '10.0.200.')       // ZoAn Management Net
     {
    

		$_SESSION['STC_Valid'] = false;
		$_SESSION['STC_fname'] = null;
		$_SESSION['STC_lname'] = null;
		$_SESSION['STC_employeeNumber'] = null;
		$_SESSION['STC_employeeID'] = null;
		$_SESSION['STC_Code'] = 1;
	} else {
		header("location:clock.php");
	}

	
} else {
	$_SESSION['STC_Valid'] = false;
	$_SESSION['STC_fname'] = null;
	$_SESSION['STC_lname'] = null;
	$_SESSION['STC_employeeNumber'] = null;
	$_SESSION['STC_employeeID'] = null;
	$_SESSION['STC_Code'] = 0;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Invalid Login</title>
<style type="text/css">
	#notice {
		font-size: xx-large;
		text-shadow: 0px 0px;
		color: #FF0004;
	}
</style>
<?php if (MOBILE == true) { // tag: 89 ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.css" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.js"></script>
<?		} // tag; 89 ?>
<?php if (MOBILE == false) { // tag: 13?>
	<link rel="stylesheet" href="http://cdn.gccmgt.com/j_query/1.10.2/themes/black-tie/jquery-ui.css" />
	<link href="css/default.css" rel="stylesheet" type="text/css">
	<?php /* <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> */ ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<?php	} // tag: 13 ?>
</head>
<body>
<?php if (MOBILE == true) { // tag: 104 ?>
<div data-role="page" id="index">
		<div id="header-fineprint" data-role="header">
<?php include("fineprint.html"); ?>
		</div>
  <div data-role="main" class="ui-content">
		<div id="notice" align="center">
<?php if ($_SESSION['STC_Code'] == 0) { ?>
		The ID/PIN combination you entered is not valid.<br>Press <button onClick="location='index.php';">HERE</button> to try again.
<?php } else { ?>
		You are not authorized to access the timeclock outside of the GC &amp; C Logistics building. Please access the timeclock from a workstation within the GC &amp; C Logistics building.
<?php } ?>
		<?php $_SESSION['STC_BadAttempt'] += 1; ?>
		</div>
	</div>
	<div id="footer">
TIMECLOCK &copy; <?= date('Y'); ?> GC &amp; C Logistics
	</div>
	</div>
<? } // tag: 104 ?>
<?php if (MOBILE == false) { // tag: 104 ?>
<div data-role="page" id="index">
		<div id="header-fineprint" data-role="header">
<?php include("fineprint.html"); ?>
		</div>
  <div data-role="main" class="ui-content">
		<div id="notice" align="center">
		The ID/PIN combination you entered is not valid.<br>Press <button onClick="location='index.php';">HERE</button> to try again.
		<?php $_SESSION['STC_BadAttempt'] += 1; ?>
		</div>
	</div>
	<div id="footer">
TIMECLOCK &copy; <?= date('Y'); ?> GC &amp; C Logistics
	</div>
	</div>
<? } // tag: 104 ?>
</body>
</html>