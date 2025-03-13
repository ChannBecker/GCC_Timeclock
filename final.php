<?php
//session_start();
require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
require_once("include/fnc_timeclock.php");
require_once('HTMLMail/htmlMimeMail.php3');

	// Get varibles out of the session data
	$mode = (isset($_SESSION['mode']) ? $_SESSION['mode'] : 0);
	$emp_number = (isset($_SESSION['emp_number']) ? $_SESSION['emp_number'] : 0);
	$emp_pin = (isset($_SESSION['emp_pin']) ? $_SESSION['emp_pin'] : 0);
	$emp_id = (isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : 0);
	
	$driver_date = (isset($_POST['driver_date']) ? $_POST['driver_date'] : date('Y-m-d'));
	$driver_hours = (isset($_POST['driver_hours']) ? $_POST['driver_hours'] : 0);
	$driver_minutes = (isset($_POST['driver_minutes']) ? $_POST['driver_minutes'] : 0);
	$driver_miles = (isset($_POST['driver_miles']) ? $_POST['driver_miles'] : 0);
	$driver_note = (isset($_POST['driver_note']) ? $_POST['driver_note'] : "");
	$driver_lunch = (isset($_POST['driver_lunch']) ? $_POST['driver_lunch'] : 0);
	$hire_hours = (isset($_POST['hire_hours']) ? $_POST['hire_hours'] : 0);
	$dclock = "X";

	// figure out MPh, driver_miles / driver_hours, if > 59, email OPS, Jerry, Jim
	$hrs_worked = ((($driver_hours * 60) + $driver_minutes) * 60);
	$time_dec = time_to_dec($hrs_worked);
	$driver_mph = $driver_miles / $time_dec;
	$lunch_dec = ($driver_lunch == 60 ? 1 : ($driver_lunch == 30 ? .5 : 0));
	
	$darray = array('X'=>'Other','O'=>'On-Demand','R'=>'Routes');

 ?>
