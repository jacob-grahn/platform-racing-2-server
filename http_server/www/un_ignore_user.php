<?php

header("Content-type: text/plain");
require_once('../fns/all_fns.php');

$target_name = $_POST['target_name'];
$safe_name = htmlspecialchars($target_name);

try {
	
	// connect
	$db = new DB();
	
	// check their login
	$user_id = token_login($db);
	
	// get the id of the un-ignored player
	$target_id = name_to_id($db, $target_name);
	
	// reconcile the differences :)
	$db->call('ignored_delete', array($user_id, $target_id));
	
	// tell the world
	echo "message=$safe_name has been un-ignored. You will now recieve any chat or private messages they send you.";

}

catch (Exception $e) {
	$error = $e->getMessage();
	echo "error=$error";
}

?>
