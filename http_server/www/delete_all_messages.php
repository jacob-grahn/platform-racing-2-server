<?php

require_once('../fns/all_fns.php');
$token = mysqli_real_escape_string($_GET['token']);

try {
	$db = new DB();
	$user_id = use_login_token($db, $token);
	
	$db->call( 'messages_delete_all', array($user_id) );
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
	exit;
}

?>
