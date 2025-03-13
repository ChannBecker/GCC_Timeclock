<?php
require_once('Lib/detectMobile.php3');
require_once("include/default.php");

if ($_SESSION['tc_valid'] != 1) {
	header("location:../index.php");
}

require_once('Utility/HTMLMail/htmlMimeMail.php3');
//require_once('Senvoy/class.EmailList.php3');
require_once('gccl/lib/class_EmailList_v5.php3');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'Utility/phpmailer/Exception.php'; // Adjust the path
require 'Utility/phpmailer/PHPMailer.php'; // Adjust the path
require 'Utility/phpmailer/SMTP.php'; // Adjust the path

$timeClockType = $_SESSION['tc_timeClockType'];
$EML = new EmailList();

$mailTo = $EML->getAddresses('TCOTAlert');

// Destroy SESSION vars, copy is local memory until script ends.

	foreach ($_SESSION as $k=>$d) {
		if (substr($k,0,3) == 'tc_' || substr($k,0,4) == 'STC_') {
		$$k = $d;
		unset($_SESSION[$k]);
		}
	}

	if ($tc_insertID > 0) {
		$tracking = $tc_insertID. "-". time();
		$tcMessage = "Timeclock entry has been recorded!";
	}


	// process any alerts/warnings
	// Alert: Over Hours Alert
	// Alert: Over MPH Alert
	// Alert: Lunch not taken
	// Alert: Miles

	$alertMessages  = array();
	$alertMessages2 = array();
	$alertSubject   = "Timeclock Alert: ";

	// Check Over Hours

//	if ($tc_hoursAlert == 1) {
		if ($tc_hoursWorked > $tc_hire_hours) {
			$alertSubject .= "Hours, ";
			$alertMessages[] = "Too many hours entered for ". $tc_dateWorked. " by driver.";
			$alertMessages[] = "Driver entered ". $tc_hoursWorked. " when they are maxed at ". $tc_hire_hours;
			$alertMessages2[] = "Driver entered ". $tc_hoursWorked. " when hours are noted as ". $tc_hire_hours;
		}
//	}

	// Check Lunch

	if ($tc_lunchAlert == 1) {
		if ($tc_lunchDuration == 0) {
			$alertSubject .= "Lunch, ";
			$alertMessages[]  = "Driver did not record any lunch.";
			$alertMessages2[] = "Driver did not record any LUNCH.";
		}
	}


	// Check MPH
	// Add to GC &amp; C Logistics Stats...

	if ($tc_MPHAlert == 1) {
		$MPH = 0;
		if ($tc_miles > 0) {
			$MPH = $tc_miles / $tc_hoursWorked;

			$MPH = intval($MPH);
			if ($MPH > 45) {
				$alertSubject .= "MPH, ";
				$alertMessages[]  = "Driver entered a combination of Miles and Time that computes to over 45 MPH. Computed value: ". $MPH;
				$alertMessages2[] = "Driver entered a combination of Miles and Hours that computes to over 45 MPH. Computed value: ". $MPH;
			}
		} 
	}


	// Check Miles

	if ($tc_milesAlert == 1) {
		if ($tc_miles == 0) {
			$alertSubject .= "MILES, ";
			$alertMessages[]  = "Driver did not enter any miles driven.";
			$alertMessages2[] = "Driver did not enter any miles driven.";
		} else if ($tc_miles >= ($tc_hire_miles * 1.25)) {
			$alertSubject .= "MILES, ";
			$alertMessages[]  = "Driver exceeded allocated miles by more than 25%";
			$alertMessages2[] = "Driver entered $tc_miles, HR has $tc_hire_miles noted";
		} 
	}



	if (count($alertMessages2) > 0) {
		$alertSubject = substr($alertSubject,0,-2);
		// Set Message Head
		$message = "Driver: ". $tc_var1. ", ". $tc_fname. " ". $tc_lname. "\r\n\r\n";
		$message2 = "Driver: ". $tc_var1. ", ". $tc_fname. " ". $tc_lname. "\r\n";
		$message2 .= "Position: ". $tc_position. " - ". $tc_category. "\r\n";
		$message2 .= "Date: ". $tc_dateWorked. "\r\n";
		foreach ($alertMessages as $k=>$d) {
			$message .= $d. "\r\n";
		}
		
		foreach ($alertMessages2 as $k=>$d) {
			$message2 .= $d. "\r\n";
		}
		
try {
        $mail = new PHPMailer(true);
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
        //$mail->addAddress('cbecker@senvoy.com'); // Add a recipient
        foreach ($mailTo->data as $mk=>$md) {
            echo $md['EMail']. "<br>";
            $mail->addAddress($md['EMail']);
        }
    
        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $alertSubject;
        $mail->Body = $message2;
        $mail->AltBody = $message2;
    
        // Add an attachment
        $mail->send();
        //echo "Email Sent"; sleep(10);
    } catch (Exception $e) {
        echo "<!-- Message could not be sent. Mailer Error: {$mail->ErrorInfo} -->\r\n";
    }
    
	}
?>
<!doctype html>
<html>
<head>
<?php if (MOBILE == true) { ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
<?php } ?>
<meta charset="utf-8">
<title>GC &amp; C Logistics Timeclock</title>
<style>
	#msgBox {
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		width: 400px;
		height: 200px;
		margin: auto;
		border-radius: 10px;
		border: medium solid #000000;
		background-color: white;
	}

</style>
</head>
<body style="background-color: lightgray">
	<div id="msgBox">
		<div id="content" align="center">
			<table width="300" border="0" cellspacing="0" cellpadding="0">
				<tr><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td></tr>
				<tr>
					<td align="center" style="font-weight: bold; font-size: 18px"><?= $tcMessage; ?></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td align="center" style="font-weight: bold; font-size: 18px">Tracking: <?= $tracking; ?></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td align="center" style="font-weight: bold; font-size: 18px"><a href="../index.php">Continue</a></td>
				</tr>
			</table>
		</div>
	</div>
</body>
</html>
	