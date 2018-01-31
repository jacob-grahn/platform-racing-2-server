<?php

require_once( '../fns/all_fns.php' );
require_once( '../fns/Encryptor.php' );

$encrypted_login = find( 'i' );
$version = find( 'version' );

$allowed_versions = array('24-dec-2013-v1');
$guest_login = false;
$has_email = false;
$has_ant = false;
$new_account = false;
$rt_available = 0;
$rt_used = 0;
$guild_owner = 0;
$emblem = '';
$guild_name = '';
$friends = array();
$ignored = array();

// ip info and run it through an IP info API (because installing geoip is not worth the hassle)
$ip = get_ip();

try {
	$ip_info = json_decode(file_get_contents('https://tools.keycdn.com/geo.json?host=' . $ip));
	$country_code = $ip_info->data->geo->country_code;
}
catch (Exception $e) {
	$country_code = '?';
}

try {


	//--- sanity check
	if(!isset($encrypted_login)) {
		throw new Exception( 'Login data not recieved.' );
	}
	if( array_search( $version, $allowed_versions ) === false ) {
		throw new Exception('Platform Racing 2 has recently been updated. Please refresh your browser to download the latest version.');
	}


	//--- rate limit
	rate_limit( 'login-'.$ip, 60, 10, 'Only 10 logins per minute per ip are accepted.' );


	//--- decrypt login data
	$encryptor = new Encryptor();
	$encryptor->set_key($LOGIN_KEY);
	$str_login = $encryptor->decrypt($encrypted_login, $LOGIN_IV);
	$login = json_decode($str_login);

	$user_name = $login->user_name;
	$user_pass = $login->user_pass;
	$version2 = $login->version;
	$server_id = $login->server->server_id;
	$server_port = $login->server->port;
	$server_address = $login->server->address;
	$origination_domain = $login->domain;
	$remember = $login->remember;
	$login_code = $login->login_code;


	//--- more sanity checks
	if( array_search( $version2, $allowed_versions ) === false ) {
		throw new Exception('Platform Racing 2 has recently been updated. Please refresh your browser to download the latest version. [Version check 2] ' . $version2);
	}
	if( $origination_domain == 'local' ) {
		throw new Exception( 'Testing mode has been disabled.' );
	}
	if( !isset($user_name) || empty($user_name) || strlen(trim($user_name)) === 0 || strpos($user_name, '`') !== false ) {
		throw new Exception( 'Invalid user name entered.' );
	}


	//--- connect
	$db = new DB();



	//--- get the server they're connecting to
	$server = $db->grab_row( 'server_select', array( $server_id ) );



	//--- guest login
	if( strtolower($login->user_name) == 'guest' ) {
		$guest_login = true;
		if( get_ip(false) != get_ip(true) ) {
			throw new Exception( 'You seem to be using a proxy to connect to PR2. You won\'t be able to connect as a guest, but you can create an account to play.' );
		}
		$user = $db->grab_row('users_select_guest');
		check_if_banned($db, $user->user_id, $ip);
	}


	//--- account login
	else {

		//--- record this attempted log in
		$db->call( 'login_attempt_insert', array($ip, $login->user_name) );

		//--- check for a token
		$in_token = find( 'token' );

		//token login
		if( isset($in_token) && $login->user_name == '' && $login->user_pass == '') {
			$token = $in_token;
			$user_id = token_login($db);
			$user = $db->grab_row('user_select', array($user_id));
		}

		//or password login
		else {
			$user = pass_login($db, $user_name, $user_pass);
		}
	}


	//--- give them a login token for future requests
	$token = get_login_token( $user->user_id );
	save_login_token( $db, $user->user_id, $token );
	if( $remember == 'true' && !$guest_login ) {
		$token_expire = time() + (60*60*24*30);
		setcookie('token', $token, $token_expire);
	}
	else {
		setcookie('token', '', time()-3600);
	}


	//--
	$user_id = $user->user_id;
	$user_name = $user->name;
	$group = $user->power;


	//---
	if( $server->guild_id != 0 && $user->guild != $server->guild_id ) {
		throw new Exception( 'You must be a member of this guild to join this server.' );
	}


	//--- get their info, or create a row for them if they don't have one
	$pr2_result = $db->call('pr2_select', array($user_id));



	//--- they don't have a row yet, so make them one
	if($pr2_result->num_rows < 1){
		$new_account = 'true';
		$db->call('pr2_insert', array($user_id));

		//--- send them a welcome pm
		$welcome_message = 'Welcome to Platform Racing 2, '.$user_name.'!

<a href="https://grahn.io" target="_blank"><u><font color="#0000FF">Click here</font></u></a> to read about the latest Platform Racing news on my blog.

If you have any questions or comments, send me an email at <a href="mailto:jacob@grahn.io?subject=Questions or Comments about PR2" target="_blank"><u><font color="#0000FF">jacob@grahn.io</font></u></a>.

Thanks for playing, I hope you enjoy.

- Jacob';
		
		$db->call('message_insert', array($user_id, 1, $welcome_message, '0'));

		//--- I don't feel like typing the defaults twice, so pull them from the db
		$pr2_result = $db->call('pr2_select', array($user_id));
	}


	//--- speed, hats, etc
	$stats = $pr2_result->fetch_object();
	$epic_upgrades = $db->grab_row( 'epic_upgrades_select', array($user_id), '', true );



	//--- check if they own rank tokens
	$row = $db->grab_row('rank_token_select', array($user_id), '', true);
	if(isset($row)) {
		$rt_available = $row->available_tokens;
		$rt_used = $row->used_tokens;
	}

	$rt_available += $db->grab( 'count', 'rank_token_rentals_count', array($user->user_id, $user->guild) );
	if( $rt_available < $rt_used ) {
		$rt_used = $rt_available;
	}


	//--- record moderator login
	if( $group > 1 ) {
		$db->call( 'mod_action_insert', array( $user_id, "$user_name logged in from $ip", $user_id, $ip ) );
	}


	$hat_array = explode( ',', $stats->hat_array );
	$head_array = explode( ',', $stats->head_array );
	$body_array = explode( ',', $stats->body_array );
	$feet_array = explode( ',', $stats->feet_array );


	//--- santa set
	$date = date('F d');
	if($date == 'December 24' || $date == 'December 25') {
		if( add_item($hat_array, 7) ) {
			$stats->hat = 7;
		}
		if( add_item($head_array, 34) ) {
		    $stats->head = 34;
		}
		if( add_item($body_array, 34) ) {
		    $stats->body = 34;
		}
		if( add_item($feet_array, 34) ) {
		    $stats->feet = 34;
		}
	}

	//--- bunny set
	if($date == 'April 20' || $date == 'April 21') {
		if( add_item($head_array, 39) ) {
		    $stats->head = 39;
		}
		if( add_item($body_array, 39) ) {
		    $stats->body = 39;
		}
		if( add_item($feet_array, 39) ) {
		    $stats->feet = 39;
		}
	}

	//--- party hat
	if($date == 'December 31' || $date == 'January 1') {
		if( add_item($hat_array, 8) ) {
			$stats->hat = 8;
		}
	}

	//--- heart set
	if($date == 'February 13' || $date == 'February 14' || $date == 'February 19' || $date == 'February 20') {
		if( add_item($head_array, 38) ) {
		    $stats->head = 38;
		}
		if( add_item($body_array, 38) ) {
		    $stats->body = 38;
		}
		if( add_item($feet_array, 38) ) {
		    $stats->feet = 38;
		}
	}

	//--- give crown hats to moderators
	if($group > 1){
		add_item( $hat_array, 6 );
	}


	//--- get their friends
	$friends_result = $db->to_array( $db->call('friends_select', array($user_id)) );
	foreach( $friends_result as $fr ) {
		$friends[] = $fr->friend_id;
	}

	//--- get their ignored
	$ignored_result = $db->to_array( $db->call('ignored_select', array($user_id)) );
	foreach( $ignored_result as $ir ) {
		$ignored[] = $ir->ignore_id;
	}


	//--- get their rank gained today
	$exp_today_id = $db->grab( 'exp', 'exp_today_select', array( 'id-'.$user_id ) );
	$exp_today_ip = $db->grab( 'exp', 'exp_today_select', array( 'ip-'.$ip ) );
	$exp_today = max( $exp_today_id, $exp_today_ip );


	//--- see if they have gotten the artifact
	$artifact = $db->grab( 'hasCurrentArtifact', 'artifact_check', array( $user_id ) );



	//--- check if they have an email set
	if( isset($user->email) && strlen($user->email) > 0 ) {
		$has_email = true;
	}


	//--- check if they have kong's ant body
	if( array_search(20, $head_array) !== false ) {
		$has_ant = true;
	}


	//--- see if they are in a guild
	if( $user->guild != 0 ) {
		$guild = $db->grab_row( 'guild_select', array( $user->guild ) );
		if( $guild->owner_id == $user_id ) {
			$guild_owner = 1;
		}
		$emblem = $guild->emblem;
		$guild_name = $guild->guild_name;
	}


	//--- get their most recent PM id
	$last_recv_id = $db->grab('message_id', 'messages_select_most_recent', array($user_id), '', true, 0);


	//--- update their status
	$status = "Playing on $server->server_name";

	$db->call( 'user_update_status', array($user_id, $status, $server_id) );
	$db->call( 'user_update_ip', array($user_id, $ip) );
	$db->call( 'recent_logins_insert', array($user_id, $ip, $country_code) );


	//---
	$stats->hat_array = join( ',', $hat_array );
	$stats->head_array = join( ',', $head_array );
	$stats->body_array = join( ',', $body_array );
	$stats->feet_array = join( ',', $feet_array );


	//--- send this info to the socket server
	$send = new stdClass();
	$send->login = $login;
	$send->user = $user;
	$send->stats = $stats;
	$send->friends = $friends;
	$send->ignored = $ignored;
	$send->artifact = $artifact;
	$send->new_account = $new_account;
	$send->rt_used = $rt_used;
	$send->rt_available = $rt_available;
	$send->exp_today = $exp_today;
	$send->status = $status;
	$send->server = $server; //can remove this later?
	$send->epic_upgrades = $epic_upgrades;

	$str = "register_login`" . json_encode( $send );
	talk_to_server($server_address, $server_port, $server->salt, $str, false);


	//--- tell it to the world
	$reply = new stdClass();
	$reply->status = 'success';
	$reply->token = $token;
	$reply->email = $has_email;
	$reply->ant = $has_ant;
	$reply->time = time();
	$reply->lastRead = $user->read_message_id;
	$reply->lastRecv = $last_recv_id;
	$reply->guild = $user->guild;
	$reply->guildOwner = $guild_owner;
	$reply->guildName = $guild_name;
	$reply->emblem = $emblem;
	$reply->userId = $user_id;
	echo json_encode( $reply );
}


catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}



function add_item( &$arr, $item ) {
	if( array_search($item, $arr) === false){
		$arr[] = $item;
		return true;
	}
	else {
		return false;
	}
}

?>
