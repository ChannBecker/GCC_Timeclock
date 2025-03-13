<?php
//session_start();
DEFINE("TC_NOTICE_LOG",false);
//require_once('Lib/ProgramHeader.php3');
require_once('Lib/functions.php3');
require_once('Utility/HTMLMail/htmlMimeMail.php3');
require_once('gccl/lib/class_EmailList_v5.php3');
require_once('Senvoy/class_SenvoyFleet.php3');
require_once('GCCL/HumanResources/class_Human_Resources.php3');
require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
require_once('GCCL/HumanResources/class_timeclock.php3');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'Utility/phpmailer/Exception.php'; // Adjust the path
require 'Utility/phpmailer/PHPMailer.php'; // Adjust the path
require 'Utility/phpmailer/SMTP.php'; // Adjust the path

//ini_set("display_errors","on");

$DB = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
$DB->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC); 

$HR = new SENVOY_Human_Resources();

$oTC = new senvoy_timeclock();

$sFL = new SenvoyFleet();

// Define Functions


// Load Email List
  $EML = new EmailList();
  $alertMail = $EML->GetAddresses('TCOTAlert');
  //$alertMail = $EML->GetAddresses('testtest');

   $adminMail = array(
    'cbecker@gccmgt.com'
   );
   
   //$alertMail = $adminMail;
	
	$mode = (isset($_GET['mode']) ? $_GET['mode'] : 0);
	
	if ($_SESSION['STC_Valid'] == false && $mode != 3) {
		$_SESSION['STC_Valid'] = false;
		$_SESSION['STC_lname'] = null;
		$_SESSION['STC_fname'] = null;
		$_SESSION['STC_BadAttempt'] += 1;
		
		header("location:index.php");
	}
  //Overtime Body
  $OTBody = "
<div class=WordSection1>

<p class=MsoNormal align=center style='text-align:center'><b><span
style='font-size:28.0pt;line-height:106%'>Overtime Alert</span></b></p>

<p class=MsoNormal><span style='font-size:14.0pt;line-height:106%'>Employee
{empNum}, {fName} {lName} has just recorded {hours} worked. Reason for going
over was, <b>{reason}</b>.</span></p>

<p class=MsoNormal><span style='font-size:14.0pt;line-height:106%'>Their HR
records indicate that they are scheduled for {sched} hours.</span></p>

<p class=MsoNormal><span style='font-size:14.0pt;line-height:106%'>Punch IN:</span></p>

<table class=MsoTable15List6ColorfulAccent6 border=1 cellspacing=0
 cellpadding=0 style='border-collapse:collapse;border:none'>
 <tr>
  <td width=72 valign=top style='width:53.75pt;border-top:solid #70AD47 1.0pt;
  border-left:none;border-bottom:solid #70AD47 1.0pt;border-right:none;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:14.0pt;color:#538135'>Host</span></b></p>
  </td>
  <td width=504 valign=top style='width:377.75pt;border-top:solid #70AD47 1.0pt;
  border-left:none;border-bottom:solid #70AD47 1.0pt;border-right:none;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:14.0pt;color:#538135'>{inIP}</span></p>
  </td>
 </tr>
 <tr>
  <td width=72 valign=top style='width:53.75pt;border:none;border-bottom:solid #70AD47 1.0pt;
  background:#E2EFD9;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:14.0pt;color:#538135'>TIME</span></b></p>
  </td>
  <td width=504 valign=top style='width:377.75pt;border:none;border-bottom:
  solid #70AD47 1.0pt;background:#E2EFD9;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:14.0pt;color:#538135'>{inDateTime}</span></p>
  </td>
 </tr>
</table>

<p class=MsoNormal style='margin-left:.5in'><span style='font-size:14.0pt;
line-height:106%'>&nbsp;</span></p>

<p class=MsoNormal><span style='font-size:14.0pt;line-height:106%'>Punch OUT:</span></p>

