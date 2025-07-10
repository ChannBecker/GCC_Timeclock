<?php

/*
		This is the main clock program.
		
		We need to scan the database to determine if this is a IN or OUT clock.
		
	*/
ini_set('display_errors','on');

require_once('timeround.php');
DEFINE("TC_NOTICE_LOG",false);

		
require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
	
$DB = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
$DB->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);
	
	if ($_SESSION['STC_Valid'] == false) {
		$_SESSION['STC_Valid'] = false;
		$_SESSION['STC_lname'] = null;
		$_SESSION['STC_fname'] = null;
		$_SESSION['STC_BadAttempt'] += 1;
		
		header("location:index.php");
	}
	
	$clockMode = $_SESSION['STC_clockType'];
  $isDriver  = $_SESSION['STC_driver'];
	
	if ($clockMode == "O") { // tag: 30
		$dataID = $_SESSION['STC_dataID'];
		$query = "
			SELECT
				*
			FROM
				timeclock
			WHERE
				id = :id 
		";
		try {
			$st = $DB->prepare($query);
			$st->bindParam(":id",$dataID);
			$st->execute();
			$data = $st->fetch(pdo::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "<pre>". print_r($e,1). "</pre>";
			die(__LINE__. " :: FATAL ERROR");
		}
	} // tag: 30

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>GC &amp; C Logistics Time Clock</title>
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
table tr td {
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
  #vehicle {
    font-size: xx-large;
    
  }
#boxLunch {
	margin-top: 15px;
	margin-bottom: 10px;
}
#clock tr td {
}
#timeOverWarning {
	color: #FF0000;
	font-weight: bolder;
	font-size: x-large;
}
.hint {
  color: #B8B8B8;
  font-size: small;
}
</style>
	<link rel="stylesheet" href="css/default.css">
	<!-- <link rel="stylesheet" href="http://cdn.gccmgt.com/j_query/1.10.2/themes/start/jquery-ui.css"> -->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.3/themes/smoothness/jquery-ui.css">
	<?php /* <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> */ ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	<script type="text/javascript" src="http://cdn.gccmgt.com/j_query/ui/jquery-ui-timepicker.js"></script>
	<script type="text/javascript" src="http://cdn.gccmgt.com/Lib/function.js"></script>
	<script>
