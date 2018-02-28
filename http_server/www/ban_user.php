<?php

header("Content-type: text/plain");

require_once('../fns/all_fns.php');

$banned_name = default_val($_POST['banned_name']);
$duration = default_val($_POST['duration'], 60);
$reason = default_val($_POST['reason'], '');
$record = default_val($_POST['record'], '');
$using_mod_site = default_val($_POST['using_mod_site'], 'no');
$redirect = default_val($_POST['redirect'], 'no');
$type = default_val($_POST['type'], 'both');
$force_ip = default_val($_POST['force_ip']);

$ip = get_ip();

$safe_banned_name = mysqli_real_escape_string($banned_name);
$safe_reason = mysqli_real_escape_string($reason);
$safe_record = mysqli_real_escape_string($record);

// if it's a month/year ban coming from PR2, correct the weird ban times
if ($using_mod_site == 'no') {
	$duration = str_replace('29030400', '31536000', $duration); // fix year ban
	$duration = str_replace('2419200', '2592000', $duration); // fix month ban
}

try {
	
	// POST check
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception("Invalid request method.");
	}
	
	// rate limiting
	rate_limit('ban-'.$ip, 5, 1);
	
	// connect
	$db = new DB();
	
	// sanity check: was a name passed to the script?
	if(is_empty($banned_name)) {
		throw new Exception('Invalid name provided');
	}
	
	// check for permission
	$mod = check_moderator($db);
	
	// get variables from the mod variable
	$mod_user_id = $mod->user_id;
	$mod_user_name = $mod->name;
	$mod_power = 2;
	
	// more rate limiting
	rate_limit('ban-'.$mod_user_id, 5, 1);
	
	
	//limit ban length
	if($duration > $mod->max_ban) {
		$duration = $mod->max_ban;
	}
	$time = time();
	$expire_time = $time + $duration;
	
	
	//limit number of bans per hour
	$safe_mod_user_id = mysqli_real_escape_string($mod_user_id);
	$safe_min_time = mysqli_real_escape_string(time()-(60*60));
	$result = $db->query("SELECT COUNT(*) as recent_ban_count
									FROM bans
									WHERE mod_user_id = '$safe_mod_user_id'
									AND time > '$safe_min_time'");
	if(!$result) {
		throw new Exception('Could not check ban throttle');
	}
	$row = $result->fetch_object();
	$recent_ban_count = $row->recent_ban_count;
	
	if($recent_ban_count > $mod->bans_per_hour) {
		throw new Exception('You have reached the cap of '.$mod->bans_per_hour.' bans per hour.');
	}
	
	
	// get the banned user's info
	$result = $db->call('user_select_by_name', array($banned_name));
	
	// sanity check: does the user exist?
	if($result->num_rows <= 0){
		throw new Exception('The account you are trying to ban does not exist.');
	}
	
	$row = $result->fetch_object();
	$banned_ip = $row->ip;
	$banned_power = $row->power;
	$banned_user_id = $row->user_id;
	
	
	// override ip
	if( !is_empty($force_ip) ) {
		$banned_ip = $force_ip;
	}
	
	
	//throw out non-banned info, set ban types
	switch ($type) {
		case 'both':
			$ip_ban = 1;
			$account_ban = 1;
			break;
		case 'account':
			$ip_ban = 0;
			$account_ban = 1;
			break;
		case 'ip':
			$ip_ban = 1;
			$account_ban = 0;
			break;
		default:
			throw new Exception("Invalid ban type specified.");
			break;
	}
	
	
	// permission check
	if($mod_power <= $banned_power || $mod_power < 2){
		throw new Exception("You lack the power to ban $banned_name.");
	}
	
	
	// don't ban guest accounts, just the ip
	if($banned_power == 0){
		$banned_user_id = '0';
		$banned_name = '';
	}
	
	//add the ban
	$safe_mod_user_name = $db->real_escape_string($mod_user_name);
	$safe_ip_ban = $db->real_escape_string($ip_ban);
	$safe_account_ban = $db->real_escape_string($account_ban);
	$result = $db->query("INSERT INTO bans
									SET banned_ip = '$banned_ip',
										banned_user_id = $banned_user_id,
										mod_user_id = '$mod_user_id',
										time = $time,
										expire_time = $expire_time,
										reason = '$safe_reason',
										record = '$safe_record',
										banned_name = '$safe_banned_name',
										mod_name = '$safe_mod_user_name',
										ip_ban = '$safe_ip_ban',
										account_ban = '$safe_account_ban'");
	if(!$result){
		throw new Exception('Could not record ban.');
	}
	
	
	
	//remove login token
	$db->call('tokens_delete_by_user', array($banned_user_id));
	
	
	
	if($using_mod_site == 'yes' && $redirect == 'yes') {
		header('Location: //pr2hub.com/mod/player_info.php?user_id='.$banned_user_id.'&force_ip='.$force_ip);
	}
	else {
		if($banned_user_id == 0) {
			echo("message=Guest [$banned_ip] has been banned for $duration seconds.");
		}
		else {
			$disp_name = htmlspecialchars($banned_name);
			echo("message=$disp_name has been banned for $duration seconds.");
		}
	}
	
	// --- action log stuff below this point --- \\
	
	// make duration pretty
	$disp_duration = format_duration($duration);
	
	// make reason pretty
	if (!is_empty($reason)) {
		$disp_reason = "reason: $reason";
	}
	else {
		$disp_reason = "no reason given";
	}
	
	// get mod's IP
	$ip = $mod->ip;
	
	// make account/ip ban detection pretty courtesy of data_fns.php
	$is_account_ban = check_value($safe_account_ban, 1);
	$is_ip_ban = check_value($safe_ip_ban, 1);

	// make expire time pretty
	$disp_expire_time = date('Y-m-d H:i:s', $expire_time);
	
	//record the ban in the action log
	$db->call('mod_action_insert', array($mod->user_id, "$mod_user_name banned $banned_name from $ip {duration: $disp_duration, account_ban: $is_account_ban, ip_ban: $is_ip_ban, expire_time: $disp_expire_time, $disp_reason}", 0, $ip));
	
}

catch(Exception $e){
	$error = $e->getMessage();
	echo "error=$error";
}

?>
