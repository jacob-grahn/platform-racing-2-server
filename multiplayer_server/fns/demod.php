<?php

require_once(__DIR__ . '/db_fns.php');

function demote_mod($port, $user_name, $admin, $demoted_player) {
	global $db;

	// boolean var for use in if statement @end
	$caught_exception = false;

	// if the user isn't an admin on the server, kill the function (2nd line of defense)
	if($admin->group != 3) {
		$caught_exception = true;
		echo $admin->name." lacks the server power to demote $user_name.";
		$admin->write("message`Error: You lack the power to demote $user_name.");
		return false;
	}

	try {
		$user_id = name_to_id($db, $user_name);
		$safe_admin_id = addslashes($admin->user_id);
		$safe_user_id = addslashes($user_id);
		

		//check for proper permission in the db (3rd + final line of defense before promotion)
		$result = $db->query("SELECT *
										FROM users
										WHERE user_id = '$safe_admin_id'
										LIMIT 0,1");
		$row = $result->fetch_object();
		if($row->power != 3) {
			throw new Exception("You lack the power to demote $user_name.");
		}
		
		
		//check if the person being demoted is a staff member
		$user_result = $db->query("SELECT *
										FROM users
										WHERE user_id = '$safe_user_id'
										LIMIT 0,1");
		$user_row = $result->fetch_object();
		
		//delete mod entry
		$result = $db->query("DELETE FROM mod_power
										WHERE user_id = '$safe_user_id'");
		if(!$result) {
			throw new Exception("Could not delete the moderator type from the database because $user_name isn\'t a moderator.");
		}


		//set power to 1
		$result = $db->query("UPDATE users
										SET power = 1
										WHERE user_id = '$safe_user_id'");
		if(!$result) {
			throw new Exception("Could not demote $user_name due to a database error.");
		}
		
		// if the user was a mod or higher, log it in the action log
		if($user_result->power >= 2) {
		
			//action log
			$ip = $admin->ip;
			$safe_admin_name = addslashes($admin->name);
			$safe_user_name = addslashes($user_name);
			
			//make pretty server names
			$servers = json_decode(file_get_contents('https://pr2hub.com/files/server_status_2.txt'));
			$server_count = count($servers->servers);

			foreach (range(0,$server_count) as $server_id) {
				$server_port = $servers->servers[$server_id]->port;

				if ($port == $server_port) {
					$server_name = $servers->servers[$server_id]->server_name;
					break;
				}
			}
			
			// log action in action log
			$db->call('mod_action_insert', array($admin->user_id, "$safe_admin_name demoted $safe_user_name from $ip on $server_name", $admin->user_id, $ip));
			
		}
		
	}

	catch(Exception $e){
		$caught_exception = true;
		$message = $e->getMessage();
		echo "Error: $message";
		$admin->write("message`Error: $message");
		return false;
	}

	if(!$caught_exception) {
		if(isset($demoted_player) && $demoted_player->group >= 2) {
			$demoted_player->group = 1;
			$demoted_player->write('setGroup`1');
		}
		echo $admin->name." demoted $user_name.";
		$admin->write("message`$user_name has been demoted.");
		return true;
	}

}

?>
