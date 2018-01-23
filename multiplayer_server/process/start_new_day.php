<?php

//--- clear player's daily exp levels -------------------------------------------------
function process_start_new_day ($socket, $data) {
	if( $socket->process == true ) {
		global $player_array;
		foreach( $player_array as $player ) {
			$player->start_exp_today = $player->exp_today = 0;
		}
		$socket->write('day-ok');
	}
}

?>
