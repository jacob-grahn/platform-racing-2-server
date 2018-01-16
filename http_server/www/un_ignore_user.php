<?php
require_once('../fns/all_fns.php');

$target_name = $_POST['target_name'];

try{
	
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	
	//get the to id
	$target_id = name_to_id($db, $target_name);
	
	//delete dat row!
	$db->call('ignored_delete', array($user_id, $target_id));
	
	//return status
	echo 'message='.$target_name.' has been un-ignored. You will now recieve any chat or private messages they send you.';						
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

?>