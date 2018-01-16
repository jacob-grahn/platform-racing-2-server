<?php

require_once( '../fns/all_fns.php' );

try {

	//--- import data
	$guild_id = find( 'guild_id' );


	//--- connect to the db
	$db = new DB();


	//--- check thier login
	$mod = check_moderator($db);


	//--- edit guild in db
	$db->call( 'guild_delete', array($guild_id), 'Could not delete the guild.' );


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
