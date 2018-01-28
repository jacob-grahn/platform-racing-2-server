<?php

require_once('../fns/all_fns.php');

try{
	
	//connect to the db
	$db = new DB();
	
	//check their login
	$user_id = token_login($db);
	
	//get their username
	$user = $db->grab_row('user_select', array($user_id));
	
	if($user->power == 0) {
		throw new Exception('you are logged in as a guest');
	}
	
	//tell it to the world
	echo 'user_name='.urlencode($user->name).'&guild_id='.urlencode($user->guild);
}

catch(Exception $e) {
	echo 'user_name=';
}

?>
