<?php
ini_set("display_errors","on");
//session_start();
$mode = (isset($_GET['mode']) ? $_GET['mode'] : null);

if ($mode == 'trackDisclaimer') {
	require_once('Human_Resources/PDO_CONN_hr-rw.php3');
	$db = new PDO(PDO_hr_CONN,PDO_hr_USER,PDO_hr_PASS);
	$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);
	
	$retvar = array('result'=>false,'data'=>null);
	
	$ackDateTime = date('Y-m-d H:i:s');
	$clientIP = (isset($_GET['clientIP']) ? $_GET['clientIP'] : null);
	$employeeID = (isset($_GET['eid']) ? $_GET['eid'] : null);
	$userAgent = $_SERVER['HTTP_USER_AGENT'];
	$action = (isset($_GET['action']) ? $_GET['action'] : null);
	
	
	$query = "
	INSERT INTO t_timeclock_disclaimer_log (ackDateTIme,clientIP,employeeID,userAgent,action) 
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
?>