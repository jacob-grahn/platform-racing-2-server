#!/usr/bin/php

<?php

require_once('/home/jiggmin/pr2/server/fns/management_fns.php');
require_once('/home/jiggmin/pr2/server/fns/all_fns.php');

$my_ip = exec('/home/jiggmin/pr2/get_server_ip');

output('testing if servers are running on server '.$my_ip.'... ');


//--- test the policy server
test_server('/home/jiggmin/pr2/policy_server/run_policy.php', 'localhost', 843, 'QHE0NSNwKWZZQVEhU19xMA==', 0);


//--- load all active servers
$db = new DB();
$servers = $db->to_array( $db->call( 'servers_select', array() ) );


//--- test all active servers at this address
foreach( $servers as $server ) {
	output( $server->server_name );
	output( $server->address );
	output( $my_ip );
	if($server->address == $my_ip) {
		test_server('/home/jiggmin/pr2/server/pr2.php', 'localhost', $server->port, $server->salt, $server->server_id);
	}
}


//--- tell it to the world
output('done!');

?>
