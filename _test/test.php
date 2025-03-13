<?php
/*
**
**
**
*/
// Initial Includes
ini_set("display_errors","on");

require_once('Lib/ProgramHeader.php3');
ini_set("display_errors","on");
require_once('Lib/class_Log.php3');
require_once('Lib/class_fncResult.php3');
require_once('GCC/PDO_CONN_GCCL.php3');


// Start Logging
$l = new IntranetLog();
$l->SetEcho(false);
$l->brk();
$l->og("DB Testing");

// Required files

// Define local classes

// Define local functions

// Create DB Connections
$db = new PDO(PDO_GCCL_CONN,PDO_GCCL_USER,PDO_GCCL_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
$db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);

// Create Objects

// Define Variables

// Script Body
$result = new fncResult();
$query = "select * from testdb";
try {
    $stm = $this->db->query($query);
    $data = $stm->fetchALL();
    //$data = $stm->fetch();

    //$stm = ${}->prepare($query);
    //$stm->bindParam('',);
    //$stm->execute();
    //$data = $stm->fetchALL();
    //$data = $stm->fetch();

    if ( !empty( $data ) ) {
        $result->result = true;
        $result->data = $data;
    }
} catch (PDOException $e) {
    $result->error = true;
    $result->error_desc = print_r($e,1);
    $result->misc = 'Line: __LINE__';
}

echo print_r($result,1);
?>