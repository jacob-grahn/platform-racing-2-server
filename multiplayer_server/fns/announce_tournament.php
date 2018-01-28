<?php

function announce_tournament( $chat ) {
	if( pr2_server::$tournament ) {
		$chat->send_to_all('systemChat`Tournament mode is on!<br/>'
			.'Hat: '.Hats::id_to_str(pr2_server::$tournament_hat).'<br/>'
			.'Speed: '.pr2_server::$tournament_speed.'<br/>'
			.'Accel: '.pr2_server::$tournament_acceleration.'<br/>'
			.'Jump: '.pr2_server::$tournament_jumping);
	}
	else {
		$chat->send_to_all('systemChat`Tournament mode is off.');
	}
}

function tournament_status( $requester ) {
	if( pr2_server::$tournament ) {
		$requester->write('systemChat`Tournament mode is on!<br/>'
			.'Hat: '.Hats::id_to_str(pr2_server::$tournament_hat).'<br/>'
			.'Speed: '.pr2_server::$tournament_speed.'<br/>'
			.'Accel: '.pr2_server::$tournament_acceleration.'<br/>'
			.'Jump: '.pr2_server::$tournament_jumping);
	}
	else {
		$requester->write('systemChat`Tournament mode is off.');
	}
}

?>
