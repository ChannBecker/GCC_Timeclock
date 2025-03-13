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
	$retvar['data'] = $_SESSION;
	
	echo json_encode($retvar);
}

if ($mode == 'checkDate') {
    require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
	$db = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
	$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
	$db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);
	$return = array('result'=>true,'data'=>array(),'error'=>null);
	
	$checkDate = $_GET['checkDate'];
	$eid = $_SESSION['tc_eid'];
	
	$query = "SELECT 1 as dateExists from timeclock_driver where eid = :eid and dateWorked = :checkDate";
	try {
		$stm = $db->prepare($query);
		$stm->bindParam(":eid",$eid);
		$stm->bindParam(":checkDate",$checkDate);
		$stm->execute();
		$data = $stm->fetch();
		
		if (!empty($data)) {
			$return['result'] = false;
		}
	
	} catch (PDOException $e) {
		$return['result'] = false;
		$return['error'] = true;
	}

	echo json_encode($return);
}
?>