<?php

function shutdown_server() {
	global $player_array;
	foreach($player_array as $player){
		$player->write('message`The server is restarting, hold on a sec...');
		$player->remove();
	}
	sleep(1);
	exit();
}

?>
