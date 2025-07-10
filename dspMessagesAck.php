<?php
session_start();
require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
$eID = $_SESSION['eID'];
if (!isset($_POST['messageID']) || !$eID) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}
$messageID = (int)$_POST['messageID'];
$db = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
$ip = $_SERVER['REMOTE_ADDR'];
// Get deviceID from user agent
$deviceID = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$sql = "INSERT INTO employee_message_log (employeeID, messageID, logIP, deviceID) VALUES (:employeeID, :messageID, :logIP, :deviceID)";
$stm = $db->prepare($sql);
$stm->bindParam(':employeeID', $eID);
$stm->bindParam(':messageID', $messageID);
$stm->bindParam(':logIP', $ip);
$stm->bindParam(':deviceID', $deviceID);
$stm->execute();
echo 'OK';
?>
