<?php

//--- unlock a temporary perk -------------------------------------------------------------
function process_unlock_perk ($socket, $data) {
	if( $socket->process == true ) {
		list( $slug, $user_id, $guild_id, $user_name ) = explode( '`', $data );

		start_perk( $slug, $user_id, $guild_id );

		if( $guild_id != 0 ) {
			if( $slug == Perks::GUILD_FRED ) {
				send_to_guild( $guild_id, "systemChat`$user_name unlocked Fred mode for your guild!" );
			}
			if( $slug == PERKS::GUILD_GHOST ) {
				send_to_guild( $guild_id, "systemChat`$user_name unlocked Ghost mode for your guild!" );
			}
			if( $slug == PERKS::HAPPY_HOUR ) {
				send_to_all_players( "systemChat`$user_name just triggered a Happy Hour!" );
			}
		}

		$socket->write('{"status":"ok"}');
	}
}


//--- unlock items -------------------------------------------------------------
function process_unlock_set_king ($socket, $data) {
	if( $socket->process == true ) {
		$user_id = $data;
		$player = id_to_player( $user_id, false );
		if( isset( $player ) ) {
			$player->gain_part( 'head', 28, true );
			$player->gain_part( 'body', 26, true );
			$player->gain_part( 'feet', 24, true );
			$player->gain_part( 'eHead', 28 );
			$player->gain_part( 'eBody', 26 );
			$player->gain_part( 'eFeet', 24 );
			$player->send_customize_info();
		}
		$socket->write('{"status":"ok"}');
	}
}


//--- unlock items -------------------------------------------------------------
function process_unlock_set_queen ($socket, $data) {
	if( $socket->process == true ) {
		$user_id = $data;
		$player = id_to_player( $user_id, false );
		if( isset( $player ) ) {
			$player->gain_part( 'head', 29, true );
			$player->gain_part( 'body', 27, true );
			$player->gain_part( 'feet', 25, true );
			$player->gain_part( 'eHead', 29 );
			$player->gain_part( 'eBody', 27 );
			$player->gain_part( 'eFeet', 25 );
			$player->send_customize_info();
		}
		$socket->write('{"status":"ok"}');
	}
}


//--- unlock items -------------------------------------------------------------
function process_unlock_set_djinn ($socket, $data) {
	if( $socket->process == true ) {
		$user_id = $data;
		$player = id_to_player( $user_id, false );
		if( isset( $player ) ) {
			$player->gain_part( 'head', 35, true );
			$player->gain_part( 'body', 35, true );
			$player->gain_part( 'feet', 35, true );
			$player->gain_part( 'eHead', 35 );
			$player->gain_part( 'eBody', 35 );
			$player->gain_part( 'eFeet', 35 );
			$player->send_customize_info();
		}
		$socket->write('{"status":"ok"}');
	}
}


//--- unlock epic everything --------------------------------------------------
function process_unlock_epic_everything ($socket, $data) {
	if( $socket->process == true ) {
		$user_id = $data;
		$player = id_to_player( $user_id, false );
		if( isset( $player ) ) {
			$player->gain_part( 'eHat', '*' );
			$player->gain_part( 'eHead', '*' );
			$player->gain_part( 'eBody', '*' );
			$player->gain_part( 'eFeet', '*' );
			$player->send_customize_info();
		}
		$socket->write('{"status":"ok"}');
	}
}

?>
