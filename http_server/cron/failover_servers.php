<?php

require_once(__DIR__ . '/../fns/all_fns.php');

$db = new DB();

failover_servers($db);

echo 'result=ok';


function failover_servers($db) {
	$servers = $db->to_array( $db->call('servers_select') );
	$addresses = array('45.76.24.255');
	foreach($servers as $server) {
		if($server->status == 'down') {
			$fallback_address = $addresses[ array_rand($addresses) ];
			$db->call('server_update_address', array($server->server_id, $fallback_address));
		}
	}
}

?>
