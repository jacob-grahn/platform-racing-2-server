<?php

require_once(__DIR__ . '/all_fns.php');

function artifact_first_check($port, $player) {
	global $db;

	$caught_exception = false;
	$user_id = $player->user_id;
	$safe_user_name = htmlspecialchars($player->name);

	try {
		$first_finder = $db->grab( 'first_finder', 'artifact_find', array($user_id) );

		if( $first_finder == $user_id ) {
			award_parts( $db, $user_id, 'head', [27] );
			award_parts( $db, $user_id, 'body', [21] );
			award_parts( $db, $user_id, 'feet', [28] );
		}
	}

	catch(Exception $e) {
		$caught_exception = true;
		$message = $e->getMessage();
		echo "Error: ".$message;
		return false;
	}

	if(!$caught_exception) {
		echo "Awarded bubble set to $safe_user_name for finding the artifact first.";
		$player->write('message`The most humble of congratulations to you for finding the artifact first! To commemorate this momentous occasion, you\'ve been awarded with your very own bubble set.<b /><b />Thanks for playing Platform Racing 2!<b />- Jiggmin');
		return true;
	}

}


//--- award hats ----------------------------------
function award_parts($db, $user_id, $type, $part_ids, $ensure=true) {

	if($type == 'hat') {
		$field = 'hat_array';
	}
	else if($type == 'head') {
		$field = 'head_array';
	}
	else if($type == 'body') {
		$field = 'body_array';
	}
	else if($type == 'feet') {
		$field = 'feet_array';
	}
	else {
		throw new Exception('Unknown type');
	}

	$array_str = $db->grab($field, 'pr2_select', array($user_id));

	$array = explode(',', $array_str);

	foreach($part_ids as $part_id) {
		if( $ensure ) {
			$db->call( 'part_awards_insert', array( $user_id, $type, $part_id, MYSQLI_ASYNC ) );
		}
		$index = array_search($part_id, $array);
		if($index === false) {
			$array[] = $part_id;
		}
	}

	$new_array_str = join(',', $array);

	if($new_array_str != $array_str) {
		$db->call('pr2_update_part_array', array($user_id, $type, $new_array_str), MYSQLI_ASYNC);
		return true;
	}
	else {
		return false;
	}
}

?>
