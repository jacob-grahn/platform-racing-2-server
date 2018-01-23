<?php
require_once('../fns/all_fns.php');

$friend_name = $_POST['target_name'];

try{
	
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	
	//get the to id
	$friend_id = name_to_id($db, $friend_name);
	
	//create the magical one sided friendship
	$db->call('friends_insert', array($user_id, $friend_id));
	
	//tell it to the world
	echo 'message='.$friend_name.' has been added to your friends list!';			
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

?>