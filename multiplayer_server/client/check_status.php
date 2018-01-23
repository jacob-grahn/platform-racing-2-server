<?php

function client_check_status ($socket, $data) {
	$socket->write('ok');
}

?>
