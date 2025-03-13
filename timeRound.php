<?php
function timeRound($value=null, $round=15) {
	$round = $round * 60;
	$time = strtotime($value);
//	echo "val: ". $value. "<br>";
//	echo "rnd: ". $round. "<br>";
//	echo "Time: ". $time. "<br>";
	$time = $round * round($time/$round);
	
return $time;
}
?>