<table class=MsoTable15Grid6ColorfulAccent6 border=1 cellspacing=0
 cellpadding=0 style='border-collapse:collapse;border:none'>
 <tr>
  <td width=72 valign=top style='width:53.75pt;border:solid #A8D08D 1.0pt;
  border-bottom:solid #A8D08D 1.5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:14.0pt;color:#538135'>Host</span></b></p>
  </td>
  <td width=504 valign=top style='width:377.75pt;border-top:solid #A8D08D 1.0pt;
  border-left:none;border-bottom:solid #A8D08D 1.5pt;border-right:solid #A8D08D 1.0pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:14.0pt;color:#538135'>{outIP}</span></p>
  </td>
 </tr>
 <tr>
  <td width=72 valign=top style='width:53.75pt;border:solid #A8D08D 1.0pt;
  border-top:none;background:#E2EFD9;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:14.0pt;color:#538135'>Time</span></b></p>
  </td>
  <td width=504 valign=top style='width:377.75pt;border-top:none;border-left:
  none;border-bottom:solid #A8D08D 1.0pt;border-right:solid #A8D08D 1.0pt;
  background:#E2EFD9;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:14.0pt;color:#538135'>{outDateTime}</span></p>
  </td>
 </tr>
 <tr>
  <td width=72 valign=top style='width:53.75pt;border:solid #A8D08D 1.0pt;
  border-top:none;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:14.0pt;color:#538135'>Lunch</span></b></p>
  </td>
  <td width=504 valign=top style='width:377.75pt;border-top:none;border-left:
  none;border-bottom:solid #A8D08D 1.0pt;border-right:solid #A8D08D 1.0pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:14.0pt;color:#538135'>{lunch}</span></p>
  </td>
 </tr>
 <tr>
  <td width=72 valign=top style='width:53.75pt;border:solid #A8D08D 1.0pt;
  border-top:none;background:#E2EFD9;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:14.0pt;color:#538135'>Miles</span></b></p>
  </td>
  <td width=504 valign=top style='width:377.75pt;border-top:none;border-left:
  none;border-bottom:solid #A8D08D 1.0pt;border-right:solid #A8D08D 1.0pt;
  background:#E2EFD9;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:14.0pt;color:#538135'>{miles}</span></p>
  </td>
 </tr>
 <tr>
  <td width=72 valign=top style='width:53.75pt;border:solid #A8D08D 1.0pt;
  border-top:none;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:14.0pt;color:#538135'>Note</span></b></p>
  </td>
  <td width=504 valign=top style='width:377.75pt;border-top:none;border-left:
  none;border-bottom:solid #A8D08D 1.0pt;border-right:solid #A8D08D 1.0pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:14.0pt;color:#538135'>{note}</span></p>
  </td>
 </tr>
 <tr>
  <td width=72 valign=top style='width:53.75pt;border:solid #A8D08D 1.0pt;
  border-top:none;background:#E2EFD9;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:14.0pt;color:#538135'>Reason</span></b></p>
  </td>
  <td width=504 valign=top style='width:377.75pt;border-top:none;border-left:
  none;border-bottom:solid #A8D08D 1.0pt;border-right:solid #A8D08D 1.0pt;
  background:#E2EFD9;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:14.0pt;color:#538135'>{reason}</span></p>
  </td>
 </tr>
</table>

<p class=MsoNormal style='margin-left:.5in'>&nbsp;</p>

</div>

SESSION:<br>". 
		"isOver:  ". $_SESSION['STC_isOver']. "<BR>". 
		"timeMax: ". $_SESSION['STC_dbtimeMax']. "<BR>".
		"subTime: ". $_SESSION['STC_dbsubTime']. "<BR>".
