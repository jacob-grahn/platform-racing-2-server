#!/usr/bin/php
<?php

require_once('/home/jiggmin/pr2/server/fns/db_fns.php');

$port = $argv[1];
$name = $argv[2];
$type = $argv[3];

try {
	
	//sanity check
	if($type != 'trial' && $type != 'permanent') {
		throw new Exception('Invalid mod type');
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
		throw new Exception('Could not check promotion throttle.');
	}

	$row = $result->fetch_object();
	if($row->recent_promotion_count > 0) {
		throw new Exception('Someone has already been promoted to moderater recently.');
	}
	
	//log the power change
	$result = $connection->query("INSERT INTO promotion_log
								 	SET message = 'user_id: $safe_user_id has been promoted to $safe_type moderator',
										power = 2,
										time = '$safe_time'");
	if(!$result) {
		throw new Exception('Could not record power change.');
	}
	
	//do the power change
	$result = $connection->query("update users
									set power = 2
									where user_id = '$safe_user_id'");
	if(!$result){
		throw new Exception("Could not promote $user_id to moderator");
	}
	
	//set limits
	if($type == 'trial') {
		$max_ban = 60 * 60 * 24;
		$bans_per_hour = 30;
		$can_unpublish_level = 0;
	}
	if($type == 'permanent') {
		$max_ban = 31536000;//1 year
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
		throw new Exception('Could not set limits');
	}
}

catch(Exception $e){
	echo $e->getMessage();
	exit;
}

?>