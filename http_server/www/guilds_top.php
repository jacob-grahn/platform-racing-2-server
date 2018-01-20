<?php

header("Content-Type: text/plain;charset=UTF-8");

require_once( '../fns/all_fns.php' );
require_once( '../fns/pr2_fns.php' );

try {
	
	//--- import data
	$sort = find('sort', 'gpToday');
	
	
	//--- connect to the db
	$db = new DB();
	
	
	//--- sanity check
	$allowed_sort_values = array( 'gpToday', 'gpTotal', 'members', 'activeMembers' );
	if( array_search( $sort, $allowed_sort_values ) === false ) {
		throw new Exception( 'Unexpected sort value' );
	}
	
	
	//--- select list from db
	$guilds = $db->to_array( $db->call('guilds_select_by_most_gp_today') );
	
	
	//--- get active member count guild by guild
	//--- also disable html parsing
	foreach( $guilds as $guild ) {
		$guild->active_count = guild_count_active( $db, $guild->guild_id );
		$guild->guild_name = htmlspecialchars($guild->guild_name);
	}
	
	
	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->guilds = $guilds;
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>
