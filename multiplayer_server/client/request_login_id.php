<?php

function client_request_login_id ($socket, $data) {
	if(!isset($socket->login_id)) {
		global $login_array;
		$socket->login_id = get_login_id();
		$login_array[$socket->login_id] = $socket;
		$socket->write('setLoginID`'.$socket->login_id);
	}
}

?>
