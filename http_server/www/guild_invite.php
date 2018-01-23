<?php

require_once( '../fns/all_fns.php' );
require_once( '../fns/pr2_fns.php' );

try {
	
	//--- import data
	$target_id = find( 'userId' );
	
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- gather info
	$user_id = token_login($db, false);
	$account = $db->grab_row( 'user_select_expanded', array( $user_id ), 'Could not find a row for you.' );
	$guild = $db->grab_row( 'guild_select', array( $account->guild ) );
	$target_account = $db->grab_row( 'user_select_expanded', array( $target_id ), 'Could not find this user.' );
	
	
	//--- sanity check
	if( $account->guild == 0 ) {
		throw new Exception( 'You are not a member of a guild.' );
	}
	if( $guild->owner_id != $user_id ) {
		throw new Exception( 'You are not the owner of this guild.' );
	}
	if( $target_account->guild != 0 ) {
		throw new Exception( 'They are already in a guild.' );
	}
	if( $user_id == $target_id ) {
		throw new Exception( 'Do not invite yourself, yo.' );
	}
	if( !isset( $target_id ) ) {
		throw new Exception( 'user id not received.' );
	}
	
	
	//--- compose an eloquent invitation
	$pm_safe_guild_name = preg_replace("/[^a-zA-Z0-9 ]/", "_", $guild->guild_name);
	$message = "Hi $target_account->name, You've been invited to join our guild, [guildlink=$guild->guild_id]". $pm_safe_guild_name ."[/guildlink]. Click [invitelink=$guild->guild_id]here[/invitelink] to accept.";
	
	
	//--- add the invitation to the db
	send_pm( $db, $user_id, $target_id, $message );
	$db->call( 'guild_invitation_insert', array( $guild->guild_id, $target_id ), 'Could not register the invitation.' );
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->message = 'Your invitation has been sent.';
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>
