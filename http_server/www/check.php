<?php

require_once('../fns/all_fns.php');

try {
	$db = new DB();

	$user_id = $db->grab('user_id', 'user_select_user_id', array('aaaa'));

	echo 'result=ok';
}

catch(Exception $e) {
	echo 'error='.$e->getMessage();
}

?>
