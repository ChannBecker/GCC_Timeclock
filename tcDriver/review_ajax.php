<?php
//ini_set("display_errors","on");
//session_start();
$mode = (isset($_GET['mode']) ? $_GET['mode'] : null);

if ($mode == 'trackDisclaimer') {
    require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
	$db = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
	$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
	$db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);
	
	$retvar = array('result'=>false,'data'=>null);
	
	$ackDateTime = date('Y-m-d H:i:s');
	$clientIP = (isset($_GET['clientIP']) ? $_GET['clientIP'] : null);
	$employeeID = (isset($_GET['eid']) ? $_GET['eid'] : null);
	$userAgent = $_SERVER['HTTP_USER_AGENT'];
	$action = (isset($_GET['action']) ? $_GET['action'] : null);
	
	
	$query = "
	INSERT INTO timeclock_disclaimer_log (ackDateTIme,clientIP,employeeID,userAgent,action) 
	values (:ackDateTime, :clientIP, :employeeID, :userAgent, :action)
	";
	
	try {
		$stm = $db->prepare($query);
		$stm->bindParam(":ackDateTime",$ackDateTime);
		$stm->bindParam(":clientIP", $clientIP);
		$stm->bindParam(":employeeID", $employeeID);
		$stm->bindParam(":userAgent", $userAgent);
		$stm->bindParam(":action", $action);
		$stm->execute();
		$aRows = $stm->rowCount();
		
		if ($aRows > 0) {
			$retvar['result'] = true;
		}
	} catch (PDOException $e) {
		$retvar['result'] == false;
		$retvar['data'][] = 'DB INSERT FAILED';
		$retvar['data'][] = $e;
		//die(__LINE__. " :: FATAL ERROR");
	}
		
	echo json_encode($retvar);

}

if ($mode == 'setReviewData') {
	$dateWorked = $_GET['dateWorked'];
	$hoursWorked = $_GET['hoursWorked'];
	$lunchDuration = $_GET['lunchDuration'];
	$miles = $_GET['miles'];
	$note = $_GET['note'];
	
	$_SESSION['tc_dateWorked'] = $dateWorked;
	$_SESSION['tc_hoursWorked'] = $hoursWorked;
	$_SESSION['tc_lunchDuration'] = $lunchDuration;
	$_SESSION['tc_miles'] = $miles;
	$_SESSION['tc_note'] = $note;
	
	$retvar = array('result'=>true);
	
	echo json_encode($retvar);
}

if ($mode == 'xx') { echo "<PRE>". print_r($_SESSION,1); }

if ($mode == 'recordTCEntry') {
    require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
	$db = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
	$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
	$db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);
	
	$retvar = array('result'=>false,'error'=>null,'data'=>null);
	$recorded = false;

	$timeClockType = $_SESSION['tc_timeClockType'];
	
	if ($timeClockType == 2) {
		$retvar = array('result'=>false,'data'=>array());
		
		$dateWorked = $_SESSION['tc_dateWorked'];
		$hoursWorked = $_SESSION['tc_hoursWorked'];
		$lunchDuration = $_SESSION['tc_lunchDuration'];
		$miles = $_SESSION['tc_miles'];
		$note  = $_SESSION['tc_note'];
		$eid = $_SESSION['tc_eid'];
		$dts = date('Y-m-d H:i:s');

		// Check to see if there is an entry for this date already
		$query = "SELECT 1 as recorded from timeclock_driver where dateWorked = '$dateWorked' and eid = $eid";
		unset($data);
		try {
			$stm = $db->query($query);
			$data = $stm->fetchALL();
			if (!empty($data)) {
				$recorded = true;
				$retvar['result'] = false;
				$retvar['data'] = null;
				$retvar['error'] = 1;
			}
		} catch (PDOException $e) {
			echo "<pre>". print_r($e,1). "</pre>";
			die(__LINE__. " :: FATAL ERROR");
		}
		unset($stm);
		
		if ($recorded == false) {
			$query = "
				INSERT INTO timeclock_driver
					(eid, dateWorked, hoursWorked, lunchDuration, miles, note, tc_dts)
				values
					(:eid, :dateWorked, :hoursWorked, :lunchDuration, :miles, :note, :dts)
			";
			try {
				$stm = $db->prepare($query);
				$stm->bindParam(":eid",$eid);
				$stm->bindParam(":dateWorked", $dateWorked);
				$stm->bindParam(":hoursWorked", $hoursWorked);
				$stm->bindParam(":lunchDuration", $lunchDuration);
				$stm->bindParam(":miles", $miles);
				$stm->bindParam(":note", $note);
				$stm->bindParam(":dts",$dts);
				$stm->execute();
				$newID = $db->lastInsertID();
				$retvar['result'] = true;
				$_SESSION['tc_recorded'] = true;
			} catch (PDOException $e) {
				$newID = -1;
				$retvar['result'] = false;
				$retvar['data'][] = 'There was an error.';
				$retvar['data'][] = print_r($e,1);
			}
				$_SESSION['tc_insertID'] = $newID;
		}
		
		echo json_encode($retvar);

	}
}
?>