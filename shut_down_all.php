<?php

require_once(__DIR__ . '/multiplayer_server/fns/management_fns.php');
require_once(__DIR__ . '/multiplayer_server/fns/all_fns.php');

$day = date('w');

@$mode = $argv[1];
if( !isset($mode) ) {
	$mode = 'all';
}

output('shutting down servers... ');


//--- load all servers
$db = new DB();
$servers = $db->to_array( $db->call( 'servers_select_all', array() ) );


//--- test all active servers at this address
foreach( $servers as $server ) {
	output( $server->server_name );

	if( (($mode == 'inactive' && $server->active == 0) || ($mode == 'active' && $server->active == 1) || ($mode == 'all')) ) {
		echo "Shutting down $server->server_name ($server->server_id)";
		try{
			$reply = talk_to_server_id( $db, $server->server_id, 'shut_down`', true );
			echo "Reply: $reply\n";
		}
		catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	output('');
	sleep(1);
}


//--- tell it to the world
output('done!');

?>
