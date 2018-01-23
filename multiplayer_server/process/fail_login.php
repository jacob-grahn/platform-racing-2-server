<?php

//--- disconnects if the log in failed -------------------------------------
function process_fail_login ($socket, $data) {
	if($socket->process == true){
		global $login_array;

		list($login_id, $message) = explode('`', $data);

		$socket = $login_array[$login_id];
		unset($login_array[$login_id]);

		if(is_object($socket)){
			$socket->write('message`'.$message);
			$socket->on_disconnect();
			$socket->close();
		}
	}
}

?>
