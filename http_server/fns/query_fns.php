<?php

require_once(__DIR__ . '/random_str.php');


//--- checks if a login is valid -----------------------------------------------------------
function pass_login($db, $name, $password){

	//get their ip
	$ip = get_ip();

	//error check
	if(empty($name) || !is_string($password) || $password == ''){
		throw new Exception('You must enter a name and a password.');
	}
	if(strlen($name) < 2){
		throw new Exception('Your name must be at least 2 characters long.');
	}
	if(strlen($name) > 20){
		throw new Exception('Your name can not be more than 20 characters long.');
	}

	// load the user row
	$result = $db->call('user_select_all_by_name', array($name));
	if($result->num_rows < 1) {
		throw new Exception('That account was not found.');
	}
	$user = $result->fetch_object();

	// check the password
	if (!password_verify(sha1($password), $user->pass_hash)) {
		if (password_verify(sha1($password), $user->temp_pass_hash)) {
			$db->call('user_apply_temp_pass', array($user->user_id));
		}
		else {
			throw new Exception('Incorrect password');
		}
	}

	//check to see if they're banned
	check_if_banned($db, $user->user_id, $ip);

	//respect changes to capitalization
	$user->name = $name;

	//done
	return ($user);
}



//--- login using a token ------------------------------------------------------------
function token_login($db, $use_cookie=true) {

	$rec_token = find_no_cookie( 'token' );
	if(isset($rec_token) && $rec_token != '') {
		$token = $rec_token;
	}
	else if($use_cookie && isset($_COOKIE['token']) && $_COOKIE['token'] != '') {
		$token = $_COOKIE['token'];
	}

	if(!isset($token)) {
		throw new Exception('No token found. Please log in again.');
	}

	$user_id = use_login_token($db, $token, 'Could not log you in. Please log in again.');

	$ip = get_ip();
	check_if_banned($db, $user_id, $ip);

	return($user_id);
}



//--- lookup user_id with name ---------------------------------------------------------
function name_to_id($db, $name) {
	$user_id = $db->grab('user_id', 'user_select_user_id', array($name), 'Could not find a user with that name.');
	return $user_id;
}



//--- lookup name with user_id ---------------------------------------------------------
function id_to_name($db, $user_id) {
	$user_name = $db->grab('name', 'user_select', array($user_id));
	return $user_name;
}



