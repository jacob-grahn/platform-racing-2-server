<?php

require_once('../fns/all_fns.php');

try {
	$db = new DB();
	$user_id = token_login($db);
	
	$db->call('messages_delete_all', array($user_id) );
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
	exit;
}

?>
