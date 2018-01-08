#!/usr/bin/php
<?php

require_once(__DIR__ . '/../fns/db_fns.php');

$port = $argv[1];
$name = $argv[2];
$type = $argv[3];
$admin = $argv[4]; // player passed as argument for use with error/confirmation messages
$caught_exception = false;

// if the user isn't an admin, kill the script
if($admin->group != 3) {
	echo $admin->name." lacks the power to promote. Quitting...";
	$admin->write("message`Error: You lack the power to promote $name to a $type moderator.");
	exit;
}

try {

	//sanity check
	if($type != 'trial' && $type != 'permanent') {
		throw new Exception('Invalid moderator type.');
	}

	$connection = user_connect();
	$user_id = name_to_id($connection, $name);
	$safe_user_id = addslashes($user_id);
	$safe_type = addslashes($type);
	$safe_time = addslashes(time());
	$safe_min_time = addslashes(time()-(60*60*6));

	//throttle mod promotions
	$result = $connection->query("SELECT COUNT(*) as recent_promotion_count
									FROM promotion_log
									WHERE power > 1
									AND time > $safe_min_time");
	if(!$result) {
		throw new Exception('Could not check for recent promotions.');
	}

	$row = $result->fetch_object();
	if($row->recent_promotion_count > 0) {
		throw new Exception('Someone has already been promoted to a moderator recently. Wait a bit before trying to promote again.');
	}
	
	//check for guest
	$result = $connection->query("SELECT *
									FROM users
									WHERE user_id = '$safe_user_id'
									LIMIT 0,1");
	$row = $result->fetch_object();
	if($row->power < 1) {
		throw new Exception('Guests can\'t be promoted to moderators.');
	}

	//log the power change
	$result = $connection->query("INSERT INTO promotion_log
								 	SET message = 'user_id: $safe_user_id has been promoted to $safe_type moderator',
										power = 2,
										time = '$safe_time'");
	if(!$result) {
		throw new Exception('Could not record the promotion.');
	}

	//do the power change
	$result = $connection->query("update users
									set power = 2
									where user_id = '$safe_user_id'");
	if(!$result){
		throw new Exception("Could not promote $name to a $type moderator.");
	}

	//set limits
	if($type == 'trial') {
		$max_ban = 60 * 60 * 24;
		$bans_per_hour = 30;
		$can_unpublish_level = 0;
	}
	if($type == 'permanent') {
		$max_ban = 31536000; // 1 year
		$bans_per_hour = 101;
		$can_unpublish_level = 1;
	}

	$safe_max_ban = $connection->real_escape_string($max_ban);
	$safe_bans_per_hour = $connection->real_escape_string($bans_per_hour);
	$safe_can_unpublish_level = $connection->real_escape_string($can_unpublish_level);

	$result = $connection->query("INSERT INTO mod_power
									SET user_id = '$safe_user_id',
										max_ban = '$safe_max_ban',
										bans_per_hour = '$safe_bans_per_hour',
										can_ban_ip = '1',
										can_ban_account = '1',
										can_unpublish_level = '$safe_can_unpublish_level'
									ON DUPLICATE KEY UPDATE
										max_ban = '$safe_max_ban',
										bans_per_hour = '$safe_bans_per_hour',
										can_ban_ip = '1',
										can_ban_account = '1',
										can_unpublish_level = '$safe_can_unpublish_level'");
	if(!$result) {
		throw new Exception('Could not set limits on the new moderator\'s power.');
	}
}

catch(Exception $e){
	$caught_exception = true;
	echo $e->getMessage();
	$admin->write('message`Error: '.$e->getMessage());
	exit;
}

if (!$caught_exception) {
	$admin->write("message`$name has been promoted to a $type moderator!");
	exit;
}

?>
