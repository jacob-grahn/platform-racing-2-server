<?php


require_once('../../fns/all_fns.php');
require_once('../../fns/api_fns.php');

try {
	
	api_test_ip();
	
	$code = htmlspecialchars( find('code') );
	$name = find('name');
	$message = 'code: ' . $code;
	
	if(strlen($code) != 32) {
		throw new Exception('bade code length');
	}
	
	$db = new DB();
	$user_id = name_to_id($db, $name);
	$db->call('message_insert', array($user_id, 1, $message, 'no-ip'));
	
	echo 'success';
}

catch(Exception $e) {
	echo 'error='.($e->getMessage());
}

?>