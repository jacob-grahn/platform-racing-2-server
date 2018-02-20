<?php

header("Content-type: text/plain");
require_once('../fns/all_fns.php');

$friend_name = $_POST['target_name'];
$safe_friend_name = htmlspecialchars($friend_name);

try {
	
	// connect
	$db = new DB();
	
	// check their login
	$user_id = token_login($db);
	
	// get the id of the player they're removing as a friend
	$friend_id = name_to_id($db, $friend_name);
	
	// delete the friendship :(
	$db->call('friend_delete', array($user_id, $friend_id));
	
	// tell the world
	echo "message=$safe_friend_name has been removed from your friends list.";						
}

catch (Exception $e){
	$error = $e->getMessage();
	echo "error=$error";
}
