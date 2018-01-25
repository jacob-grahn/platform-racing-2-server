<?php

require_once( '../fns/all_fns.php' );

try {
	
	//--- import data
	$note = filter_swears(find('note'));
	$guild_name = filter_swears(find('name'));
	$emblem = filter_swears(find('emblem'));
	
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- check thier login
	$user_id = token_login($db, false);
	$account = $db->grab_row( 'user_select_expanded', array( $user_id ) );
	$guild = $db->grab_row( 'guild_select', array( $account->guild ) );
	
	//--- sanity check
	if( $account->power <= 0 ) {
		throw new Exception( 'Guests cannot edit guilds.' );
	}
	if( $account->guild == 0 ) {
		throw new Exception( 'You are not a member of a guild.' );
	}
	if( $guild->owner_id != $user_id ) {
		throw new Exception( 'You are not the owner of this guild.' );
	}
	if( !isset( $note ) ) {
		throw new Exception( 'Your guild needs a prose.' );
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
	if( strlen(trim($guild_name)) === 0 ) {
		throw new Exception('I\'m not sure what would happen if you didn\'t enter a guild name, but it would probably destroy the world.');
	}
	
	
	
	//--- edit guild in db
	$db->call( 'guild_update', array( $guild->guild_id, $guild_name, $emblem, $note, $guild->owner_id ), 'A guild already exists with that name.' );
	
	
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
