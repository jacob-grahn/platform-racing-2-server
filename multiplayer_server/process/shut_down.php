<?php

//--- shutdown ----------------------------------------------------------------
function process_shut_down ($socket, $data) {
	if($socket->process == true){
		output('received shut down command...');
		$socket->write('shuting_down');
		shutdown_server();
	}
}

?>
