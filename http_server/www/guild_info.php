<?php

header("Content-type: text/plain");

require_once( '../fns/all_fns.php' );
require_once( '../fns/pr2_fns.php' );


try {
	
	//--- import data
	$guild_id = find( 'id', 0 );
	$guild_name = find( 'name', '' );
	$get_members = find( 'getMembers', 'no' );
	
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- sanity check
	if( (!is_numeric($guild_id) || $guild_id <= 0) && $guild_name == '' )  {
		throw new Exception( 'No id or name of guild was provided.' );
	}
	
	
	//--- get guild infos
	if( $guild_id > 0 ) {
		$guild = $db->grab_row( 'guild_select', array($guild_id), 'Could not find a guild with that id.' );
	}
	else {
		$guild = $db->grab_row( 'guild_select_by_name', array($guild_name), 'Could not find a guild by that name.' );
		$guild_id = $guild->guild_id;
	}
	
	
	//--- get members
	$members = array();
	if( $get_members == 'yes' ) {
		$members_result = $db->call( 'guild_select_members', array($guild_id) );
		$members = array();
		while( $row = $members_result->fetch_object() ) {
			$members[] = $row;
		}
	}
	
	
	//--- count active members
	$guild->active_count = guild_count_active( $db, $guild->guild_id );
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->guild = $guild;
	$reply->members = $members;
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>