<?php

require_once(__DIR__ . '/db_fns.php');

function promote_server_mod($name, $owner, $promoted) {
	global $db, $guild_owner;
	
	// safety first
	$safe_owner = htmlspecialchars($owner->name);
	$safe_name = htmlspecialchars($name);
	
	// if the user doesn't own the server, kill the function (2nd line of defense)
	if($owner->group < 3 || $owner->server_owner == false || $owner->user_id != $guild_owner) {
		$owner->write("message`Error: You lack the power to promote $safe_name to a server moderator.");
		return false;
	}
  
	if(!isset($promoted)) {
		$owner->write("message`Error: Could not find a user named $safe_name on this server.");
		return false;
	}
	
	// if the player being promoted is an admin or staff member, end the function
	if($promoted->group == 3) {
		$owner->write("message`Error: I'm not sure what would happen if you promoted an admin to a moderator, but it would probably make the world explode.");
		return false;
	}
	
	// get info about the user being promoted
	$user = $db->grab_row('user_select', array($promoted->user_id), 'Could not find a user with that ID.');
	
	// if the person being promoted is a guest, end the function
	if($user->power == 0) {
		$owner->write("message`Error: Guests can't be promoted to server moderators.");
		return false;
	}
	
	// if the person being promoted is an admin, kill the function
	if($user->power == 3) {
		$owner->write("message`Error: I'm not sure what would happen if you promoted an admin to a moderator, but it would probably make the world explode.");
		return false;
	}
	
	// now that we've determined that the user is able to do what they're trying to do, let's finish
	$promoted->become_temp_mod();
	$owner->write("message`$name has been promoted to a server moderator! They'll remain a moderator until you type /demod *their name* or until they log out.");
	if (isset($owner->chat_room)) {
		$owner->chat_room->send_chat("systemChat`$safe_owner has promoted $safe_name to a server moderator! Your private peace-keeping is greatly appreciated! You'll have your mod powers until you log out or are demoted.");
	}
	return true;
	
}

function demote_server_mod($name, $owner, $demoted) {
	global $db, $guild_owner;
	
	// safety first
	$safe_name = htmlspecialchars($name);
	
	// if the user isn't the on the server, kill the function (2nd line of defense)
	if($owner->group < 3 || $owner->server_owner == false || $owner->user_id != $guild_owner) {
		$owner->write("message`Error: You lack the power to promote $safe_name to a server moderator.");
		return false;
	}
	
	// don't let the server owner demote themselves
	if($demoted->user_id == $guild_owner) {
		$owner->write("message`Error: The server owner reigns supreme!");
		return false;
	}
	
	// let the server owner demote temps
	if ($owner->server_owner == true) {
		if (isset($demoted) && $demoted->temp_mod == true) {
			$demoted->group = 1;
			$demoted->write('setGroup`1');
			$demoted->temp_mod = false;
			$owner->write("message`$safe_name has been demoted.");
			return true;
		}
		else {
			$owner->write("message`Could not find a server moderator with the name \"$safe_name\" on this server.");
			return false;
		}
	}
}

?>
