<?php


//-----------------------------------------------------------------
function start_perk( $slug, $user_id, $guild_id ) {
	output( "start_perk - slug: $slug, user_id: $user_id, guild_id: $guild_id" );
	$seconds_duration = 3700;
	if( $slug == Perks::GUILD_FRED ) {
		assign_guild_part( 'body', 29, $user_id, $guild_id, $seconds_duration );
	}
	else if( $slug == PERKS::GUILD_GHOST ) {
		assign_guild_part( 'head', 31, $user_id, $guild_id, $seconds_duration );
		assign_guild_part( 'body', 30, $user_id, $guild_id, $seconds_duration );
		assign_guild_part( 'feet', 27, $user_id, $guild_id, $seconds_duration );
	}
	else if( $slug == PERKS::GUILD_ARTIFACT ) {
		assign_guild_part( 'hat', 14, $user_id, $guild_id, $seconds_duration );
		assign_guild_part( 'eHat', 14, $user_id, $guild_id, $seconds_duration );
	}
	else if( $slug == PERKS::HAPPY_HOUR ) {
		HappyHour::activate();
	}
	else if( $slug == PERKS::SERVER_RESTART ) {
		HappyHour::activate();
		assign_guild_part( 'body', 29, $user_id, $guild_id, $seconds_duration, false );
	}
}


//--------------------------------------------------------------------------------
function assign_guild_part( $type, $part_id, $user_id, $guild_id, $seconds_duration, $set=true ) {
	global $player_array;

	TemporaryItems::add( $type, $part_id, $user_id, $guild_id, $seconds_duration );

	foreach( $player_array as $player ) {
		if( $player->guild_id == $guild_id ) {
			$player->gain_part( 'e'.ucfirst($type), $part_id );
			if( $set === true ) {
				$player->set_part( $type, $part_id );
			}
			$player->send_customize_info();
		}
	}
}

//


?>
