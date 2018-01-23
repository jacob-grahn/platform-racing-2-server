<?php

set_time_limit(0);

require_once(__DIR__ . '/../fns/all_fns.php');

$db = new DB();

$i = 0;
$min_time = time() - (60 * 60 * 24 * 30 * 12 * 3); //three years

$result = $db->call('users_select_old', array($min_time));

output(number_format($result->num_rows) . ' accounts have not been logged into recently.');


while($row = $result->fetch_object()) {
	$user_id = $row->user_id;
	$rank = $row->rank;

	$user_result = $db->call('user_select_level_plays', array($user_id));
	$user_row = $user_result->fetch_object();
	$play_count = @$user_row->level_plays;
	if(!isset($play_count)) {
		$play_count = 0;
	}

	$str = "$i $user_id plays: $play_count rank: $rank.";
	if($play_count > 50 || $rank > 10) {
		output("$str SPARE");
	}
	else {
		output("$str DELETE");
		$db->call('user_delete', array($user_id));
	}

	$user_result->close();
	$i++;
}


function output($str) {
	echo "$str\n";
}



?>
