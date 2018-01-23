<?php

require_once( '../fns/all_fns.php' );

try {
	
	
	//--- import info
	$guild_id = find( 'guildId' );
	
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- gather information
	$user_id = token_login($db, false);
	$account = $db->grab_row( 'user_select_expanded', array( $user_id ) );
	$guild = $db->grab_row( 'guild_select', array( $guild_id ) );
	
	
	//--- sanity check
	if( $account->guild != 0 ) {
		throw new Exception( 'You are already a member of a guild.' );
	}
	if( $guild->member_count >= 100 ) {
		throw new Exception( 'This guild is full.' );
	}
	$db->grab_row( 'guild_invitation_select', array( $guild_id, $user_id ), 'This invitation has expired.' );
	
	
	//--- join the guild
	$db->call( 'guild_invitation_delete', array( $guild_id, $user_id ) );
	$db->call( 'guild_increment_member', array( $guild_id, 1 ) );
	$db->call( 'user_update_guild', array( $user_id, $guild_id ) );
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->message = 'Welcome to '.$guild->guild_name.'!';
	$reply->guildId = $guild->guild_id;	
	$reply->guildName = $guild->guild_name;
	$reply->emblem = $guild->emblem;
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>