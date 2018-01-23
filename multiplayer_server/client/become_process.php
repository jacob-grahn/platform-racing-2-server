<?php

function client_become_process ($socket, $data) {
  global $PROCESS_PASS, $PROCESS_IP;
	if($data === $PROCESS_PASS && ($socket->ip === $PROCESS_IP || $socket->ip === '127.0.0.1')) {
		$socket->process = true;
	}
}

?>
