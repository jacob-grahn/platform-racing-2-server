#!/usr/bin/php
<?php

require_once(__DIR__ . '/../fns/db_fns.php');

$port = $argv[1];
$user_name = $argv[2];
$admin = $argv[3]; // player passed as argument for use with confirmation/error messages
$caught_exception = false;

// if the user isn't an admin, kill the script
if($admin->group != 3) {
	echo $admin->name." lacks the power to demote. Quitting...";
	$admin->write("message`Error: You lack the power to demote $user_name.");
	exit;
}

try {
	$connection = user_connect();
	$user_id = name_to_id($connection, $user_name);
	$safe_user_id = addslashes($user_id);


	//delete mod entry
	$result = $connection->query("DELETE FROM mod_power
									WHERE user_id = '$safe_user_id'");
	if(!$result) {
		throw new Exception('Could not delete the moderator type from database.');
	}


	//set power to 1
	$result = $connection->query("UPDATE users
									SET power = 1
									WHERE user_id = '$safe_user_id'");
	if(!$result) {
		throw new Exception("Could not demote $user_name to a member.");
	}
}

catch(Exception $e){
	$caught_exception = true;
	$message = $e->getMessage();
	echo $message;
	$admin->write("message`Error: $message");
	exit;
}

if(!$caught_exception) {
	$admin->write("message`$user_name has been successfully demoted to a member.");
	exit;
}

exit;

?>
