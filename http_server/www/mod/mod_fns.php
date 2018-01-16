<?php

class ActionConstants {
	const BAN = 'ban';
	const LIFT_BAN = 'liftBan';
	const LOGIN = 'login';
	const UNPUBLISHED_LEVEL = 'unpublish';
	const MODDED = 'modded';
}


function output_pagination($start, $count) {
	$next_start_num = $start + $count;
	$last_start_num = $start - $count;
	if($last_start_num < 0) {
		$last_start_num = 0;
	}
	
	echo('<p>');
	if($start > 0) {
		echo("<a href='?start=$last_start_num&count=$count'><- Last</a> |");
	}
	else {
		echo('<- Last |');
	}
	echo(" <a href='?start=$next_start_num&count=$count'>Next -></a></p>");
}

?>
