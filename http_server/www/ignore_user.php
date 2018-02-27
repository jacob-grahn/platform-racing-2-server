<?php

header("Content-type: text/plain");
require_once('../fns/all_fns.php');

$ignored_name = $_POST['target_name'];
$safe_ignored_name = htmlspecialchars($ignored_name);
$ip = get_ip();

try {
	
	// post check
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception("Invalid request method.");
	}
	
	// rate limiting
	rate_limit('ignored-list-'.$ip, 3, 1);
	rate_limit('ignored-list-'.$ip, 30, 5);
	
	// connect
	$db = new DB();
	
	// check their login
	$user_id = token_login($db);
	
	// more rate limiting
	rate_limit('ignored-list-'.$user_id, 3, 1);
	rate_limit('ignored-list-'.$user_id, 30, 5);
	
	// get the ignored user's id
	$ignored_id = name_to_id($db, $ignored_name);
	
	// create the restraining order
	$db->call('ignored_insert', array($user_id, $ignored_id));
	
	// tell it to the world
	echo "message=$safe_ignored_name has been ignored. You won't recieve any chat or private messages from them.";
}

catch (Exception $e) {
	$error = $e->getMessage();
	echo "error=$error";
	exit;
}
