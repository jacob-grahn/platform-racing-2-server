<?php

require_once(__DIR__ . '/../fns/all_fns.php');
require_once(__DIR__ . '/../fns/shell_output_fns.php');

$day = date('w');

//--- load all servers
$db = new DB();
$servers = $db->to_array( $db->call( 'servers_select_all', array() ) );


//--- test all active servers at this address
foreach( $servers as $server ) {

	if( true ) { //$server->server_id % 7 == $day
		output( "Shutting down $server->server_name ($server->server_id)" );
		try {
			$reply = talk_to_server( $server->address, $server->port, $server->salt, 'shut_down`', true );
			output( "Reply: $reply" );
		}
		catch(Exception $e) {
			output( $e->getMessage() );
		}
	}
	else {
		output( "Ignoring $server->server_name" );
	}

	output('');
}


//--- tell it to the world
output( 'done' );

?>
