<?php

//--- outputs a message from a process --------------------------------------
function process_process_message ($socket, $data) {
	if($socket->process == true){
		output($data);
	}
}

?>
