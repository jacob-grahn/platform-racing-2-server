<?php

require_once('../fns/all_fns.php');

try {
	$db = new DB();
	$user_id = token_login($db, false);
	
	$db->call('messages_delete_all', array($user_id) );
	echo 'message=All of your PMs have been deleted!'; // if successful, echo a success message
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
	exit;
}

?>