<?php 
	if ($clockMode == "O") {  ?>
<?php
	$D = new DateTime(substr($data['inDateTime'],0,16)); 
	$oY = date('Y',timeround(date('Y-m-d H:i:s')));
	$oM = date('m',timeround(date('Y-m-d H:i:s')));
	$oD = date('d',timeround(date('Y-m-d H:i:s')));
	$oH = date('H',timeround(date('Y-m-d H:i:s')));
	$oI = date('i',timeround(date('Y-m-d H:i:s')));
	
?>
		var outDate = new Date(<?= $oY. ",". (int) ($oM - 1). ",$oD,$oH,$oI,0,0"; ?>);
		var outTime = new Date(<?= $oY. ",". (int) ($oM - 1). ",$oD,$oH,$oI,0,0"; ?>);
		var inDate  = new Date(<?= $D->format('Y,'). ((int) $D->format('m') - 1). $D->format(',d,H,i,0,0'); ?>);
		var diffTime;
    var isDriver = <?= ($isDriver == 0 ? 'false' : 'true'); ?>;

		$().ready(function(e) {
			diffTime = (outDate - inDate) - min_to_ticks(60);

			if (diffTime < 0) diffTime = 0;
      
      if (isDriver == true) {
        $("#record").prop("disabled",true);
      }
			
			$("#boxLunch").buttonset();
			$("#outDate").val(outDate.getFullYear() + "-" + pad((outDate.getMonth() + 1),2) + "-" + pad(outDate.getDate(),2));
			$("#outTime").val(pad(outTime.getHours(),2) + ":" + pad(outTime.getMinutes(),2));
			$("#totalHours").val(ticks_to_hhmm(diffTime));
			checkHours();
			
			$("#lunchNO").click(function(e) {
				diffTime = (outDate - inDate);
				$("#totalHours").val(ticks_to_hhmm(diffTime));
				checkHours();
			});
			$("#lunch30").click(function(e) {
				diffTime = (outDate - inDate) - min_to_ticks(30);
				if (diffTime < 0) diffTime = 0;
				$("#totalHours").val(ticks_to_hhmm(diffTime));
				checkHours();
			});
			$("#lunch60").click(function(e) {
				diffTime = (outDate - inDate) - min_to_ticks(60);
				if (diffTime < 0) diffTime = 0;
				$("#totalHours").val(ticks_to_hhmm(diffTime));
				checkHours();
			});
		});
<?php } // CLOCKMODE = "O" ?>
    
		function checkHours() {
      //alert($("#totalHours").val());
			var timeMax = dec_to_min(<?= $_SESSION['STC_hire_hours'];?>);
			var subTime = hhmm_to_min($("#totalHours").val());
			
			if (subTime > timeMax) {
				$("#warningLine1").show();
				$("#warningLine2").show();
				$("#isOver").val(1);
				$("#dbtimeMax").val(timeMax);
				$("#dbsubTime").val(subTime);
				//$("#record").attr("disabled","disabled");
				$("#reason").focus();
			}
		}
		
		function checkReason() {
      console.log("L: " + $("#reason").val().length);
      console.log("V: " + $("#vehicle").val());
      console.log("ID: " + (isDriver == true ? "T" : "F"));
      
			if ($("#reason").val().length > 5 && isDriver == false) {
        console.log("not driver");
				$("#record").prop("disabled",false);
			} else if ($("#reason").val().length > 5 && isDriver == true) {
        console.log("isDriver");
        if ($("#vehicle").val() > 0) {
          console.log("enable record");
          $("#record").prop("disabled",false);
        }
      } else {
        console.log("ELSE");
      }
    }
    
    
    
    function checkVehicle() {
      if ($("#vehicle").val() > 0) {
        $("#record").prop("disabled",false);
      }
    }
	</script>
