<?php

header("Content-type: text/plain");

require_once( '../fns/all_fns.php' );
require_once( '../fns/pr2_fns.php' );

$guild_id = find_no_cookie('id', 0);
$guild_name = find_no_cookie('name', '');
$get_members = find_no_cookie('getMembers', 'no');
$ip = get_ip();

try {
	
	// rate limiting
	rate_limit('guild-info-'.$ip, 5, 1);
	rate_limit('guild-info'.$ip, 60, 10);
	
	
	// connect
	$db = new DB();
	
	
	// sanity check: was any information requested?
	if((!is_numeric($guild_id) || $guild_id <= 0) && $guild_name == '')  {
		throw new Exception('No guild name or ID was provided.');
	}
	
	
	// get guild infos
	if( $guild_id > 0 ) {
		$guild = $db->grab_row( 'guild_select', array($guild_id), 'Could not find a guild with that id.' );
	}
	else {
		$guild = $db->grab_row( 'guild_select_by_name', array($guild_name), 'Could not find a guild by that name.' );
		$guild_id = $guild->guild_id;
	}
	
	// check for .j instead of .jpg on the end of the emblem file name
	if(substr($guild->emblem, -2) == '.j') {
		$guild->emblem = str_replace('.j', '.jpg', $guild->emblem); 
	}
	
	
	// get members
	$members = array();
	if( $get_members == 'yes' ) {
		$members_result = $db->call( 'guild_select_members', array($guild_id) );
		$members = array();
		while( $row = $members_result->fetch_object() ) {
			$members[] = $row;
		}
	}
	
	
	// count active members
	$guild->active_count = guild_count_active( $db, $guild->guild_id );
	
	
	// tell it to the world
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
