<?php

require_once('../fns/all_fns.php');
require_once('../fns/pr2_fns.php');

$to_name = $_POST['to_name'];
$message = $_POST['message'];

try {
	
	$db = new DB();
	
	$from_user_id = token_login($db, false);
	$to_user_id = name_to_id($db, $to_name);
	
	send_pm( $db, $from_user_id, $to_user_id, $message );
	
	echo 'message=Your message was sent successfully!';		
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

?>
