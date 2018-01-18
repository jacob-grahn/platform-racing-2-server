<?php

require_once(__DIR__ . '/../fns/all_fns.php');

output('minute is starting');

$db = new DB();

generate_level_list($db, 'newest');
run_update_cycle($db);
write_server_status($db);

echo 'result=ok';





function run_update_cycle( $db ) {
	output( 'run update cycle' );
	//--- gather data to send to active servers
	$send = new stdClass();
	$send->artifact = get_artifact( $db );
	$send->recent_pms = get_recent_pms( $db );
	$send->recent_bans = get_recent_bans( $db );
	$send->campaign = get_campaign( $db );
	$send_str = json_encode( $send );

	//--- send the data
	$servers = poll_servers_2( $db, 'update_cycle`' . $send_str );

	//--- process replies
	foreach( $servers as $server ) {
		if( $server->result != false && $server->result != null ) {
			$happy_hour = (int)$server->result->happy_hour;
			output( 'server is up' );
			save_plays( $db, $server->result->plays );
			save_gp( $db, $server->server_id, $server->result->gp );
			save_population( $db, $server->server_id, $server->result->population );
      save_status( $db, $server->server_id, $server->result->status, $happy_hour );
		}
		else {
			output( 'server is down: ' . json_encode( $server ) );
			save_population( $db, $server->server_id, 0 );
			save_status( $db, $server->server_id, 'down', 0 );
		}
	}
}



function write_server_status( $db ) {
	$servers = $db->to_array( $db->call( 'servers_select' ) );
	$displays = array();
	foreach( $servers as $server ) {
		$display = new stdClass();
		output( 'server id ' . $server->server_name);
		$display->server_id = $server->server_id;
		$display->server_name = preg_replace("/[^A-Za-z0-9 ]/", '', $server->server_name);
		$display->address = $server->address;
		$display->port = $server->port;
		$display->population = $server->population;
		$display->status = $server->status;
		$display->guild_id = $server->guild_id;
		$display->tournament = $server->tournament;
		$display->happy_hour = $server->happy_hour;
		$displays[] = $display;
	}

	$save = new stdClass();
	$save->servers = $displays;
	$display_str = json_encode( $save );

	output( 'output display str' );
	output( $display_str );

	file_put_contents( __DIR__ . '/../www/files/server_status_2.txt', $display_str );
}



function get_artifact( $db ) {
	$artifact = $db->grab_row( 'artifact_location_select' );
	return( $artifact );
}



function get_recent_pms( $db ) {
	$file = __DIR__ . '/../cron/last-pm.txt';
	//--- get the last message id that a notifacation was sent for
	$last_message_id = file_get_contents( $file );
	if(!isset($last_message_id)) {
		$last_message_id = 0;
	}

	//--- select the messages
	output( "last_message_id: $last_message_id" );
	$messages = $db->to_array( $db->call('messages_select_recent', array($last_message_id)) );
	$last_message_id = $db->grab( 'message_id', 'messages_select_max_message_id', array() );

	//--- save the message id for next time
	file_put_contents( $file, $last_message_id );

	//--- done
	return $messages;
}



function get_recent_bans( $db ) {
	$bans = $db->to_array( $db->call('bans_select_recent') );
	return $bans;
}



function get_campaign( $db ) {
	$campaign = $db->to_array( $db->call('campaign_select') );
	return $campaign ;
}



function save_plays( $db, $plays ) {
	foreach($plays as $course => $plays) {
		$db->call('level_increment_play_count', array($course, $plays));
	}
}



function save_gp( $db, $server_id, $gp_array ) {
	foreach( $gp_array as $user_id => $gp ) {
		$user = $db->grab_row( 'user_select', array( $user_id ) );
		$guild_id = $user->guild;
		if( $guild_id > 0 && $server_id == $user->server_id ) {
			$db->call( 'gp_increment', array( $user_id, $guild_id, $gp ) );
			$db->call( 'guild_increment_gp', array( $guild_id, $gp ) );
		}
	}
}



function save_population( $db, $server_id, $population ) {
	$db->call( 'server_update_population', array( $server_id, $population ) );
}



function save_status( $db, $server_id, $status, $happy_hour ) {
	$db->call( 'server_update_status', array( $server_id, $status, $happy_hour ) );
}



function output($str) {
	echo $str . "\n";
}

?>
