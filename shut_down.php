<?php

require_once(__DIR__ . '/multiplayer_server/fns/DB.php');
require_once(__DIR__ . '/multiplayer_server/fns/db_fns.php');
require_once(__DIR__ . '/multiplayer_server/fns/management_fns.php');

$db = new DB();

for($i=1; $i<100; $i++) {
	@$server_id = (int) $argv[$i];

	if(isset($server_id) && $server_id != 0) {
		try{
			$reply = talk_to_server_id( $db, $server_id, 'shut_down`', true );
			echo "Shutting down server $server_id. Reply: $reply\n";
		}
		catch(Exception $e){
			echo $e->getMessage();
		}
	}
	else {
		break;
	}
}

?>
