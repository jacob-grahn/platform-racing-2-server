<?php

require_once( '../fns/all_fns.php' );

try {
	
	//--- import data
	$note = find( 'note' );
	$guild_name = find( 'name' );
	$emblem = find( 'emblem' );
	
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- check thier login
	$user_id = token_login($db, false);
	$account = $db->grab_row( 'user_select_expanded', array( $user_id ) );
	$guild = $db->grab_row( 'guild_select', array( $account->guild ) );
	
	
	//--- sanity check
	if( $account->power <= 0 ) {
		throw new Exception( 'Guests can not edit guilds.' );
	}
	if( $account->guild == 0 ) {
		throw new Exception( 'You are not a member of a guild.' );
	}
	if( $guild->owner_id != $user_id ) {
		throw new Exception( 'You are not the owner of this guild.' );
	}
	if( !isset( $note ) ) {
		throw new Exception( 'Note not recieved.' );
	}
	if( !isset( $guild_name ) ) {
		throw new Exception( 'Guild name not recieved.' );
	}
	if( !isset( $emblem ) ) {
		throw new Exception( 'Emblem not recieved.' );
	}
	if( preg_match( '/.jpg$/', $emblem) !== 1 || preg_match( '/\.\.\//', $emblem) === 1 || preg_match( '/\?/', $emblem) === 1) {
	    throw new Exception('Emblem invalid');
	}
	
	
	
	//--- edit guild in db
	$db->call( 'guild_update', array( $guild->guild_id, $guild_name, $emblem, $note ), 'A guild already exists with that name.' );
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->message = 'Guild edited successfully!';
	$reply->guildId = $guild->guild_id;
	$reply->emblem = $emblem;
	$reply->guildName = $guild_name;
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>
