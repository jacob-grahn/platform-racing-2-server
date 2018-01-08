#!/usr/bin/php
<?php

require_once(__DIR__ . '/../fns/db_fns.php');

$port = $argv[1];
$user_name = $argv[2];

try {
	$connection = user_connect();
	$user_id = name_to_id($connection, $user_name);
	$safe_user_id = addslashes($user_id);


	//delete mod entry
	$result = $connection->query("DELETE FROM mod_power
									WHERE user_id = '$safe_user_id'");
	if(!$result) {
		throw new Exception('could not delete mod row');
	}


	//set power to 1
	$result = $connection->query("UPDATE users
									SET power = 1
									WHERE user_id = '$safe_user_id'");
	if(!$result) {
		throw new Exception('Could not set user power');
	}
}

catch(Exception $e){
	$message = $e->getMessage();
	echo $message;
	exit;
}

?>
