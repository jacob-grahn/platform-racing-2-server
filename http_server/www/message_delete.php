<?php

header("Content-type: text/plain");
require_once('../fns/all_fns.php');

$message_id = $_POST['message_id'];

try {
	
	// connect
	$db = new DB();
	
	// check their login
	$user_id = token_login($db);
	
	// delete the message from the database
	$db->call('message_delete', array($user_id, $message_id));
	
	// tell the world
	echo 'success=true';
	
}
catch (Exception $e) {
	$error = $e->getMessage();
	echo "error=$error";
	exit;
}
?>
