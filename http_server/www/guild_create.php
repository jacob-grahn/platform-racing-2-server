<?php

require_once( '../fns/all_fns.php' );

try {
	
	//--- import data
	$note = find( 'note' );
	$guild_name = find( 'name' );
	$emblem = find( 'emblem' );
	$noexploit = preg_replace("/[^a-zA-Z0-9#-.:;=?@~! ]/", "", $guild_name);

	
	//--- connect to the db
	$db = new DB();
	
	
	//--- check thier login
	$user_id = token_login($db, false);
	$account = $db->grab_row( 'user_select_expanded', array($user_id) );
	
	
	//--- sanity check
	if( $account->rank < 20 ) {
		throw new Exception( 'Must be rank 20 or above to create a guild.' );
	}
	if( $account->power <= 0 ) {
		throw new Exception( 'Guests can not create guilds.' );
	}
	if( $account->guild != 0 ) {
		throw new Exception( 'You are already a member of a guild.' );
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
        if($noexploit != $guild_name){
        throw new Exception('There is an invalid character in your guild name. '
                            .'The allowed characters are a-z, 1-9, and !#$%&()*+.:;=?@~.');
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
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>
