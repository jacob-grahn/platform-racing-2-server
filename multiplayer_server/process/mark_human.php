<?php

//--- mark an ip as a good human ------------------------------------------------------
function mark_human ($socket, $data) {
	if($socket->process == true) {
		$ip = $data;
		global $player_array;
		foreach( $player_array as $player ) {
			if($player->ip == $ip && $player->human == false) {
				$player->human = true;
				$exp_gain = $player->hostage_exp_points + 250;
				$player->inc_exp($exp_gain);
			}
		}
	}
}

?>
