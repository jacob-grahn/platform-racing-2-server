<?php

function process_update_cycle ($socket, $data) {
	if( $socket->process == true ) {
		$obj = json_decode( $data );
		place_artifact( $obj->artifact );
		pm_notify( $obj->recent_pms );
		apply_bans( $obj->recent_bans );

		$rep = new stdClass();
		$rep->plays = drain_plays();
		$rep->gp = GuildPoints::drain();
		$rep->population = get_population();
		$rep->status = get_status();
		$rep->happy_hour = HappyHour::isActive();

		$socket->write( json_encode( $rep ) );
	}
}

?>
