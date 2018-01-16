<?php

require_once('../fns/all_fns.php');

$x = find('x', 0);
$y = find('y', 0);
$level_id = find('levelId', 0);

try {
	//error check
	if($level_id == 0){
		throw new Exception('No level id was given.');
	}
	
	//connect
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	if( $user_id != 1 && $user_id != 4291976 ) {
		throw new Exception( 'You are not Fred.' );
	}
	
	$db->call( 'artifact_location_update', array( $level_id, $x, $y ) );
}

catch(Exception $e) {
	echo 'error='.$e->getMessage();
}

?>