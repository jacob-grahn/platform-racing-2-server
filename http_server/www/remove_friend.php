<?php
require_once('../fns/all_fns.php');

$friend_name = $_POST['target_name'];

try {
	
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	
	//get the to id
	$friend_id = name_to_id($db, $friend_name);
	
	//delete dat row!
	$db->call('friend_delete', array($user_id, $friend_id));
	
	//return info
	echo 'message='.$friend_name.' has been removed from your friends list.';						
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}