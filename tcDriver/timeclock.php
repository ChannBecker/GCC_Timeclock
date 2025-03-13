<?php
//	ini_set("display_errors","on");
    require_once('Lib/detectMobile.php3');

	require_once("include/default.php");

if ($_SESSION['tc_valid'] != 1) {
	header("location:../index.php");
}

	$timeClockType = $_SESSION['tc_timeClockType'];
	$baseTime = time();
	$aDay = (60*60*24);


	$useDate = date('Y-m-d',($baseTime - $aDay));
	// Get a list of entries for the last 30 days.
    require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');

    $db = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
    $db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);

	$eid = $_SESSION['tc_eid'];
	
	$excludeDate = array();
	$query = "SELECT dateWorked from timeclock_driver where eid = $eid and dateWorked > date_add(now(), INTERVAL -45 DAY)";
	//echo "\r\n<!-- ". $query. "-->\r\n";
	try {
		$stm = $db->query($query);
		$entryDays = $stm->fetchALL();
	} catch (PDOException $e) {
		trigger_error(print_r($e,1));
	}

	if (!empty($entryDays)) {
		foreach ($entryDays as $k=>$d) {
			$excludeDate[] = "'". $d['dateWorked']. "'";
		}
	}

$excludeDays = null;
for ($xx = 1; $xx < 32; $xx++) {
    $excludeDate[] = "'2024-03-$xx'";
}
$excludeDays = implode(",",$excludeDate);

//echo "\r\n<!-- ". $excludeDays. "-->\r\n";

//echo "<!-- <pre>". print_r($_SESSION,1). "</pre> -->\r\n";
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<?php if (MOBILE == true) { ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
<?php } ?>
<title>Timeclock</title>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.3/themes/smoothness/jquery-ui.css">
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
            font-weight: bold;
            font-size: 12px;
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
<script type="text/javascript" src="http://cdn.gccmgt.com/j_query/ui/jquery-ui-timepicker.js"></script>

<script type="text/javascript">
	var clientIP = "<?= $_SERVER['REMOTE_ADDR']; ?>";
	var action = "Enter:TC Data";
	var eid = '<?php echo $_SESSION['tc_eid']; ?>';
	var maxHours = <?php echo $_SESSION['tc_hire_hours']; ?>;
	var excludeDays = <? echo (empty($excludeDays) ? "[". date('Y-m-d'). "]" : "[". $excludeDays. "]"); ?>;
	
	$().ready(function(e) {
		$("#fineprintAccept").prop('checked',false);
		//$("#submitButton").prop("disabled",true);
		
		$("#dateWorked").datepicker({
			defaultDate: -1,
			duration: "slow",
			dateFormat: "yy-mm-dd",
			maxDate: "+0d",
			minDate: "-21d",
			beforeShowDay: function (date){
				var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
        return [ excludeDays.indexOf(string) == -1 ]
			}
		});
		
		$("#hoursWorked").spinner({
			min: 0,
			max: 24,
			step: .25,
			spin: function(event,ui) {
				if (ui.value > maxHours) {
					//console.log("show - " + $("#hoursWorked").val());
					$("#hoursOverMessage").show();
				} else {
					//console.log("hide - " + $("#hoursWorked").val());
					$("#hoursOverMessage").hide();
				}
				
			}
			

		});
		
		$("#hoursWorked").change(function() {
		});
		
		$("#hoursWorked").bind("keydown", function(event) { event.preventDefault(); });
			
		$("#dateWorked").focus();		
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
		
		if ($("#dateWorked").val() == '1900-01-01') { 
			dataGood = false;
			alert('Date Worked does not have a valid value!');
		}
		
		if (dataGood == true) {
			var dateWorked = $("#dateWorked").val();
			var lunchDuration = $("input[name='lunchDuration']:checked").val();
			var hoursWorked = $("#hoursWorked").val();
			var miles = $("#miles").val();
			var note = $("#note").val();
			
			var urlString = ajaxHandler + "?mode=setReviewData&dateWorked=" + dateWorked + "&lunchDuration=" + lunchDuration + "&hoursWorked=" + hoursWorked + "&miles=" + miles + "&note=" + note;
			console.log(urlString);
			
			Temp = jQuery.ajax({
				type:"GET",
				url: urlString,
				async:false }).responseText;

			Result = $.parseJSON(Temp);
			console.log(Result);
			//alert('debug');
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
			if ($("#fineprintAccept").is(":checked")) {
				$("#submitData").prop("disabled", false);
				recordDisclaimer('submit:checked');
			} else {
				$("#submitData").prop("disabled", true);
				recordDisclaimer('submit:unChecked');
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
		
</script>
</head>
<body>
	<div id="header-fineprint">
	<?php include("fineprint.html"); ?>
	</div>
<?php if ($_SESSION['tc_timeClockType'] == 2) { // tag: 89, timeClockType = 2 ?>
<table id="container" width="300" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="label" align="center">Date Worked</td>
	</tr>
	<tr>
		<td class="" align="center">
			<input id="dateWorked" type="text" value="<? echo $useDate; ?>" readonly onBlur="">
		</td>
	</tr>
	<tr>
		<td class="label" align="center"><div id="dspHoursWorked"  style="">Hours Worked</div></td>
	</tr>
	<tr>
		<td class="label" align="center"><div id="dspHoursWorked"  style="margin-top: -5px; margin-bottom: 5px; font-size: 10px;">(This includes time spent at lunch)</div></td>
	</tr>
	<tr>
		<td align="center"><input id="hoursWorked" value="8"></td>
	</tr>
	<tr id="hoursOverMessage" style="display:none">
		<td align="center" style="margin-top: -5px; margin-bottom: 5px; font-size: 12px; color:red;">Hours entered are over allowed value. Explain overage in note.</td>
	</tr>
	<tr>
		<td class="label" align="center">Lunch</td>
	</tr>
	<tr>
		<td align="center">
			<label for="lunchNone">None</label>
			<input type="radio" id="lunchNone" name="lunchDuration" value="0" >,
			<label for="lunch30">30 Min</label>
			<input type="radio" id="lunch30"   name="lunchDuration" value="30">,
			<label for="lunch60">60 Min</label>
			<input type="radio" id="lunch60"   name="lunchDuration" value="60" checked>
		</td>
	</tr>
	<tr>
		<td class="label" align="center"><span id="labelMiles">Miles</span></td>
	</tr>
	<tr>
		<td align="center"><input type="text" id="miles" name="miles" maxlength="3" value="0"></td>
	</tr>
	<tr>
		<td class="label" align="center">Note</td>
	</tr>
	<tr>
		<td align="center"><input type="text" id="note" name="note" maxlength="100" value="" height="60"></td>
	</tr>
	<tr>
		<td class="label" align="center"><button id="submitData" onClick="checkData();" disabled>RECORD</button></td>
	</tr>
</table>
<?php } // tag: 89, timeClockType = 2 ?>
<? require_once($_SERVER['DOCUMENT_ROOT']. "/include/footer.html"); ?>
</body>
</html>