"END SESSION<BR>";

	if ($mode == 0) { // tag: 23, mode 0, default
		// Get data record
		$id = $_SESSION['STC_dataID'];
		$query = "SELECT * from timeclock where id = :id";
		try {
			$stm = $DB->prepare($query);
			$stm->bindParam(":id",$id);
			$stm->execute();
			$data = $stm->fetch(pdo::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "<pre>". print_r($e,1). "</pre>";
			die(__LINE__. " :: FATAL ERROR");
		}

		$_SESSION['STC_inDate']   = $inDate  = new DateTime($data['inDateTime']);
		$_SESSION['STC_outDate']  = $outDate = new DateTime($_POST['outDate']. " ". $_POST['outTime']);
		$_SESSION['STC_lunch']   = $lunch   = (isset($_POST['lunch'])   ? $_POST['lunch']   : null);
		$_SESSION['STC_totalHours'] = $totalHours = $_POST['totalHours'];
		$_SESSION['STC_miles']   = $miles   = (isset($_POST['miles'])   ? $_POST['miles']   : null);
    $_SESSION['STC_vehicleID'] = $vehicleID = (isset($_POST['vehicle']) ? $_POST['vehicle'] : null);
		$_SESSION['STC_Note']    = $Note    = (isset($_POST['Note'])    ? $_POST['Note']    : null);
		$_SESSION['STC_reason']  = $reason  = (isset($_POST['reason'])  ? $_POST['reason']  : null);
		$_SESSION['STC_isOver']  = $isOver  = (isset($_POST['isOver'])  ? $_POST['isOver']  : 0);
		$_SESSION['STC_dbtimeMax'] = (isset($_POST['dbtimeMax']) ? $_POST['dbtimeMax'] : "NS");
		$_SESSION['STC_dbsubTime'] = (isset($_POST['dbsubTime']) ? $_POST['dbsubTime'] : "NS");
    
/*		echo "M: $miles<br>";
echo "<pre>". print_R($_SESSION,1);
echo "<pre>". print_R($_POST,1);
echo "<pre>". print_R($addResult,1);
die('xx');
		*/
		//echo "Lunch = ". $lunch;
			
		// Options, out > in, time worked = out - in
		// if out is less than in, out must be on the next day. Need in to midnight plus midnight to out.
		
	} // tag: 23, mode 0, default
	
	if ($mode == 2) { // tag: 47, mode 2, write

    $employeeID = $_SESSION['STC_employeeID']; 
		$dataID          = $_SESSION['STC_dataID'];
		$updateTimestamp = date('Y-m-d H:i:s');
		$outClientIP     = $_SERVER['REMOTE_ADDR'];
		$outDateTimeTemp = $_SESSION['STC_outDate'];
		$outDateTime     = $outDateTimeTemp->format('Y-m-d H:i:00');
        $inDateTimeTemp  = $_SESSION['STC_inDate'];
        $inDateTime      = $inDateTimeTemp->format('Y-m-d H:i:00');
		$lunch           = $_SESSION['STC_lunch'];
		$miles           = $_SESSION['STC_miles'];
    $vehicleID       = $_SESSION['STC_vehicleID'];
		$Note            = $_SESSION['STC_Note'];
		$reason          = $_SESSION['STC_reason'];
		$isOver          = $_SESSION['STC_isOver'];
    $userAgent       = substr($_SERVER['HTTP_USER_AGENT'],0,149);
    
    //$addResult = $sFL->addMileageRecord($employeeID,$vehicleID,$outDateTimeTemp->format("Y-m-d"),$miles); 
    
		$query = "
			UPDATE timeclock
			SET updateTimeStamp = :updateTimestamp, outClientIP = :outClientIP, outDateTime = :outDateTime, lunch = :lunch, miles = :miles, Note = :Note, reasonOver = :reason, agent = :userAgent
			WHERE id = :id
		";
		
		try {
			$stm = $DB->prepare($query);
			$stm->bindParam(":updateTimestamp",$updateTimestamp);
			$stm->bindParam(":outClientIP",$outClientIP);
			$stm->bindParam(":outDateTime",$outDateTime);
			$stm->bindParam(":lunch",$lunch);
			$stm->bindParam(":miles",$miles);
			$stm->bindParam(":Note",$Note);
			$stm->bindParam(":id",$dataID);
			$stm->bindParam(":reason",$reason);
      $stm->bindParam(":userAgent",$userAgent);
			
			$stm->execute();
			$last = $outDateTimeTemp->format('U'). "-". $_SESSION['STC_dataID'];
			$lastID = $dataID;
			
			if ($isOver == 1) {
				$emp = $HR->employeeInfoByID($employeeID);
				// Build email
				//echo "<pre>". print_r($_SESSION,1); die();
        
				$body =  "Employee, ". $emp['fname']. " ". $emp['lname']. " (". trim($emp['employee_number']). ") recorded ". $_SESSION['STC_totalHours']. " hours worked.\r\n\r\n";
        $body .= "Their HR Record indicates they are scheduled for only ". $emp['hire_hours']. ".\r\n";
        $body .= "The reason given was: $reason";
        
        // Set the correct message and replace values
        $body = $OTBody;

        //echo "<pre>". print_r($_SESSION,1). "</pre>";
        //echo $inDateTime;
        //die('debug');

        $body = str_replace("{inIP}",$_SESSION['STC_inClientIP'],$body);
        $body = str_replace("{empNum}",$emp['employee_number'],$body);
        $body = str_replace("{fName}",$emp['fname'],$body);
        $body = str_replace("{lName}",$emp['lname'],$body);
        $body = str_replace("{inDateTime}",$inDateTime,$body);
        $body = str_replace("{outIP}",$outClientIP,$body);
        $body = str_replace("{outDateTime}",$outDateTime,$body);
        $body = str_replace("{lunch}",$lunch,$body);
        $body = str_replace("{miles}",$miles,$body);
        $body = str_replace("{note}",$Note,$body);
        $body = str_replace("{reason}",$reason,$body);
        $body = str_replace("{hours}",$_SESSION['STC_totalHours'],$body);
        $body = str_replace("{sched}",$emp['hire_hours'],$body);

/*        
		$mail = new htmlMimeMail5();
		$mail->setTextEncoding(new EightBitEncoding());
		$mail->setHTMLEncoding(new EightBitEncoding());
		$mail->setFrom('Timeclock@senvoy.com');
		$mail->setSubject('Overtime Violation'); //. trim($emp['employee_number']). " - ". $emp['fname']. " ". $emp['lname']);
		$mail->setPriority('high');
		$mail->setText($body);
		$mail->setHTML($body);
		$abc = $oTC->writeOTAlert($dataID);
		//$mail->send($alertMail);
        //$mail->send($adminMail);
*/
    $mail = new PHPMailer(true);
try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Enable verbose debug output
    $mail->isSMTP(); // Send using SMTP
    $mail->Host = 'smtp.office365.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'NoReply@senvoy.com'; // SMTP username
    $mail->Password = 'DoNotReply!'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port = 587; // TCP port to connect to, use 587 for TLS

    //Recipients
    $mail->setFrom('NoReply@senvoy.com', 'Do Not Reply');

    foreach ($alertMail->data as $mk=>$md) {
        echo $md['EMail']. "<br>";
        $mail->addAddress($md['EMail']);
    }
//    $mail->addAddress('bwiggins@gccmgt.com'); // Add a recipient

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'Overtime Violation'; //. trim($emp['employee_number']). " - ". $emp['fname']. " ". $emp['lname'];
    $mail->Body = $body;
    $mail->AltBody = $body;

    $mail->send();
    echo "email sent"; sleep(10);
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

        foreach ($_SESSION as $sk=>$sd) {
          if (substr($sk,0,3) == "STC") {
            unset($_SESSION[$sk]);
          }
        }
        
        $_SESSION['STC_UID'] = $last;
			}
      
//      if ($STC_)
			
			header('location:record.php?mode=3');
      
			
		} catch (PDOException $e) {
			echo "<pre>". print_r($e,1). "</pre>";
			die(__LINE__. " :: FATAL ERROR");
		}
		

	} // tag: 47, mode 2, write
	
	if ($mode == 3) { // tag: 109, mode 3, confirmation message
		$UID = $_SESSION['STC_UID'];
	} // tag: 109, mode 3, confirmation message
	
	if ($mode == 4) { // tag: 166, mode 4, Clock IN
		//echo "<pre>". print_r($_POST,1). "</pre>";
		//echo "<pre>". print_r($_SESSION,1). "</pre>";
		//die('dead');
		
		$insertDateTime = $_POST['inDateTime'];
		$employeeID     = $_SESSION['STC_employeeID'];
		$employeeNumber = $_SESSION['STC_employeeNumber'];
		$inClientIP     = $_SESSION['STC_inClientIP'];
		$inDateTime     = $_POST['inDateTime'];
		
		$query = "
			INSERT INTO timeclock
			(insertTimeStamp,employeeID, employeeNumber, inClientIP, inDateTime)
			values
			(:insertTimeStamp,:employeeID, :employeeNumber, :inClientIP, :inDateTime)
		";
		
try {
	$stm = $DB->prepare($query);
	$stm->bindParam(":insertTimeStamp",$insertDateTime);
	$stm->bindParam(":employeeID",$employeeID);
	$stm->bindParam(":employeeNumber",$employeeNumber);
	$stm->bindParam(":inClientIP",$inClientIP);
	$stm->bindParam(":inDateTime",$inDateTime);
	$stm->execute();
	
	$UID = $DB->lastInsertID();
	
	$_SESSION['STC_UID'] = $UID;
	
	header("location:record.php?mode=5");
	
} catch (PDOException $e) {
	echo "<pre>". print_r($e,1). "</pre>";
	die(__LINE__. " :: FATAL ERROR");
}

		
	} // tag: 166, mode 4, Clock IN
	
	if ($mode == 5) { // tag: 203, mode 5 display IN
		$UID = $_SESSION['STC_UID'];
		
//		if ($_SERVER['REMOTE_ADDR'] == '10.0.200.33') {
//			$query = "select * from  where id = $UID";
//			$stm = $DB->query($query);
//			$data = $stm->fetchAll(pdo::FETCH_ASSOC);
//			
//			echo "mode 5 <br>";
//			echo "<pre>". print_r($data,1). "</pre>";
//			echo "<pre>". print_r($_SESSION,1). "</pre>";
//			die('dead');
//		}
	} // tag: 203, mode 5
  
	
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Review</title>
<style type="text/css">
	#notice {
		font-size: xx-large;
		text-shadow: 0px 0px;
		color: #FF0004;
	}
