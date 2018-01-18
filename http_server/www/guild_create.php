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
	$account = $db->grab_row( 'user_select_expanded', array($user_id) );
	
	
	//--- sanity check
	if( $account->rank < 20 ) {
		throw new Exception( 'You must be rank 20 or above to create a guild.' );
	}
	if( $account->power <= 0 ) {
		throw new Exception( 'Guests can not create guilds.' );
	}
	if( $account->guild != 0 ) {
		throw new Exception( 'You are already a member of a guild.' );
	}
	if( !isset( $note ) ) {
		throw new Exception( 'You need a guild prose.' );
	}
	if( !isset( $guild_name ) ) {
		throw new Exception( 'Your guild needs a name.' );
	}
	if( !isset( $emblem ) ) {
		throw new Exception( 'Your guild needs an emblem.' );
	}
	if( preg_match( '/.jpg$/', $emblem) !== 1 || preg_match( '/\.\.\//', $emblem) === 1 || preg_match( '/\?/', $emblem) === 1) {
		throw new Exception('Emblem invalid');
	}
	if( preg_match( "/^[a-zA-Z0-9\s-]+$/", $guild_name) !== 1) {
		throw new Exception('Guild name invalid; a-z, 0-9, space, and - are the only allowed characters.');
	}
	
	
	//--- add guild to db
	$guild_id = $db->grab( 'guild_id', 'guild_insert', array( $user_id, $guild_name, $emblem, $note ), 'A guild already exists with that name.' );	
	$db->call( 'user_update_guild', array( $user_id, $guild_id ) );
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->message = 'Congratulations on starting your own guild! What an auspicious day!';
	$reply->guildId = $guild_id;
	$reply->emblem = $emblem;
	$reply->guildName = $guild_name;
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = 'Error: '$e->getMessage();
	echo json_encode( $reply );
}

?>
