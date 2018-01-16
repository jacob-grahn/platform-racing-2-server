<?php

require_once( '../fns/all_fns.php' );

try {
	
	//--- import data
	$target_id = find( 'userId' );
	
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- gather info
	$user_id = token_login($db, false);
	$account = $db->grab_row( 'user_select_expanded', array( $user_id ) );
	$target_account = $db->grab_row( 'user_select_expanded', array( $target_id ) );
	$guild = $db->grab_row( 'guild_select', array( $account->guild ) );
	
	
	//--- sanity check
	if( $account->guild == 0 ) {
		throw new Exception( 'You are not a member of a guild.' );
	}
	if( $guild->owner_id != $user_id ) {
		throw new Exception( 'You are not the owner of this guild.' );
	}
	if( $target_account->guild != $account->guild ) {
		throw new Exception( 'They are not in your guild.' );
	}
	if( $user_id == $target_id ) {
		throw new Exception( 'Do not kick your self, yo.' );
	}
	if( !isset( $target_id ) ) {
		throw new Exception( 'user id not received.' );
	}
	
	
	//--- edit guild in db
	$db->call( 'user_update_guild', array( $target_id, 0 ) );
	$db->call( 'guild_increment_member', array( $guild->guild_id, -1 ) );
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->message = 'User kicked!';
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>
