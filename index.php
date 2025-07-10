<?php
DEFINE("TC_NOTICE_LOG",false);

foreach ($_SESSION as $k=>$d) {
	if (substr($k,0,4) == 'STC_' || substr($k,0,3) == 'tc_') {
		unset($_SESSION[$k]);
	}
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>GC &amp; C Logistics Timeclock</title>
<link rel="stylesheet" href="http://cdn.gccmgt.com/j_query/1.10.2/themes/black-tie/jquery-ui.css" />
<link href="css/default.css" rel="stylesheet" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript">
    var ajaxHandler = "index_ajax.php";
    var clientIP = "<?= $_SERVER['REMOTE_ADDR']; ?>";

    function validate(type,id) {
        var inputVal = $(id).val();
        if (type == 'A')   {
        var RegEx = /^[a-zA-Z0-9 .]{3,45}$/;
            if (RegEx.test(inputVal)) {
                $(id).removeClass('required');
            } else {
                $(id).addClass('required');
            }
        }
    }
    
    function formON() {
        if ($("#fineprintAccept").is(":checked")) {
            $("#submitButton").prop("disabled", false);
            recordDisclaimer('logonV1:Checked');
        } else {
            $("#submitButton").prop("disabled", true);
            recordDisclaimer('loginV1:unChecked');
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
        <!--
        Testing login to use pre_validate page. change action back to validate.php
        -->
		<form id="var1Form" method="post" action="pre_validate.php" data-ajax="false">
			<div id="iform">
				<label for="var1">I.D.:</label>
				<input type="text" name="var1" id="var1" placeholder="I.D." maxlength="6" onBlur="validate('A',this);">
				<div id="var1_note">&nbsp;</div>
				<label for="var2">PIN:</label>
				<input type="password" name="var2" id="var2" maxlength="4">
				<div id="var2_note">&nbsp;</div>
			</div>
			<table width="400" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td width="200" align="left"><input id="submitButton" type="submit" value="LOGIN" <?php echo (TC_NOTICE_LOG == true ? "disabled" : ""); ?> ></td>
					<td width="200" align="right"><!--<input type="button" value="DRIVER">--></td>
				</tr>

		</form>
	</div>
    <? require_once($_SERVER['DOCUMENT_ROOT']. "/include/footer.html"); ?>
</body>
</html>