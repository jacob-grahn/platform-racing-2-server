#!/usr/bin/php
<?php

require_once(__DIR__ . '/../fns/db_fns.php');

function promote_mod($port, $name, $type, $admin) {
	
	// boolean var for use in if statement @end
	$caught_exception = false;

	// if the user isn't an admin on the server, kill the function (2nd line of defense)
	if($admin->group != 3) {
		$caught_exception = true;
		echo $admin->name." lacks the power to promote. Quitting...";
		$admin->write("message`Error: You lack the power to promote $name to a $type moderator.");
		return false;
	}

	try {
	
		//sanity check
		if($type != 'trial' && $type != 'permanent') {
			throw new Exception('Invalid moderator type.');
		}
	
		$connection = user_connect();
		$user_id = name_to_id($connection, $name);
		$safe_admin_id = addslashes($admin->user_id);
		$safe_user_id = addslashes($user_id);
		$safe_type = addslashes($type);
		$safe_time = addslashes(time());
		$safe_min_time = addslashes(time()-(60*60*6));
	
		//throttle mod promotions
		$result = $connection->query("SELECT COUNT(*) as recent_promotion_count
										FROM promotion_log
										WHERE power > 1
										AND time > $safe_min_time",
										MYSQLI_ASYNC);
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
										LIMIT 0,1",
										MYSQLI_ASYNC);
		$row = $result->fetch_object();
		if($row->power < 1) {
			throw new Exception('Guests can\'t be promoted to moderators.');
		}
		
		//check for proper permission in the db (3rd + final line of defense before promotion)
		$result = $connection->query("SELECT *
										FROM users
										WHERE user_id = '$safe_admin_id'
										LIMIT 0,1",
										MYSQLI_ASYNC);
		$row = $result->fetch_object();
		if($row->power != 3) {
			throw new Exception("You lack the power to promote $name to a $type moderator.");
		}
	
		//log the power change
		$result = $connection->query("INSERT INTO promotion_log
									 	SET message = 'user_id: $safe_user_id has been promoted to $safe_type moderator',
											power = 2,
											time = '$safe_time'",
											MYSQLI_ASYNC);
		if(!$result) {
			throw new Exception('Could not record the promotion in the database.');
		}
	
		//do the power change
		$result = $connection->query("update users
										set power = 2
										where user_id = '$safe_user_id'",
										MYSQLI_ASYNC);
		if(!$result){
			throw new Exception("Could not promote $name to a $type moderator.");
		}
	
		//set power limits
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
											can_unpublish_level = '$safe_can_unpublish_level'",
											MYSQLI_ASYNC);
		if(!$result) {
			throw new Exception('Could not set limits on the new moderator\'s power.');
		}
	}
	
	catch(Exception $e){
		$caught_exception = true;
		echo $e->getMessage();
		$admin->write('message`Error: '.$e->getMessage());
		return false;
	}
	
	if (!$caught_exception) {
		echo $admin->name." promoted $name to a $type moderator.";
		$admin->write("message`$name has been promoted to a $type moderator!");
		return true;
	}

}

?>
