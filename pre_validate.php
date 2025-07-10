<?php
ini_set("display_errors", 1);
require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
	
$DB = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
$DB->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);

$username = (isset($_POST['var1']) ? $_POST['var1'] : null);
$password = (isset($_POST['var2']) ? $_POST['var2'] : null);
$_SESSION['STC_Valid'] = false;
$_SESSION['var1'] = $username;
$_SESSION['var2'] = $password;

//echo "<!-- Session ". print_r($_SESSION,1) . " -->";

$query = "
SELECT
    timeClockType, id
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
    $data = $stm->fetch();
    if ( !empty($data) ) {
        $_SESSION['eID'] = $data['id'];
    }


} catch (PDOException $e) {
     echo "<!-- \r\n";
     echo "error: true\r\n";
     echo print_r($e,1). "\r\n";
     echo "Line: __LINE__";
     echo "-->";

}

$useClock = 0;

if ( $data['timeClockType'] == 1 ) {
    // employee
    $useClock = 1;
    header("location:dspMessages.php?tc=$useClock");

} else if ( $data['timeClockType'] == 2 ) {
    // driver
    $useClock = 2;
    header("location:dspMessages.php?tc=$useClock");
} else if ( $data['timeClockType'] == 3 ) {
    // both
    $useClock = 3;
    //echo "<!-- Both clocks selected, please choose one below. -->";
} else {
    echo "<script>alert('There was an error. Please try again later.');location='index.php';</script>";
}
    //echo "<script>console.log('clock type: $useClock');</script>";

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>

<script>
    //console.log(useClock);
    //console.log("User: <?= $username; ?>");
    
    <? //if ($useClock == 1) {
       //$_SESSION['tc_useClock'] = 1;
       //echo 'window.location.href = "'. $GCCL_URL. '/validate.php";';
       //}
    ?>

    <? //if ($useClock == 2) { 
       // $_SESSION['tc_useClock'] = 2;
       // echo 'window.location.href = "'. $GCCL_URL. '/tcDriver/validate.php";';
       // }
    ?>

    function clockSelected() {
        if ( $( "#clockType" ).val() == 0 ) {
            $( "#submitButton" ).prop( "disabled", true );
        } else {
            $( "#submitButton" ).prop( "disabled", false );
        }
        useClock = $( "#clockType" ).val();
    }

    function chainNext() {
        if ($( "#clockType" ).val() == 1) { 

            // Company Vehicle
            <? $_SESSION['location'] = "/validate.php"; ?>
            <? $_SESSION['tc_useClock'] = 1; ?>
            //console.log('Type: 1, emp');
            //window.location.href = "/validate.php";
        } 

        if ($( "#clockType" ).val() == 2) { 
            // Personal Vehicle
            <? $_SESSION['location'] = "/tcDriver/validate.php"; ?>
            <? $_SESSION['tc_useClock'] = 2; ?>
            //console.log('Type: 2, driver');
            //window.location.href = "/tcDriver/validate.php";
        }
            //console.log(useClock);
            //console.log("dspMessages.php?tc=" + useClock);
       window.location.href = "dspMessages.php?tc=" + useClock;

    }
</script>
<style>
    #main {
	position:fixed;
	margin-top: 25px;
	width: 400px;
	height: 200px;
	left: 50%;
	margin-left: -10%;
}
#main #var1Form #iform label{
	font-size: xx-large;
}
#main #var1Form #iform input{
	font-size: xx-large;
}
</style>
</head>
<body>
<?php if ($useClock == 3) { ?>
    <div id="main">
        <!--
        Testing login to use pre_validate page. change action back to validate.php
        -->
		<form id="var1Form" method="post" data-ajax="false">
            <div><span style="font-size:xx-large">Select Timeclock Type</span></div>
			<div id="iform" style="margin-top: 10px;">
                <select id="clockType" name="clockType" onchange="clockSelected()" style="width:300px; height: 40px; font-size: xx-large;">
                    <option value=0>-- Select --</option>
                    <option value=1>Company Vehicle</option>
                    <option value=2>Personal Vehicle</option>

                </select>
			</div>
			<table width="400" border="0" cellspacing="0" cellpadding="0" style="margin: top 10px;">
				<tr>
					<td width="200" align="left"><input id="submitButton" style="margin-top:10px;" type="button" value="CONTINUE" disabled onclick="chainNext()"></td>
					<td width="200" align="right"><!--<input type="button" value="DRIVER">--></td>
				</tr>

		</form>
	</div>

<? } ?>
</body>
</html>