//--- checks if an account is banned -----------------------------------------------
function query_ban_record($db, $where){
	$time = time();
	$result = $db->query("select *
									from bans
									where $where
									and lifted != 1
									and expire_time > '$time'
									limit 0, 1");
	if(!$result){
		throw new Exception('Could not check your account status');
	}
	return($result);
}



//--- count the number of bans an account has recieved -----------------
function count_bans($db, $value, $ban_type='account') {
	$safe_value = addslashes($value);

	if($ban_type == 'account') {
		$field = 'banned_user_id';
	}
	else {
		$field = 'banned_ip';
	}

	$result = $db->query("select count(*) as ban_count
									from bans
									where $field = '$safe_value'");
	if(!$result){
		throw new Exception('Could not count bans for '.$ban_type);
	}

	$row = $result->fetch_object();
	$ban_count = $row->ban_count;

	return($ban_count);
}



//--- retrieve all bans of a certain type ---------------
function retrieve_bans($db, $value, $ban_type='account') {
	$safe_value = addslashes($value);

	if($ban_type == 'account') {
		$field = 'banned_user_id';
	}
	else {
		$field = 'banned_ip';
	}

	$result = $db->query("select *
									from bans
									where $field = '$safe_value'
									and ".$ban_type."_ban = 1
									limit 0, 50");
	if(!$result){
		throw new Exception('Could not retrieve bans for '.$ban_type);
	}

	return($result);
}


function award_part($db, $user_id, $type, $part_id, $ensure=true) {
	$parts = array();
	$parts[] = $part_id;
	$result = award_parts( $db, $user_id, $type, $parts, $ensure );
	return $result;
}


//--- award hats ----------------------------------
function award_parts($db, $user_id, $type, $part_ids, $ensure=true) {
	$ret = false;
	$epicUpgrade = false;

	if( strpos($type, 'e') === 0 ) {
		$epicUpgrade = true;
	}

	if( $epicUpgrade ) {
		$row = $db->grab_row( 'epic_upgrades_select', array($user_id), '', true );
	}
	else {
		$row = $db->grab_row( 'pr2_select', array($user_id), '', true );
	}

	if( isset($row) ){
		$field = type_to_db_field( $type );
		$str_array = $row->{$field};
	}
	else {
		$str_array = '';
	}

	$str_array_new = $str_array;

	foreach($part_ids as $part_id) {
		if( $ensure ) {
			$db->call( 'part_awards_insert', array( $user_id, $type, $part_id ), 'Could not ensure this part award.' );
		}
		$str_array_new = append_to_str_array( $str_array_new, $part_id );
	}

	if($str_array_new != $str_array) {
		if( $epicUpgrade ) {
			$db->call( 'epic_upgrades_update_field', array($user_id, $type, $str_array_new), 'Could not update epic field.' );
		}
		else {
			$db->call( 'pr2_update_part_array', array($user_id, $type, $str_array_new), 'Could not update part field.' );
		}
		$ret = true;
	}

	return $ret;
}


function type_to_db_field( $type ) {
	if($type == 'hat') {
		$field = 'hat_array';
	}
	else if($type == 'head') {
		$field = 'head_array';
	}
	else if($type == 'body') {
		$field = 'body_array';
	}
	else if($type == 'feet') {
		$field = 'feet_array';
	}
	else if($type == 'eHat') {
		$field = 'epic_hats';
	}
	else if($type == 'eHead') {
		$field = 'epic_heads';
	}
	else if($type == 'eBody') {
		$field = 'epic_bodies';
	}
	else if($type == 'eFeet') {
		$field = 'epic_feet';
	}
	else {
		throw new Exception('Unknown type');
	}
	return( $field );
}


function append_to_str_array( $str_arr, $val ) {
	if( !isset($str_arr) || $str_arr == ',' ) {
		$str_arr = '';
	}
	if( $str_arr === '' ) {
		$ret = $val.'';
	}
	else {
		$arr = explode( ',', $str_arr );
		$index = array_search( $val, $arr );
		if( $index === false ) {
			$arr[] = $val;
		}
		$ret = join( ',', $arr );
	}
	return $ret;
}



//--- generates a login token ----------------------------------
function get_login_token($user_id) {
	$token = $user_id . '-' . random_str(30);
	return $token;
}



//--- save a login token ------------------------------------------
function save_login_token($db, $user_id, $token) {
	$db->call('token_insert', array($user_id, $token));
}



//--- uses a login token ------------------------------------------
function use_login_token($db, $token) {
	$user_id = $db->grab('user_id', 'token_select', array($token), 'You are not logged in.');
	return $user_id;
}


//--- delete a login token -----------------------------------------
function delete_login_token($db, $token) {
	$db->call('token_delete', array($token));
}



//--- throw an exception if the user is banned ----------------------
function check_if_banned($db, $user_id, $ip){
	$row = query_if_banned( $db, $user_id, $ip );

	if( $row !== false ){
		$ban_id = $row->ban_id;
		$banned_ip = $row->banned_ip;
		$banned_user_id = $row->banned_user_id;
		$mod_user_id = $row->mod_user_id;
		$expire_time = $row->expire_time;
		$reason = $row->reason;
		$response = $row->response;

		//figure out what the best way to say this is
		$seconds = $expire_time - time();
		$time_left = format_duration( $seconds );

		//tell it to the world
		$output = "This account or ip address has been banned.\n"
					."Reason: $reason \n"
					."This ban will expire in $time_left. \n"
					."You can see more details about this ban at pr2hub.com/bans/show_record.php?ban_id=$ban_id. \n\n"
					."If you feel that this ban is unjust, you can dispute it. Follow the instructions outlined at jiggmin2.com/forums/showthread.php?tid=110.";

		throw new Exception($output);
	}
}



function query_if_banned( $db, $user_id, $ip ) {
	$ban_row = false;
	if( isset( $user_id ) && $user_id != 0 ) {
		$result = query_ban_record($db, "banned_user_id = '$user_id' AND account_ban = 1");
		if($result->num_rows > 0) {
			$ban_row = $result->fetch_object();
		}
	}
	if( isset( $ip ) && $ban_row === false ) {
		$result = query_ban_record($db, "banned_ip = '$ip' AND ip_ban = 1");
		if($result->num_rows > 0) {
			$ban_row = $result->fetch_object();
		}
	}
	return( $ban_row );
}




//--- write a level list to the filesystem --------------------------------------------
function generate_level_list($db, $mode) {

	if($mode == 'campaign'){
		$result = $db->call('levels_select_campaign');
	}
	else if($mode == 'best'){
		$result = $db->call('levels_select_best');
	}
	else if($mode == 'best_today'){
		$result = $db->call('levels_select_best_today');
	}
	else if($mode == 'newest'){
		$result = $db->call('levels_select_newest');
	}

	$dir = __DIR__ . '/../www/files/lists/'.$mode.'/';
	@mkdir($dir, 0777, true);

	for($j=0; $j<9; $j++) {
		$str = format_level_list($result, 9);
		//echo $str;
		$filename = $dir .($j+1);
		$handle = @fopen($filename, 'w');
		if($handle) {
			fwrite($handle, $str);
			fclose($handle);
			//chmod($filename, 0777);
		}
		else {
			throw new Exception('could not write level list to '.$filename);
		}
	}
}






//--- perform a level search ---------------------------------------------------
function search_levels($mode, $search_str, $order, $dir, $page) {

	if(!isset($mode) || ($mode != 'user' && $mode != 'title')) {
		$mode = 'title';
	}
	if(!isset($order) || ($order != 'rating' && $order != 'date' && $order != 'alphabetical' && $order != 'popularity')) {
		$order = 'date';
	}
	if(!isset($dir) || ($dir != 'asc' && $dir != 'desc')) {
		$dir = 'desc';
	}
	if(!isset($page) || !is_numeric($page) || $page < 1) {
		$page = 1;
	}

	$start = ($page-1) * 6;
	$count = 6;

	$safe_search_str = addslashes($search_str);
	$safe_dir = addslashes($dir);

	$select_str = 'select pr2_levels.level_id, pr2_levels.version, pr2_levels.title, pr2_levels.rating, pr2_levels.play_count, pr2_levels.min_level, pr2_levels.note, pr2_levels.live, pr2_levels.type, users.name, users.power, pr2_levels.pass';
	$from_str = 'from pr2_levels, users';


	if($order == 'rating'){
		$order_by = 'order by pr2_levels.rating';
	}
	else if($order == 'date'){
		$order_by = 'order by pr2_levels.time';
	}
	else if($order == 'alphabetical'){
		$order_by = 'order by pr2_levels.title';
	}
	else if($order == 'popularity'){
		$order_by = 'order by pr2_levels.play_count';
	}
	else {
		$order_by = 'order by pr2_levels.time';
	}


	if($mode == 'user'){
		$query_str = "$select_str
						$from_str
						WHERE users.name = '$safe_search_str'
						AND pr2_levels.user_id = users.user_id
						AND (pr2_levels.live = 1 OR pr2_levels.pass IS NOT NULL)
						$order_by $safe_dir
						limit $start, $count";

	}
	else if($mode == 'title'){
		$query_str = "$select_str
						$from_str
						WHERE MATCH (title) AGAINST ('\"$safe_search_str\"' IN BOOLEAN MODE)
						AND pr2_levels.user_id = users.user_id
						AND live = 1
						$order_by $safe_dir
						limit $start, $count";
	}


	$db = new DB();
	$result = $db->query($query_str, '*search_levels');
	if(!$result){
		throw new Exception('Could not retrieve levels.');
	}


	$str = format_level_list($result, 999);
	return($str);
}


?>
