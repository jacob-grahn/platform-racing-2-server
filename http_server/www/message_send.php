<?php

require_once('../fns/all_fns.php');
require_once('../fns/pr2_fns.php');

$to_name = $_POST['to_name'];
$message = $_POST['message'];

try {
	
	$db = new DB();
	
	$from_user_id = token_login($db, false);
	$from_name = id_to_name($from_user_id);
	$to_user_id = name_to_id($db, $to_name);
	
	// let admins use the [url=][/url] tags
	if($from_name->power >= 3) {
		$message = preg_replace('/\[url=(.+?)\](.+?)\[\/url\]/', '<a href="\1" target="_blank"><u><font color="#0000FF">\2</font></u></a>', $message);
	}
	
	send_pm( $db, $from_user_id, $to_user_id, $message );
	
	echo 'message=Your message was sent successfully!';		
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

?>
