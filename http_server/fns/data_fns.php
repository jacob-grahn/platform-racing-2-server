<?php


//--- tries to pull a variable from the $_GET or $_POST array. If it is not present, the default is used. ---------------
//--- returns *
function find($str, $default=NULL) {
	if( isset($_COOKIE[$str]) ) {
		$val = $_COOKIE[$str];
	}
	if( isset($_POST[$str]) ) {
		$val = $_POST[$str];
	}
	if( isset($_GET[$str]) ) {
		$val = $_GET[$str];
	}
	if( !isset($val) ) {
		$val = $default;
	}
	return $val;
}


function find_no_cookie($str, $default=NULL) {
	if( isset($_POST[$str]) ) {
		$val = $_POST[$str];
	}
	if( isset($_GET[$str]) ) {
		$val = $_GET[$str];
	}
	if( !isset($val) ) {
		$val = $default;
	}
	return $val;
}



function format_duration( $seconds ) {
	if($seconds < 60){
		$time_left = "$seconds second";
		if( $seconds != 1 ) {
			$time_left .= 's';
		}
	}
	else if($seconds < 60*60){
		$minutes = round($seconds/60, 0);
		$time_left = "$minutes minute";
		if( $minutes != 1 ) {
			$time_left .= 's';
		}
	}
	else if($seconds < 60*60*24){
		$hours = round($seconds/60/60, 0);
		$time_left = "$hours hour";
		if( $hours != 1 ) {
			$time_left .= 's';
		}
	}
	else if($seconds < 60*60*24*30){
		$days = round($seconds/60/60/24, 0);
		$time_left = "$days day";
		if( $days != 1 ) {
			$time_left .= 's';
		}
	}
	else if($seconds < 60*60*24*30*12){
		$months = round($seconds/60/60/24/30, 0);
		$time_left = "$months month";
		if( $months != 1 ) {
			$time_left .= 's';
		}
	}
	else{
		$years = round($seconds/60/60/24/30/12, 0);
		$time_left = "$years year";
		if( $years != 1 ) {
			$time_left .= 's';
		}
	}
	return $time_left;
}


function get_ip() {
    return $_SERVER['REMOTE_ADDR'];
}



function poll_servers_strict( $db, $message, $server_ids ) {
	$servers = poll_servers_2( $db, $message, true, $server_ids );
	foreach( $servers as $server ) {
		if( !isset($server->result) || $server->result->status != 'ok' ) {
			throw new Exception( "Unexpected reply from multiplayer-server. Expected 'ok', got '" . json_encode($server) . "'. ");
		}
	}
}


//--- send a message to every server --------------------------------------------
//--- DO NOT OUTPUT ANYTHING FROM THIS FUNCTION FOR TESTING ---
function poll_servers_2( $db, $message, $receive=true, $server_ids=array() ) {

	$servers = $db->to_array( $db->call('servers_select', array()) );
	$results = array();

	foreach( $servers as $server ) {
		if( count($server_ids) == 0 || array_search($server->server_id, $server_ids) !== false ) {
			$result = talk_to_server( $server->address, $server->port, $server->salt, $message, $receive );
			$server->command = $message;
			$server->result = json_decode( $result );
			$results[] = $server;
		}
	}

	return( $results );
}



//--- connects to the farm server and calls a function -------------------------------------
function talk_to_server($address, $port, $key, $server_function, $receive=false){
	global $PROCESS_PASS;

	$end = chr(0x04);
	$send_num = 1;
	$data = $PROCESS_PASS;
	$intro_function = 'become_process';
	$str_to_hash = $key . $send_num.'`'.$intro_function.'`'.$data;
	$local_hash = md5($str_to_hash);
	$sub_hash = substr($local_hash, 0, 3);

	$message1 = $sub_hash .'`'. $send_num .'`'. $intro_function .'`'. $data . $end;
	$message2 = $server_function . $end;
	$send_str = $message1 . $message2;

	$reply = true;
	$fsock = @fsockopen($address, $port, $errno, $errstr, 2);

	if($fsock){
		fputs($fsock, $send_str);
		stream_set_timeout($fsock, 2);
		if($receive){
			$reply = fread($fsock, 999999);
		}
		fclose($fsock);
	}
	else {
		$reply = false;
	}

	if($receive && $reply == '') {
		$reply = false;
	}
	else {
		$reply = substr($reply, 0, strlen($reply)-1);
	}

	return($reply);
}



