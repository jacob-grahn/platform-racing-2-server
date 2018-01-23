<?php

function client_close ($socket, $data) {
	$socket->close();
	$socket->on_disconnect();
}

?>
