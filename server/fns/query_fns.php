<?php

function award_part($db, $user_id, $type, $part_id, $ensure=true) {
	$parts = array();
	$parts[] = $part_id;
	$result = award_parts( $db, $user_id, $type, $parts, $ensure );
	return $result;
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
			$db->call( 'part_awards_insert', array( $user_id, $type, $part_id ) );
		}
		$index = array_search($part_id, $array);
		if($index === false) {
			$array[] = $part_id;
		}
	}

	$new_array_str = join(',', $array);

	if($new_array_str != $array_str) {
		$db->call('pr2_update_part_array', array($user_id, $type, $new_array_str));
		return true;
	}
	else {
		return false;
	}
}

?>
