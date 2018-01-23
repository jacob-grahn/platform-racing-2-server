<?php

function client_ping ($socket, $data) {
	$socket->write( 'ping`' . time() );
}

?>