.ui-grid-a .ui-block-a div {
	padding-left: 3px;
	padding-right: 3px;
}
.ui-grid-a .ui-block-b div {
	padding-left: 3px;
	padding-right: 3px;
}
.label {
	font-weight:bolder;
	padding-left: 3px;
	padding-right: 3px;
}
table tr .label2 {
	font-weight: bolder;
	font-size: xx-large;
	padding-right: 3px;
	padding-left: 3px;
}
table tr .field2 {
	font-weight: bolder;
	font-size: xx-large;
	padding-right: 3px;
	padding-left: 3px;
}
#boxLunch {
	margin-top: 15px;
	margin-bottom: 10px;
}
</style>
	<link rel="stylesheet" href="css/default.css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<!--
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script><script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
-->
	<script src="http://cdn.gccmgt.com/Lib/function.js"></script>
	<script type="text/javascript">
		$().ready(function(e) {
<?php if ($mode == 0) { ?>
			var totalHoursMin = hhmm_to_min('<?= $totalHours; ?>');
			var totalHoursMaxMin = dec_to_min(<?= $_SESSION['STC_hire_hours']; ?>);
			var totalHoursMaxWarning = tooManyHours(totalHoursMin,totalHoursMaxMin);
			
			if (totalHoursMaxWarning == true) {
				showTooManyHoursWarning(totalHoursMin - totalHoursMaxMin);
			}
			
<?php } ?>
<?php if ($mode == 1) { // tag: 222, mode 1 ?>
<?php } // tag: 222, mode 1 ?>
<?php if ($mode == 2) { // tag: 224, mode 2 ?>
<?php } // tag: 224, mode 2 ?>
<?php if ($mode == 3) { ?>
			
<?php } ?>

		});
		
		function showTooManyHoursWarning(overAmount) {
			$("#warning").show();
			$("#reason").show();
		}
		
		function tooManyHours(timeWorked,timeMax) {
			var RetVal = false;
			if (timeWorked > (timeMax + 5)) {
				RetVal = true;
			}
			
			return RetVal;
		}
	</script>
