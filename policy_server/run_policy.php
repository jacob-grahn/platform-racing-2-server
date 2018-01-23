<?

ini_set('mbstring.func_overload', '0');
ini_set('output_handler', '');
error_reporting(E_ALL | E_STRICT);
@ob_end_flush();
set_time_limit(0);

require_once(__DIR__ . '/socket/socket.php');
require_once(__DIR__ . '/server.php');

//start the socket server
$daemon = new socketDaemon();
$server = $daemon->create_server('server', 'server_client', 0, 843);
$daemon->process();

?>
