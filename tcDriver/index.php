<?php
	//ini_set("display_errors","on");
	require_once("include/default.php");

	foreach ($_SESSION as $k=>$d) {
		unset($_SESSION[$k]);
	}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Timeclock</title>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/black-tie/jquery-ui.css">
	<link href="css/default.css" rel="stylesheet" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
	var clientIP = "<?= $_SERVER['REMOTE_ADDR']; ?>";
	
	$().ready(function(e) {
		$("#fineprintAccept").prop('checked',false);
		$("#submitButton").prop("disabled",true);
	});
	
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
				$("#submitButton").prop("disabled", false);
				recordDisclaimer('logon:Checked');
			} else {
				$("#submitButton").prop("disabled", true);
				recordDisclaimer('login:unChecked');
			}
		}
	
		function recordDisclaimer (action=null) {
			var eid = $("#var1").val();
			
			Temp = jQuery.ajax({
				type:"GET",
				url:ajaxHandler + "?mode=trackDisclaimer&eid=" + eid + "&action=" + action + "&clientIP=" + clientIP,
				async:false }).responseText;

			Result = $.parseJSON(Temp);
		}
		
</script>
</head>
<body>
	<div id="header-fineprint">
	<?php include("fineprint.html"); ?>
	</div>
	<div id="main">
		<form id="var1Form" method="post" action="validate.php" data-ajax="false">
			<div id="iform">
				<label for="var1">I.D.:</label>
				<input type="text" name="var1" id="var1" placeholder="I.D." maxlength="6" onBlur="validate('A',this);">
				<div id="var1_note">&nbsp;</div>
				<label for="var2">PIN:</label>
				<input type="password" name="var2" id="var2" maxlength="4">
				<div id="var2_note">&nbsp;</div>
			</div>
			<input id="submitButton" type="submit" value="LOGIN" disabled >
		</form>
	</div>
	<div id="footer">
TIMECLOCK &copy; <?= date('Y'); ?> GC &amp; C Logistics
	</div>
</body>
</html>