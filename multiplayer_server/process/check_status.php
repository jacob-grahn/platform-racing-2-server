<?php

function process_check_status ($socket, $data) {
	$socket->write('ok');
}

?>
