<?php

function begin_loadup( $server_id ) {
	global $db;
	$db = new DB();

	$server = $db->grab_row( 'server_select', array($server_id) );
	$campaign = $db->to_array( $db->call('campaign_select') );
	$perks = $db->to_array( $db->call('purchases_select_recent') );
	$artifact = $db->grab_row('artifact_location_select');

	set_server( $db, $server );
	set_campaign( $campaign );
	set_perks( $perks );
	place_artifact($artifact);
	HappyHour::activate();
	start_perk(Perks::GUILD_FRED, 0, -1);
}



function set_server( $db, $server ) {
	global $port, $guild_id, $guild_owner, $server_name, $key;
	$port = $server->port;
	$server_name = $server->server_name;
	$guild_id = $server->guild_id;
	$guild_owner = 0;
	$key = $server->salt;
	pr2_server::$tournament = $server->tournament;
	if( pr2_server::$tournament ) {
		pr2_server::$no_prizes = true;
	}

	if( $guild_id != 0 ) {
		$guild = $db->grab_row( 'guild_select', array($guild_id) );
		$guild_owner = $guild->owner_id;
	}
	else {
		$guild_owner = 4291976; //Fred the G. Cactus
	}
}



function set_campaign( $campaign_levels ) {
	global $campaign_array;
	$campaign_array = array();
	foreach($campaign_levels as $level) {
		$campaign_array[$level->level_id] = $level;
	}
}



function set_perks( $perks ) {
	foreach( $perks as $perk ) {
		$slug = $perk->product;
		$a = array( Perks::GUILD_FRED, Perks::GUILD_GHOST );
		if( array_search($slug, $a) !== false ) {
			output( "activating perk $slug for user $perk->user_id and guild $perk->guild_id");
			start_perk( $slug, $perk->user_id, $perk->guild_id );
		}
		if( $slug == 'happy-hour' ) {
			HappyHour::activate();
		}
	}
}

?>
