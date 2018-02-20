<?php
require_once('../fns/all_fns.php');

header("Content-type: text/plain");

$friend_name = $_POST['target_name'];
$safe_friend_name = htmlspecialchars($friend_name);

try {
	
	// connect
	$db = new DB();
	
	//check their login
	$user_id = token_login($db);
	
	//get the new friend's id
	$friend_id = name_to_id($db, $friend_name);
	
	//create the magical one sided friendship
	$db->call('friends_insert', array($user_id, $friend_id));
	
	//tell it to the world
	echo "message=$safe_friend_name has been added to your friends list!";			
}

catch(Exception $e){
	$error = $e->getMessage();
	echo "error=$error";
}

?>
