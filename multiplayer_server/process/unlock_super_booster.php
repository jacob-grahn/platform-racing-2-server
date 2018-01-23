<?php

function process_unlock_super_booster ($socket, $data) {
	if( $socket->process == true ) {
		$user_id = $data;
		$player = id_to_player( $user_id, false );
		if( isset( $player ) ) {
			$player->super_booster = true;
		}
		$socket->write('ok`');
	}
}

?>