<html>
<style type="text/css">
<!--
.t_bold {
	font-weight: bold;
}
body {
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
.fineprint {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #666666;
	text-align: center;
}
-->
</style>
<title>Timeclock:Final</title><body>
<? require_once("fineprint.html"); ?>
<? if ($emp_id <> 0 && ($emp_number <> '' || $emp_number <> 0)):
	$query = "
	insert into human_resources.t_timeclock 
	(emp_id,employee_number,client_ip,rec_datetime,rec_date,rec_time,lunch,miles,driver_note,dclock)
	values (". $emp_id. ",'". $emp_number. "','". $_SERVER['REMOTE_ADDR']. "','". date('Y-m-d H:i:s'). "','". $driver_date. "','". $driver_hours. ":". $driver_minutes. ":00',". $driver_lunch. ",". $driver_miles. ",'". $driver_note. "','$dclock')
	";
	$result = mysql_query($query,$tc_link) or die(mysql_error("There was an error recording your information. Please contact GC &amp; C Logistics immediatly."));
	$count = mysql_affected_rows($tc_link);
	if ($count == 1)
		{
			$new_id = mysql_insert_id($tc_link);
			$query = "SELECT concat(id,'-',unix_timestamp(rec_datetime)) as unique_id FROM human_resources.t_timeclock where id = ". $new_id;
			$result = mysql_query($query,$tc_link) or die(mysql_error());
			$data = mysql_fetch_assoc($result);
			$unique_id = $data['unique_id'];
			
			$aquery = "delete from human_resources.t_timeclock_alert where emp_no = '$emp_number' and alert_date = '$driver_date'";
			$qresult = mysql_query($aquery,$tc_link);
		} // END: count == 1
					

?>
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
  	<tr><td align="center" class="t_bold">CLOCK OUT</td></tr>
  </table>
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
  	<tr>
    	<td align="center" class="">
      	Timeclock information has been submitted to payroll.<br><br>
        For your records, the tracking number for this transaction is<br> <strong><?= $unique_id; ?></strong><br>
      </td>
    </tr>
  </table>
<center>
  <a href='http://timeclock.gccmgt.com'>Time Clock</a>
</center>
<?
	$exclude = false;
	if ($emp_number == 1477) $exclude = true;
	
	if ($exclude == false): // tag: 87, exclusions, if exclude is true, skip checks.
		
		$mph_log = 0;
		
		$lunch_log = 0;
		if ($driver_lunch == 0 and $driver_hours >= 6):
			if ($key_1 != 1477): // Exclude driver 1477 from timeclock lunch errors, per Jerry on 9/17/09
			$lunch_log = 1;
			$lunchq = "insert into human_resources.t_timeclock_lunch(un_id,driver,dts_lunch,driver_hours,emailed) values ('$unique_id','$emp_number','". date('Y-m-d H:i:s'). "','". $driver_hours. ":". $driver_minutes. ":00',0)";
			$lunchr = mysql_query($lunchq,$tc_link) or die(mysql_error());
			endif;
			
	endif; // END: lunch = 0 hours >= 6
	
	$ot_log = 0;
	
	if ($hourly != 0):
		$min_worked = intval(($driver_hours * 60)) + intval($driver_minutes) - intval($driver_lunch);
		if ($min_worked >= 481):
			$ot_log = 1;
			$otq = "insert into human_resources.t_timeclock_ot (un_id,driver,dts_ot,driver_hours,emailed,driver_lunch) values ('$unique_id','$emp_number','". date('Y-m-d H:i:s'). "','". $driver_hours. ":". $driver_minutes. ":00',0,$driver_lunch)";
			$otr = mysql_query($otq,$tc_link) or die(mysql_error('ot'));
		endif;
	endif; // END: hourly != 0
	
	$under_log = 0;

	
	$message = '';
	$alert_msg = array();
	if ($mph_log == 1):
		$alert_msg[] = "MPH Alert";
	endif;
	if ($lunch_log == 1):
		$alert_msg[] = "Lunch Alert";
	endif;
	if ($ot_log == 1):
		$alert_msg[] = "OT Alert";
	endif;
	if ($under_log == 1):
		$alert_msg[] = "Under Reporting Alert";
	endif;
	
	if (count($alert_msg) > 0):
		$message = implode(" | ",$alert_msg);
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td align="center"><span style="color:#F00;"><br><?= $message; ?><br><?= (count($alert_msg) == 1 ? 'Has' : 'Have'); ?> been sent to operations.</span></td></tr>
</table>
<?
	endif;
	
	if (date('Y-m-d H:i:s') > '2009-11-12 17:00:00'):
	$query = "select id from human_resources.t_packet where EmployeeNumber = '$emp_number'";
		 $result = mysql_query($query,$tc_link);
		 $count = mysql_num_rows($result);
		 if ($count == 0):
	?>
	<br>
	<form action="final_post.php" method="post" name="get_data">
		<input name="EmployeeNumber" type="hidden" value="<?= $emp_number; ?>">
		<input name="EmployeeName" type="hidden" value="<?= $_SESSION['dname']; ?>">
		<input name="unique_id" type="hidden" value="<?= $unique_id; ?>">
		
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr><td align="center" class="t_bold">
			I, <?= $_SESSION['dname']; ?>, acknowledge receipt of the package that includes a Company Memorandum, Flu Information and Prevention Supplies that were distributed on Thursday, November 12<sup>th</sup>, 2009
		</td></tr>
		<tr><td align="center">Enter your birth year (YYYY): <input type="text" name="Validation" maxlength="4" size="6">&nbsp;<input name="submit" type="submit" value="Acknowledge"></td></tr>
	</table>
	</form>
	<? endif; // Query
	endif;
	session_destroy();
	 endif; // tag: 87, exclude == false ?>
<? else: // emp_id <> 0 and employee_number <> '' ?>
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
  	<tr><td align="center" class="t_bold">CLOCK OUT</td></tr>
  </table>
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
  	<tr>
    	<td align="center" class="">
      	Timeclock information for this session has already been recorded.<br>
        If you need to enter another timeclock entry, please select Time Clock link below.<br>
      </td>
    </tr>
  </table>
<center>
a href='http://timeclock.gccmgt.com'>Time Clock</a>
</center>
<? endif; // emp_id <> 0 and employee_number <> '' ?>
</body>
</html>