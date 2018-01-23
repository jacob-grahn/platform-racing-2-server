<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/api_fns.php');

try {
	
	api_test_ip();
	
	$name = find('name');
	
	$db = new DB();
	$user_id = name_to_id($db, $name);
	$lux = $db->grab('lux', 'lux_drain', array($user_id));
	
	echo 'lux='.$lux;
}

catch(Exception $e) {
	echo 'error='.($e->getMessage());
}

?>