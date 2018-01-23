<?php

//--- mark an ip as a bad robot --------------------------------------------------------
function process_mark_robot( $socket, $data ) {
	if($socket->process == true) {
		$ip = $data;
		global $player_array;
		Robots::add($ip);
		foreach( $player_array as $player ) {
			if($player->ip == $ip) {
				$player->human = false;
				$player->hostage_exp_points = 0;
			}
		}
	}
}

?>
