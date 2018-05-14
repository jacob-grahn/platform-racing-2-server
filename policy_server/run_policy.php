<?php

namespace jiggmin\ps;

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

require_once SOCKET_DAEMON_FILES;
require_once ROOT_DIR . '/policy_server/server.php';
require_once ROOT_DIR . '/policy_server/server_client.php';

// start the policy server
$daemon = new \chabot\SocketDaemon();
$server = $daemon->createServer('jiggmin\ps\server', 'jiggmin\ps\serverClient', 0, 843);
$daemon->process();
