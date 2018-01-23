<?php

header("Content-type: text/plain");

require_once('../../fns/all_fns.php');

$user_name = find('user_name');
$pass = find('pass');
$type = find('type');
$part_id = find('part_id');

try {

	if($pass != $PR2_HUB_API_PASS) {
		throw new Exception('Incorrect pass.');
	}

	//connecto!!!
	$db = new DB();

	//get the to id
	$target_id = name_to_id($db, $user_name);

	//give the player the part
	$parts = array();
	$parts[] = $part_id;
	$result = award_parts($db, $target_id, $type, $parts);
	if(!$result) {
		throw new Exception('They already have the part.');
	}

	//tell the world
	$ret = new stdClass();
	$ret->success = true;
	$ret->message = "The part was given to " . htmlspecialchars($user_name) . ".";
	echo json_encode( $ret );
}

catch(Exception $e){
	$ret = new stdClass();
	$ret->success = false;
	$ret->error = $e->getMessage();
	echo json_encode( $ret );
}

?>
