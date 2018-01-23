<?php

function process_send_message_to_player ($socket, $data) {
	if($socket->process == true) {
		$obj = json_decode( $data );
		$user_id = $obj->user_id;
		$message = $obj->message;

		$player = id_to_player( $user_id, false );
		if( isset($player) ) {
			$player->write( 'message`' . $message );
		}
		$socket->write('{"status":"ok"}');
	}
}

?>
