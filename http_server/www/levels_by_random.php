<?php

require_once('../fns/all_fns.php');
header("Content-type: text/plain");

$ip = get_ip();

try {

	// rate limiting
	rate_limit('rand_levels-'.$ip, 60, 1, "Only one random level search per minute is allowed.");
	
	// connect
	$db = new DB();

	// get a list of random levels
	$results = $db->call('levels_select_by_rand');
	$rows = $db->to_array($results);
	echo json_encode($rows);
	
	// end it, yo
	die();
	
}

catch(Exception $e){
	$message = $e->getMessage();
	echo "Error: $message";
	die();
}
