<?php

require_once( '../fns/all_fns.php' );

$ip = get_ip();

try {

	//--- import data
	$guild_id = find( 'guild_id' );


	//--- connect to the db
	$db = new DB();


	//--- check their login
	$mod = check_moderator($db);


	//--- edit guild in db
	$db->call( 'guild_delete', array($guild_id), 'Could not delete the guild.' );
	
	
	$power = $mod->power;
	
	
	if ($power >= 2) {
	
		//htmlspecialchars
		$html_name = htmlspecialchars($mod->name);
		$html_guild_id = htmlspecialchars($guild_id);
	
		//record the deletion in the action log
		$db->call('mod_action_insert', array($user_id, "$html_name deleted guild $html_guild_id from $ip.", $user_id, $ip));
	
	}


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