//--- tests to see if a string contains obsene words ---------------------------------------
function is_obsene($str){
	$str = strtolower($str);
	$bad_array = array('fuck', 'shit', 'nigger', 'nigga', 'whore', 'bitch', 'slut', 'cunt', 'cock', 'dick', 'penis', 'damn', 'spic');
	$obsene = false;
	foreach($bad_array as $bad){
		if(strpos($str, $bad) !== false){
			$obsene = true;
			break;
		}
	}
	return $obsene;
}




//--- checks if an email address is valid --------------------------------------------------------
function valid_email($email) {
  if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
	  return true;
  }
  else {
	  return false;
  }
}



//returns your account if you are a moderator
function check_moderator($db, $check_ref=true, $min_power=2) {
	if($check_ref) {
		$ref = $_SERVER['HTTP_REFERER'];
		if(
			strpos($ref, 'http://pr2hub.com') !== 0 &&
			strpos($ref, 'https://pr2hub.com') !== 0 &&
			strpos($ref, 'http://cdn.jiggmin.com') !== 0 &&
			strpos($ref, 'http://chat.kongregate.com') !== 0 &&
			strpos($ref, 'http://external.kongregate-games.com/gamez/') !== 0
		) {
			throw new Exception('referrer is: '.$ref);
		}
	}

	$user_id = token_login($db);
	$user = $db->grab_row('user_select_one_mod', array($user_id), 'You are not logged in');

	if($user->power < $min_power) {
		throw new Exception('You lack the power. -1');
	}

	return $user;
}



//returns true if you are logged in as a moderator, false if you are not
function is_moderator($db, $check_ref=true) {
	$is_mod = false;
	try {
		check_moderator($db, $check_ref);
		$is_mod = true;
	}
	catch (Exception $e) {
	}

	return $is_mod;
}




//
function format_level_list($result, $max=9){
	global $LEVEL_LIST_SALT;

	$num = 0;
	$str = '';
	while($row = $result->fetch_object()){
		$level_id = $row->level_id;
		$version = $row->version;
		$title = urlencode($row->title);
		$rating = round($row->rating, 2);
		$play_count = $row->play_count;
		$min_level = $row->min_level;
		$note = urlencode($row->note);
		$user_name = urlencode($row->name);
		$group = $row->power;
		$live = $row->live;
		$pass = isset($row->pass);
		$type = $row->type;

		if($num > 0){
			$str .= "&";
		}
		$str .= "levelID$num=$level_id"
				."&version$num=$version"
				."&title$num=$title"
				."&rating$num=$rating"
				."&playCount$num=$play_count"
				."&minLevel$num=$min_level"
				."&note$num=$note"
				."&userName$num=$user_name"
				."&group$num=$group"
				."&live$num=$live"
				."&pass$num=$pass"
				."&type$num=$type";
		$num++;

		if($num == $max){
			break;
		}
	}

	if( $str != '' ) {
		$hash = md5($str . $LEVEL_LIST_SALT);
		$str .= '&hash='.$hash;
	}

	return $str;
}




function filter_swears( $str ) {
	$damnArray = Array("dang", "dingy-goo", "condemnation");
	$fuckArray = Array("fooey", "fingilly", "funk-master", "freak monster", "jiminy cricket");
	$shitArray = Array("shoot", "shewet");
	$niggerArray = Array("someone cooler than me", "ladies magnet", "cooler race");
	$bitchArray = Array("cooler gender", "female dog");

	$str = str_replace('damn', $damnArray[ array_rand($damnArray) ], $str);
	$str = str_replace('fuck', $fuckArray[ array_rand($fuckArray) ], $str);
	$str = str_replace('nigger', $niggerArray[ array_rand($niggerArray) ], $str);
	$str = str_replace('nigga', $niggerArray[ array_rand($niggerArray) ], $str);
	$str = str_replace('spic ', $niggerArray[ array_rand($niggerArray) ], $str);
	$str = str_replace('shit', $shitArray[ array_rand($shitArray) ], $str);
	$str = str_replace('bitch', $bitchArray[ array_rand($bitchArray) ], $str);
	$str = str_replace('cunt', $bitchArray[ array_rand($bitchArray) ], $str);

	return( $str );
}




function rate_limit($key, $interval, $max, $error='Slow down a bit, yo.') {
	$unit = round(time() / $interval);
	$key .= '-'.$unit;
	$count = 0;

	if( apcu_exists($key) ) {
		$count = apcu_fetch( $key );
		if( $count >= $max ) {
			throw new Exception( $error );
		}
	}

	$count++;
	apcu_store( $key, $count, $interval );

	return( $count );
}

?>
