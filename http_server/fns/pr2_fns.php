<?php

function backup_level($db, $s3, $user_id, $level_id, $version, $title, $live=0, $rating=0, $votes=0, $note='', $min_level=0, $song=0, $play_count=0) {

	$filename = "$level_id.txt";
	$backup_filename = "$level_id-v$version.txt";
	$success = true;

	try {
		$result = $s3->copyObject('pr2levels1', $filename, 'pr2backups', $backup_filename);
		if(!$result) {
			throw new Exception('Could not save a backup of your level.');
		}

		$result = $db->call('level_backups_insert', array($user_id, $level_id, $title, $version, $live, $rating, $votes, $note, $min_level, $song, $play_count));
		if(!$result) {
			throw new Exception('Could not register a backup of your level.');
		}
	}

	catch (Exception $e) {
		$success = false;
	}

	return $success;
}




function send_pm( $db, $from_user_id, $to_user_id, $message ) {
	$ip = get_ip();
	$time = time();

	$safe_message = addslashes($message);
	$safe_ip = addslashes($ip);
	$safe_time = addslashes($time);

	$account = $db->grab_row('user_select', array($from_user_id));
	$account_power = $account->power;

	$pr2_account = $db->grab_row('pr2_select', array($from_user_id));
	$account_rank = $pr2_account->rank;


	//min rank to send PMs
	if($account_rank < 3) {
		throw new Exception('You need to level up to rank 3 to send Private Messages.');
	}
	if($account_power <= 0) {
		throw new Exception('Guests can not send messages.');
	}


	//check the length of their message
	$message_len = strlen($message);
	if($message_len > 1000) {
		throw new Exception('Could not send. The maximum message length is 1,000 characters. Your message is '. number_format($message_len) .' characters long.');
	}


	//prevent flooding
	$key1 = 'pm-'.$from_user_id;
	$key2 = 'pm-'.$ip;
	$interval = 60;
	$limit = 4;
	$error_message = 'You have sent 4 messages in the past 60 seconds, please wait a bit before sending another message.';
	rate_limit( $key1, $interval, $limit, $error_message );
	rate_limit( $key2, $interval, $limit, $error_message );


	//see if they've been ignored
	$result = $db->query("select * from
									ignored
									where ignore_id = '$from_user_id'
									and user_id = '$to_user_id'
									limit 0, 1");
	if(!$result){
		throw new Exception('Could not check your status.');
	}
	if($result->num_rows > 0){
		throw new Exception('You have been ignored by this player. They won\'t recieve any chat or messages from you.');
	}


	//add the message to the db
	$db->call( 'message_insert', array( $to_user_id, $from_user_id, $message, $ip ) );
}


function guild_count_active( $db, $guild_id ) {
	$key = 'ga' . $guild_id;

	if( apcu_exists($key) ) {
		$active_count = apcu_fetch( $key );
	}
	else {
		$active_count = $db->grab( 'active_member_count', 'guild_select_active_member_count', array($guild_id) );
		apcu_store( $key, $active_count, 3600 ); //one hour
	}
	return( $active_count );
}

?>
