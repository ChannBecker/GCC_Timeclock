<?php
/*
**
**
**
*/
// Initial Includes
ini_set("display_errors","off");
require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
require_once('Lib/class_fncResult.php3');

// Start Logging

// Security Check

// Required files

// Define local classes

// Define local functions

// Create DB Connections
$db = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
$db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);

// Create Objects

// Define Variables
$username = $_SESSION['var1'];
$password = $_SESSION['var2'];
$eID      = $_SESSION['eID'];
$location = $_SESSION['location'];
$useClock = $_GET['tc'];
define('TC_NOTICE_LOG', false); // Set to true to disable login button

// Script Body

$result = new fncResult();
$query = "
SELECT
    m.messageID,
    m.employeeGroupID,
    m.startDate,
    m.endDate,
    m.titleText,
    m.messageText,
    m.archived
FROM
    employee_message m
LEFT JOIN employee_message_log l
    ON m.messageID = l.messageID
    AND l.employeeID = :employeeID
WHERE
    m.archived = 0
    AND m.startDate <= CURDATE()
    AND (m.endDate >= CURDATE() OR m.endDate IS NULL)
    AND l.messageLogID IS NULL
ORDER BY
    m.endDate,
    m.startDate";
try {
    $stm = $db->prepare($query);
    $stm->bindParam(':employeeID',$eID);
    $stm->execute();
    $data = $stm->fetchALL();
    
    if ( !empty( $data ) ) {
        $result->result = true;
        $result->data = $data;
    }
} catch (PDOException $e) {
    $result->error = true;
    $result->error_desc = print_r($e,1);
    $result->misc = 'Line: __LINE__';
}

if ($result->result == false) {
    header("location:". ($useClock == 1 ? "/validate.php" : "/tcDriver/validate.php"));
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="author" content="Channing Becker">
    <title>GCCL Intranet</title>


    <link rel="stylesheet" type="text/css" href="/css/default.css">
    <link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

    <script>
        $(function() {
            // Handler for .ready() called.

        });
        
    </script>
<style>
body, input, button, textarea {
    font-family: 'Fira Sans', 'Quicksand', 'Cabin', 'Nunito', 'Segoe UI', 'Arial Rounded MT Bold', Arial, sans-serif;
}
.centered-messages {
    max-width: 600px;
    margin: 30px auto 0 auto;
}
.message-block {
    border: 1px solid #ccc;
    margin: 10px 0;
    padding: 10px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border-radius: 6px;
}
</style>

</head>

<body>
    <!-- <pre><? echo print_r($_SESSION,1); ?></pre> -->
<div id="messages" class="centered-messages">
  	<div id="header-fineprint">
	<?php include("fineprint.html"); ?>
	</div>
    <div style="text-align: center; "><span style="font-weight: bolder;">Read and Acknowledge the Message(s) to continue.</span></div>
<?php
if (!empty($result->data) && is_array($result->data)) {
    foreach ($result->data as $msg) {
        echo '<div class="message-block">';
        echo '<h3>' . htmlspecialchars($msg['titleText']) . '</h3>';
        echo '<div>' . nl2br(htmlspecialchars($msg['messageText'])) . '</div>';
        echo '<div style="margin-top:10px;">';
        echo '<label><input type="checkbox" class="ack-checkbox" data-messageid="' . (int)$msg['messageID'] . '"> Acknowledge</label>';
        echo '</div>';
        echo '</div>';
    }
}
?>
</div>
<script>
$(function() {
    function checkAllAcknowledged() {
        // If no message blocks remain, redirect
        if ($('.message-block:visible').length === 0) {
            window.location.href = '<?php echo ($useClock == 1 ? "/validate.php" : "/tcDriver/validate.php"); ?>';
        }
    }

    // If there are no messages to start with, redirect immediately
    if ($('.message-block').length === 0) {
            window.location.href = '<?php echo ($useClock == 1 ? "/validate.php" : "/tcDriver/validate.php"); ?>';
    }

    $('.ack-checkbox').on('change', function() {
        if (this.checked) {
            var messageID = $(this).data('messageid');
            $.ajax({
                url: 'dspMessagesAck.php',
                method: 'POST',
                data: { messageID: messageID },
                success: function(resp) {
                    $("input[data-messageid='"+messageID+"']").closest('.message-block').fadeOut(300, checkAllAcknowledged);
                }
            });
        }
    });
});
</script>
</body>

</html>