<?php
require_once('../fns/all_fns.php');

$target_name = $_POST['target_name'];

try{
	
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	
	//get the to id
	$target_id = name_to_id($db, $target_name);
	
	//create the magical one sided friendship
	$db->call('ignored_insert', array($user_id, $target_id));
	
	//tell it to the world
	echo 'message='.$target_name.' has been ignored. You won\'t recieve any chat or private messages from them.';			
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
	exit;
}