</head>

<body>
<?php if ($mode == 0) { // tag: 148, mode 0 ?>

	<div data-role="page" id="homePage">
		<div id="header-fineprint" data-role="header">
			<?php include("fineprint.html"); ?>
		</div>
		<div id="header">REVIEW INFORMATION</div>
	<form method="post" action="record.php?mode=2">
		<div data-role="main" class="ui-content">
			<table align="center" width="600" border=0 cellpadding="0" cellspacing="0" id="clock">
				<tr><td align="right" class="label2">Date:</td><td align="left" class="field2"><?= $inDate->format('Y-m-d H:i'); ?></td></tr>
				<tr><td align="right" class="label2">Time:</td><td align="left" class="field2"><?= $outDate->format('Y-m-d H:i'); ?></td></tr>
				<tr><td align="right" class="label2">Lunch:</td><td align="left" class="field2"><?= $lunch. " minutes."; ?></td></tr>
				<tr><td align="right" class="label2">Total Hours:</td><td align="left" class="field2"><?= $totalHours; ?></td></tr>
				
				<tr id="warning" style="display:none"><td></td><td align="left"><span id="warningText"></span></td></tr>
				<tr id="reason"  style="display:none"><td align="right" class="label2">Over Reason:</td><td align="left" class="field2"><?= $reason; ?></td></tr>
				
				<tr><td align="right" class="label2">Miles:</td><td align="left" class="field2"><?= $miles; ?></td></tr>
				<tr><td align="right" class="label2">Note:</td><td align="left" class="field2"><?= $Note; ?></td></tr>
				<tr><td>&nbsp;</td></tr>
				<tr><td align="right"><input type="reset" data-theme="c" value="Cancel" onClick="location='clock.php?back=1'">&nbsp;</td><td align="left">&nbsp;<input type="submit" data-theme="b" value="Record"></td></tr>
				<tr><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td></tr>
			</table>
<!--			<div><button type="button" value="click" onClick="xxx();">CLICK</button> -->
		</div>
		</form>
		<div id="footer">
			TIMECLOCK &copy; <?= date('Y'); ?> GC &amp; C Logistics
		</div>
	</div>
<?php } // mode 0 ?>
<?php if ($mode == 3 || $mode == 5) { // tag: 185, mode 0 ?>
	<div >
		<div id="header-fineprint">
			<?php include("fineprint.html"); ?>
		</div>
		<div id="header">CONFIRMATION CODE</div>
		<div class="ui-content" id="Content">
			<div id="warning" align="center" style="font-size:xx-large; font-weight:bolder;">Timeclock information has been submitted to payroll.<br><br>
					For your records, the tracking number for this transaction is
			</div>
			<div id="UID" align="center" style="font-size:xx-large; font-weight:bolder;" >
				<?= date('U'). $UID; ?>
			</div>
			<div id="boxContinue" align="center" style="margin-top:25px;" ><input type="button" data-theme="b" value="Continue" onClick="location='index.php'"></div>
		</div>
			<div id="footer">
				TIMECLOCK &copy; <?= date("Y"); ?> GC &amp; C Logistics
			</div>
	</div>
<?php } // mode 3/5 ?>
</body>
</html>