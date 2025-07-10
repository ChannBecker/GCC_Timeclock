<?php
	//ini_set("display_errors","on");
    require_once('Lib/detectMobile.php3');

	require_once("include/default.php");
if ($_SESSION['tc_valid'] != 1) {
	header("location:../index.php");
}

    echo "<!-- <pre>". print_r($_SESSION,1). "</pre> -->\r\n";
	$timeClockType = $_SESSION['tc_useClock'];
	$yesterday = strtotime("yesterday");

if ($timeClockType == 2) {
	$dateWorked = $_SESSION['tc_dateWorked'];
	$hoursWorked = $_SESSION['tc_hoursWorked'];
	$lunchDuration = $_SESSION['tc_lunchDuration'];
	$miles = $_SESSION['tc_miles'];
	$note = $_SESSION['tc_note'];
	$recorded = ($_SESSION['tc_recorded'] == true ? true : false);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<?php if (MOBILE == true) { ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
<?php } ?>
<title>Timeclock</title>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/black-tie/jquery-ui.css">
	<link href="css/default.css" rel="stylesheet" type="text/css">
<style>
#container {
	margin-top: 10px;
}

.col1 {
	width: 50%;
	padding-right: 3px;
	text-align: right;
}
.col2 {
	width: 50%;
	padding-left: 3px;
	text-align: left;
}
	.colSpan {
		width:100%;
	}
	
	.label {
		padding-top:5px;
		padding-right: 2px;
		font-weight: bold;
		font-size: 18px;
	}
	
	.values {
		font-size: 18px;
		padding-left: 2px;
		text-align: left;
	}
	
	#hoursWorked {
		width:60px;
		height:30px;
	}
	
	.labelError {
		color: red;
	}
	
	.labelGood {
		color: black;
	}
	
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
	var clientIP = "<?= $_SERVER['REMOTE_ADDR']; ?>";
	var action = "Enter:TC Data";
	var eid = '<?php echo $_SESSION['tc_eid']; ?>';
	
	$().ready(function(e) {
		$("#fineprintAccept").prop('checked',false);
		//$("#submitButton").prop("disabled",true);
		
		$("#dateWorked").datepicker({
			defaultDate: -1,
			duration: "slow",
			dateFormat: "yy-mm-dd",
			maxDate: "+0d",
			minDate: "-21d"
		});
		
		$("#hoursWorked").spinner({
			min: 0,
			max: 24,
			step: .25
		});
		
		$("#hoursWorked").bind("keydown", function(event) { event.preventDefault(); });
		
	});
	
	function checkData() {
		var dataGood = true;
		// Check form data
		// Check Miles
		var test = $("#miles").val();
		
		if (isNaN(test)) {
			$("#labelMiles").addClass('labelError');
			dataGood = false;
		} else {
			$("#labelMiles").removeClass('labelError');
		}
		
		if (dataGood == true) {
			var dateWorked = $("#dateWorked").val();
			var lunchDuration = $("input[name='lunchDuration']:checked").val();
			var hoursWorked = $("#hoursWorked").val();
			var miles = $("#miles").val();
			var note = $("#note").val();
			
			Temp = jQuery.ajax({
				type:"GET",
				url:ajaxHandler + "?mode=setReviewData&dateWorked=" + dateWorked + "&lunchDuration=" + lunchDuration + "&hoursWorked=" + hoursWorked + "&miles=" + miles + "&note=" + note,
				async:false }).responseText;

			Result = $.parseJSON(Temp);
			console.log(Result);
			
			top.location= 'review.php';

		}
		
	}
	
		function validate(type,id) {
			var inputVal = $(id).val();
			if (type == 'A')   {
			var RegEx = /^[a-zA-Z0-9 .]{3,45}$/;
				if (RegEx.test(inputVal)) {
					$(id).removeClass('required');
				} else {
					$(id).addClass('required');
					alert("This field requires Alpha-Numeric entries only.");
				}
			}
		}
	
		function formON() {
			//console.log($("#fineprintAccept"));
			//console.log($("#fineprintAccept").is(":checked"));
			
			if ($("#fineprintAccept").is(":checked")) {
				$("#submitData").prop("disabled", false);
				recordDisclaimer('review:checked');
			} else {
				$("#submitData").prop("disabled", true);
				recordDisclaimer('review:unChecked');
			}
		}
	
		function recordDisclaimer (action=null) {
	
			Temp = jQuery.ajax({
				type:"GET",
				url:ajaxHandler + "?mode=trackDisclaimer&eid=" + eid + "&action=" + action + "&clientIP=" + clientIP,
				async:false }).responseText;

			Result = $.parseJSON(Temp);
			console.log(Result);
		}
	
	function recordData() {
		Temp = jQuery.ajax({
			type:"GET",
			url:ajaxHandler + "?mode=recordTCEntry",
			async:false }).responseText;

		Result = $.parseJSON(Temp);
		
		if (Result.result == true) {
			$("#submitData").attr("disabled", true);
		}
		
		top.location = 'final.php';
		
		}
		
</script>
</head>
<body>
	<div id="header-fineprint">
	<?php include("fineprint.html"); ?>
	</div>
<?php if ($_SESSION['tc_useClock'] == 2) { // tag: 89, timeClockType = 2 ?>
<table id="container" width="300" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="label" colspan="2" align="center">Review Submission</td>
	</tr>
	<tr>
		<td width="150" class="label" align="right">Date Worked:</td>
		<td width="150" class="values" align="left"><?php echo $dateWorked; ?></td>
	</tr>
	<tr>
		<td class="label" align="right">Hours Worked:</td>
		<td class="values" align="left"><?php echo $hoursWorked; ?></td>
	</tr>
	<tr>
		<td class="label" align="right">Lunch:</td>
		<td class="values" align="left"><?php echo $lunchDuration; ?></td>
	</tr>
	<tr>
		<td class="label" align="right"><span id="labelMiles">Miles:</span></td>
		<td class="values" align="center"><?php echo $miles; ?></td>
	</tr>
	<tr>
		<td class="label" align="right">Note:</td>
		<td class="values" align="center"><?php echo $note; ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td class="label" align="center" colspan="2"><button id="submitData" onclick="recordData()" disabled>RECORD</button></td>
	</tr>
</table>
<?php } // tag: 89, timeClockType = 2 ?>
<div id="footer">
TIMECLOCK &copy; <?= date('Y'); ?> GC &amp; C Logistics
	</div>
</body>
</html>