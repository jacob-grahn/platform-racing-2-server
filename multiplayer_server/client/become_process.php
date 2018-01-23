<?php

function client_become_process ($socket, $data) {
  global $PROCESS_PASS, $PROCESS_IP;
	if($data === $PROCESS_PASS && $socket->ip === $PROCESS_IP) {
		$socket->process = true;
	}
}

?>
