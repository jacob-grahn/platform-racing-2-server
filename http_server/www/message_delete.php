<?php
require_once('../fns/all_fns.php');

$message_id = $_POST['message_id'];

try{
	
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	$db->call('message_delete', array($user_id, $message_id));
	echo 'success=true';
}
catch(Exception $e){
	echo 'error='.($e->getMessage());
	exit;
}
?>