</head>
<body>
<?php 
  // tag: 217, NOT MOBILE
	if ($clockMode == "I") { // tag: 218, PUNCH IN
		$DSP_In = date('Y-m-d H:i',timeround(date('Y-m-d H:i:s'),15));

?>
		<form method="post" action="record.php?mode=4" data-ajax="false">
			<input type="hidden" name="inDateTime" value="<?= date('Y-m-d H:i',timeround(date('Y-m-d H:i:s'))); ?>">
		<div id="main-content" data-role="main" class="ui-content">
		<div id="header-fineprint" data-role="header">
			<?php include("fineprint.html"); ?>
		</div>
		<div id="header">CLOCKING IN</div>
		<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="right" class="label2">Employee #:</td><td align="left" class="field2"><?= $_SESSION['STC_employeeNumber']; ?></td>
			<tr><td align="right" class="label2">Name:</td><td align="left" class="field2"><?= $_SESSION['STC_fname']. " ". $_SESSION['STC_lname']; ?></td>
			<tr><td align="right" class="label2">Date / Time:</td><td align="left" class="field2"><?= $DSP_In; ?></td>
			<tr><td>&nbsp;</td></tr>
			<tr><td align="right" class="label2"><input type="reset" value="Cancel" onClick="location='index.php'"></td><td class="label2"><input type="submit" value="Record"></td></tr>
		</table>
		<div id="footer">
			TIMECLOCK &copy; <?= date('Y'); ?> GC &AMP; C Logistics
		</div>
		</div>
		</form>
<?php
	} // tag: 218, PUNCH IN
	if ($clockMode == "O") { // tag: 251, PUNCH OUT
?>
<div id="homePage">
  <div id="header-fineprint">
    <?php include("fineprint.html"); ?>
  </div>
  <div>
    <div id="header">You are completing a timeclock entry started on <?= substr($data['inDateTime'],0,10). " at ". substr($data['inDateTime'],11,5); ?></div>
    <form method="post" action="record.php" data-ajax="false">
		<table align="center" width="600" border=0 cellpadding="0" cellspacing="0" id="clock">
			<tr><td align="right" class="label2"><label for="outDate">Date:</label></td><td align="left" class="field2"><input name="outDate" type="text" id="outDate" readonly></td></tr>
			<tr><td align="right" class="label2"><label for="outTime">Time:</label></td><td align="left" class="field2"><input name="outTime" type="text" id="outTime" readonly></td></tr>
			<tr><td align="center" colspan="2">
				<div id="boxLunch">
					<legend class="label2">Length of Lunch</legend>
					<input type="radio" id="lunchNO" name="lunch" value=0 style="z-index:0"><label for="lunchNO" style="z-index:1">No Lunch</label>
					<input type="radio" id="lunch30" name="lunch" value=30 style="z-index:0"><label for="lunch30" style="z-index:1">30 min</label>
					<input type="radio" id="lunch60" name="lunch" value=60 checked="checked" style="z-index:0"><label for="lunch60" style="z-index:1">60 min</label>
				</div>
			</td></tr>
			<tr><td align="right" class="label2"><label for="TotalHours">Total Hours:</label></td><td align="left" class="field2"><input name="totalHours" id="totalHours" type="text" readonly></td></tr>
			
			<tr id="warningLine1" style="display:none">
				<td colspan="2"><span id="timeOverWarning">Time entered is GREATER than time on file. Please explain on the REASON line why it is over.</span></td>
			</tr>
			<tr id="warningLine2" style="display:none">
				<td align="right" class="label2"><label for="reason">Reason Over:</label></td>
				<td align="left"  class="field2">
					<input type="text" name="reason" id="reason" maxlength="250" onBlur="checkReason();">
					<input type="hidden" id="isOver" name="isOver" value=0>
					<input type="hidden" id="dbtimeMax"   name="dbtimeMax" value=0>
					<input type="hidden" id="dbsubTime"   name="dbsubTime" value=0>
					<br><span class="hint">(must be at least 5 characters)</span>
				</td>
			<tr>
<!--				<td align="right" class="label2">
					<label for="miles">Miles:</label>
        </td>
         <td align="left" class="field2">
          <input name="miles" type="number" id="miles" maxlength="4" value="0" onFocus="this.select()">
        </td>
 -->      </tr>
<? if ($isDriver == true) { ?>
			<tr>
				<td align="right" class="label2">
					<label for="vehicle">Vehicle:</label>
        </td>
        <td align="left" class="field2">
          <select name="vehicle" id="vehicle" value="-1" onChange="checkVehicle();">
            <option value="-1">-- Select Vehicle --</option>
            <option value="1">Personal Vehicle</option>
            <option value="2">Company Vehicle</option>
            <? foreach ($fleetVehicles->data as $k=>$d) { ?>
            <option value="<?= $d['vehicleID']; ?>"><?= $d['number']. " ". $d['description']; ?></option>
            <? } ?>
          </select>
        </td>
      </tr>
<? } ?>
			<tr><td align="right" class="label2"><label for="Note">Note:</label></td><td align="left" class="field2"><input type="text" name="Note" id="Note" maxlength="254" value="" data-clear-btn="true"></td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td align="right"><input type="reset" data-theme="c" value="Cancel" onClick="location='index.php'">&nbsp;</td><td align="left">&nbsp;<input id="record" type="submit" data-theme="b" value="Record"></td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
		</table>
    </form>
  </div>
		<div id="footer">
			TIMECLOCK &copy; <?= date('Y'); ?> GC &amp; C Logistics
		</div>
</div>
<?php 
	}  // tag: 251, PUNCH OUT
?>
</body>
</html>

