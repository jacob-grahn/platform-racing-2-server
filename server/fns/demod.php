#!/usr/bin/php
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
