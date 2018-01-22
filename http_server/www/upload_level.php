<?php

require_once('../fns/all_fns.php');
require_once('../fns/classes/S3.php');
require_once('../fns/pr2_fns.php');

$title = $_POST['title'];
$note = $_POST['note'];
$data = $_POST['data'];
$live = $_POST['live'];
$min_level = $_POST['min_level'];
$song = (int)$_POST['song'];
$gravity = $_POST['gravity'];
$max_time = $_POST['max_time'];
$items = $_POST['items'];
$remote_hash = $_POST['hash'];
$pass_hash = find('passHash', '');
$has_pass = find('hasPass', 0);
$game_mode = find('gameMode', 'race');
$cowboy_chance = find('cowboyChance', '5');

$note = str_replace('<', '&lt;', $note);

$time = time();
$ip = get_ip();


try {


	//connect to the db
	$db = new DB();
	$s3 = s3_connect();


	//check thier login
	$user_id = token_login($db);
	$user_name = id_to_name($db, $user_id);


	//sanity check
	if($live == 1 && (is_obsene($title) || is_obsene($note))){
		throw new Exception('Could not publish level. Check the title and note for obscenities.');
	}

	// ensure the level survived the upload without data curruption
	$local_hash = md5($title . strtolower($user_name) . $data . $LEVEL_SALT);
	if ($local_hash != $remote_hash) {
		throw new Exception('The level did not upload correctly. Maybe try again?');
	}

	//
	$account = $db->grab_row('user_select', array($user_id));
	if($account->power <= 0) {
		throw new Exception('Guests can not save levels');
	}

	//limit submissions from a single ip
	$safe_min_time = $db->escape( $time - 30 );
	$safe_ip = $db->escape($ip);
	$result = $db->query("SELECT time
									FROM pr2_new_levels
									WHERE ip = '$safe_ip'
									AND time > '$safe_min_time'
									LIMIT 1");
	if(!$result){
		throw new Exception('Could not check previous level submissions.');
	}
	if($result->num_rows > 0 && $live == 1) {
		throw new Exception('Please wait at least 30 seconds before trying to publish again.');
	}


	//
	if( $game_mode == 'race' ) {
		$type = 'r';
	}
	else if( $game_mode == 'deathmatch' ) {
		$type = 'd';
	}
	else if( $game_mode == 'egg' ) {
		$type = 'e';
	}
	else if( $game_mode == 'objective' ) {
		$type = 'o';
	}
	else {
		$type = 'r';
	}


	//load the existing level
	$org_rating = 0;
	$org_votes = 0;
	$org_play_count = 0;
	$levels = $db->call('levels_select_one_by_name', array($user_id, $title));
	if($levels->num_rows == 1) {
		$level = $levels->fetch_object();
		$org_level_id = $level->level_id;
		$org_version = $level->version;
		$org_rating = $level->rating;
		$org_votes = $level->votes;
		$org_play_count = $level->play_count;
		$org_note = $level->note;
		$org_min_level = $level->min_level;
		$org_song = $level->song;
		$org_live = $level->live;
		$org_time = $level->time;
		$org_pass_hash2 = $level->pass;

		//backup the file that is about to be overwritten
		if( ($time - $org_time) > (60*60*24*14) ) {
			backup_level($db, $s3, $user_id, $org_level_id, $org_version-1, $title, $org_live, $org_rating, $org_votes, $org_note, $org_min_level, $org_song, $org_play_count);
		}
	}



	// hash the password
	$hash2 = NULL;
	if($has_pass == 1) {
		if($pass_hash == '') {
			$hash2 = $org_pass_hash2;
		}
		else {
			$hash2 = sha1( $pass_hash . $LEVEL_PASS_SALT);
		}
	}



	//save the level
	$row = $db->grab_row('level_save', array($user_id, $title, $note, $live, $time, $ip, $min_level, $song, $hash2, $type));
	$level_id = $row->level_id;
	$version = $row->version;



	//create the save string
	$url_note = str_replace('&', '%26', $note);
	$url_title = str_replace('&', '%26', $title);
	$str = "level_id=$level_id&version=$version&user_id=$user_id&credits=&cowboyChance=$cowboy_chance&title=$url_title&time=$time"
			."&note=$url_note&min_level=$min_level&song=$song&gravity=$gravity&max_time=$max_time"
			."&has_pass=$has_pass&live=$live&items=$items&gameMode=$game_mode"
			."&data=$data";
	$str_to_hash = $version . $level_id . $str . $LEVEL_SALT_2;
	$hash = md5($str_to_hash);
	$str .= $hash;


	//save this file to the new level system
	$result = $s3->putObjectString($str, 'pr2levels1', $level_id.'.txt');
	if(!$result) {
		throw new Exception('A server error was encountered. Your level could not be saved.');
	}


	//save the new file to the backup system
	backup_level($db, $s3, $user_id, $level_id, $version, $title, $live, $org_rating, $org_votes, $note, $min_level, $song, $org_play_count);



	//tell every one it's time to party
	echo 'message=The save was successful.';
}

catch(Exception $e){
	echo 'error='.$e->getMessage();
}

?>
