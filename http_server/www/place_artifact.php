<?php

header("Content-type: text/plain");
require_once('../fns/all_fns.php');

$x = find('x', 0);
$y = find('y', 0);
$level_id = find('levelId', 0);

try {
	// sanity check: was a level ID specified?
	if($level_id == 0){
		throw new Exception('No level ID was specified.');
	}
	
	// connect
	$db = new DB();
	
	// check their login
	$user_id = token_login($db);
	
	// sanity check: are they Fred?
	if( $user_id != 1 && $user_id != 4291976 ) {
		throw new Exception( 'You are not Fred.' );
	}
	
	// update the artifact location in the database
	$db->call( 'artifact_location_update', array( $level_id, $x, $y ) );
	
	// tell the world
	echo "message=Great success! The artifact location will be updated at the top of the next minute.";
	
}

catch (Exception $e) {
	$error = $e->getMessage();
	echo "error=$error";
}

?>
