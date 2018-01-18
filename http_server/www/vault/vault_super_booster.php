<?php

header("Content-type: text/plain");

require_once( '../../fns/all_fns.php' );

$server_id = find('server_id');

try {

	//--- sanity check
	if( !isset( $server_id ) || !is_numeric( $server_id ) || $server_id <= 0 ) {
		throw new Exception( 'Invalid server id' );
	}


	//--- connect
	$db = new DB();


	//--- gather infos
	$user_id = token_login($db);
	$server = $db->grab_row( 'server_select', array( $server_id ) );


	//--- remember that the super booster was used
	$key = "sb-$user_id";
	if( apcu_exists( $key ) ) {
		throw new Exception( 'Super Booster can only be used once per day.' );
	}
	else {
		$result = apcu_add( $key, true, 86400 );
		if( !$result ) {
			throw new Exception( 'Could not store usage.' );
		}
	}


	//--- send a message to the player's server giving them a super boost
	talk_to_server( $server->address, $server->port, $server->salt, "unlock_super_booster`$user_id", false);


	//--- reply
	$r = new stdClass();
	$r->success = true;
	$r->message = 'Super Booster active! You will start your next race with +10 speed, +10 jump, and +10 acceleration.';
	echo json_encode( $r );
}

catch(Exception $e) {
	$r = new stdClass();
	$r->error = $e->getMessage();
	echo json_encode( $r );
}



?>
