<?php

require_once( '../fns/all_fns.php' );

try {
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- get thier login
	$user_id = token_login($db, false);
	$account = $db->grab_row( 'user_select_expanded', array($user_id) );
	
	
	//--- sanity check
	if( $account->guild == 0 ) {
		throw new Exception( 'You are not a member of a guild.' );
	}
	
	
	//--- leave the guild
	$db->call( 'guild_increment_member', array( $account->guild, -1 ) );
	$db->call( 'user_update_guild', array( $user_id, 0 ) );
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->message = 'You have left the guild.';
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>
