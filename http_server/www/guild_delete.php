<?php

require_once( '../fns/all_fns.php' );

try {

	//--- import data
	$guild_id = find( 'guild_id' );


	//--- connect to the db
	$db = new DB();


	//--- check their login
	$mod = check_moderator($db);
	
	//--- check if the guild exists
	$guild = $db->grab_row( 'guild_select', array($guild_id), 'Could not find a guild with that id.' );

	//--- edit guild in db
	$db->call( 'guild_delete', array($guild_id), 'Could not delete the guild.' );
	
	//htmlspecialchars
	$mod_name = $mod->name;
	$ip = $mod->ip;
	$guild_name = $guild->name;
	$guild_note = $guild->note;
	$guild_owner = $guild->owner_id;
	
	//record the deletion in the action log
	$db->call('mod_action_insert', array($user_id, "$mod_name deleted guild $guild_id from $ip {name: $guild_name, note: $guild_note, owner_id: $guild_owner}", $user_id, $ip));


	//--- tell it to the world
	$reply = new stdClass();
	$reply->success = true;
	$reply->message = 'Guild deleted.';
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

?>
