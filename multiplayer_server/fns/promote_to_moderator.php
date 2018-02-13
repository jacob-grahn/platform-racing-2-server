<?php

require_once(__DIR__ . '/db_fns.php');

function promote_mod($port, $name, $type, $admin, $promoted_player) {
	global $db;
	global $server_name;
	
	// if the user isn't an admin on the server, kill the function (2nd line of defense)
	if($admin->group != 3) {
		echo $admin->name." lacks the server power to promote $name to a $type moderator.";
		$admin->write("message`Error: You lack the power to promote $name to a $type moderator.");
		return false;
	}
	
	// define variables needed
	$user_id = name_to_id($db, $name);
	$safe_admin_id = addslashes($admin->user_id);
	$safe_user_id = addslashes($user_id);
	$safe_type = addslashes($type);
	$safe_time = addslashes(time());
	$safe_min_time = addslashes(time()-(60*60*6));
	
	// get info about the person promoting
	$admin_result = $db->query("SELECT *
									FROM users
									WHERE user_id = '$safe_admin_id'
									LIMIT 0,1");
	$admin_row = $admin_result->fetch_object();
	
	//check for proper permission in the db (3rd + final line of defense before promotion)
	if($admin_row->power != 3) {
		echo $admin->name." lacks the database power to promote $name to a $type moderator.";
		$admin->write("message`Error: You lack the power to promote $name to a $type moderator.");
		return false;
	}
	
	// get info about the person being promoted
	$user_result = $db->query("SELECT *
									FROM users
									WHERE user_id = '$safe_user_id'
									LIMIT 0,1");
	$user_row = $user_result->fetch_object();
	
	// if the person being promoted doesn't exist, end the function
	if (!$user_result) {
		echo $admin->name." tried to promote a user that doesn't exist ($name) to a $type moderator.";
		$admin->write("message`Error: $name doesn't exist.");
		return false;
	}
	
	// if the person being promoted is a guest, end the function
	if($user_row->power < 1) {
		echo $admin->name." tried to promote a guest ($name) to a $type moderator.";
		$admin->write("message`Error: Guests can't be promoted to moderators.");
		return false;
	}
	
	// now that we've determined that the user is able to do what they're trying to do, let's finish
	// if type is trial or permanent, do promotion things in the db
	if ($type == 'trial' || $type == 'permanent') {
		
		try {
		
			//throttle mod promotions
			$result = $db->query("SELECT COUNT(*) as recent_promotion_count
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
			
			//log the power change
			$result = $db->query("INSERT INTO promotion_log
										 	SET message = 'user_id: $safe_user_id has been promoted to $safe_type moderator',
												power = 2,
												time = '$safe_time'");
			if(!$result) {
				throw new Exception('Could not record the promotion in the database.');
			}
			
			//do the power change
			$result = $db->query("update users
											set power = 2
											where user_id = '$safe_user_id'");
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
			
			$safe_max_ban = $db->real_escape_string($max_ban);
			$safe_bans_per_hour = $db->real_escape_string($bans_per_hour);
			$safe_can_unpublish_level = $db->real_escape_string($can_unpublish_level);
			$result = $db->query("INSERT INTO mod_power
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
			
			//action log
			$ip = $admin->ip;
			$admin_name = $admin->name;
			$admin_id = $admin->user_id;
			$promoted_name = $name;
			
			// log action in action log
			$db->call('admin_action_insert', array($admin_id, "$admin_name promoted $promoted_name to a $type moderator from $ip on $server_name.", $admin_id, $ip));
			
			if(isset($promoted_player) && $promoted_player->group != 0){
				$promoted_player->group = 2;
				$promoted_player->write('setGroup`2');
			}
			echo $admin->name." promoted $name to a $type moderator.";
			$admin->write("message`$name has been promoted to a $type moderator!");
			return true;
			
		}
		catch(Exception $e){
			echo "Error: ".$e->getMessage();
			$admin->write('message`Error: '.$e->getMessage());
			return false;
		}
	} // end if trial/permanent
	
	elseif ($type == 'temporary') {
		
		try {
			if(isset($promoted_player) && $promoted_player->group != 0){
				$promoted_player->become_temp_mod();
			}
			
			return true;
		}
		catch(Exception $e){
			echo "Error: ".$e->getMessage();
			$admin->write('message`Error: '.$e->getMessage());
			return false;
		}

	} // end if temp
	
	else {
		$admin->write('message`Error: Unknown moderator type specified.');
		return false;
	} // if the type wasn't trial, perma, or temp, then something's wrong. Kill the function.
	
}

?>
