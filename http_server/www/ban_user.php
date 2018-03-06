<?php

header("Content-type: text/plain");

require_once('../fns/all_fns.php');
require_once('../queries/get_info/user.php'); // pdo
require_once('../queries/staff/action_log.php'); // pdo
require_once('../queries/staff/ban_user.php'); // pdo

$banned_name = default_val($_POST['banned_name']);
$duration = (int) default_val($_POST['duration'], 60);
$reason = default_val($_POST['reason'], '');
$record = default_val($_POST['record'], '');
$using_mod_site = default_val($_POST['using_mod_site'], 'no');
$redirect = default_val($_POST['redirect'], 'no');
$type = default_val($_POST['type'], 'both');
$force_ip = default_val($_POST['force_ip']);

$ip = get_ip();

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
	$pdo = pdo_connect();
	
	// sanity check: was a name passed to the script?
	if(is_empty($banned_name)) {
		throw new Exception('Invalid name provided');
	}
	
	// check for permission
	$mod = check_moderator($db);
	
	// get variables from the mod variable
	$mod_user_id = (int) $mod->user_id;
	$mod_user_name = $mod->name;
	$mod_power = $mod->power;
	
	// limit ban length
	if($duration > $mod->max_ban) {
		$duration = $mod->max_ban;
	}
	$time = (int) time();
	$expire_time = $time + $duration;
	
	
	// throttle bans using PDO
	$throttle = throttle_bans($pdo, $mod_user_id);
	$recent_ban_count = $throttle->recent_ban_count;
	if($recent_ban_count > $mod->bans_per_hour) {
		throw new Exception('You have reached the cap of '.$mod->bans_per_hour.' bans per hour.');
	}
	
	// get the banned user's info using PDO
	$target = user_select_by_name($pdo, $banned_name);
	if ($target === false) {
		throw new Exception("The user you're trying to ban doesn't exist.");
	}
	
	// make some variables
	$banned_ip = $target->ip;
	$banned_power = $target->power;
	$banned_user_id = $target->user_id;
	
	
	// override ip
	if(!is_empty($force_ip)) {
		$banned_ip = $force_ip;
	}
	
	
	//throw out non-banned info, set ban types
	$ip_ban = 0;
	$account_ban = 0;
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
		$banned_user_id = 0;
		$banned_name = '';
	}
	
	// add the ban using pdo
	$result = ban_user($pdo, $banned_ip, $banned_user_id, $mod_user_id, $expire_time, $reason, $record, $banned_name, $mod_user_name, $ip_ban, $account_ban);
	if($result === false){
		throw new Exception('Could not record ban.');
	}
	
	
	// remove login token
	$db->call('tokens_delete_by_user', array($banned_user_id));
	
	
	
	// --- action log stuff --- \\
	
	// make duration pretty
	$disp_duration = format_duration($duration);
	
	// make reason pretty
	$disp_reason = "reason: $reason";
	if (is_empty($reason)) {
		$disp_reason = "no reason given";
	}
	
	// get mod's IP
	$ip = $mod->ip;
	
	// make account/ip ban detection pretty courtesy of data_fns.php
	$is_account_ban = check_value($safe_account_ban, 1);
	$is_ip_ban = check_value($safe_ip_ban, 1);

	// make expire time pretty
	$disp_expire_time = date('Y-m-d H:i:s', $expire_time);
	
	// action log string
	$action_string = "$mod_user_name banned $banned_name from $ip {duration: $disp_duration, account_ban: $is_account_ban, ip_ban: $is_ip_ban, expire_time: $disp_expire_time, $disp_reason}";
	
	//record the ban in the action log
	//$db->call('mod_action_insert', array($mod->user_id, "$mod_user_name banned $banned_name from $ip {duration: $disp_duration, account_ban: $is_account_ban, ip_ban: $is_ip_ban, expire_time: $disp_expire_time, $disp_reason}", 0, $ip));
	log_mod_action($pdo, $action_string, $mod_user_id, $ip);
	
	// --- end action log stuff --- \\
	
	
	if($using_mod_site == 'yes' && $redirect == 'yes') {
		header('Location: //pr2hub.com/mod/player_info.php?user_id='.$banned_user_id.'&force_ip='.$force_ip);
		die();
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
	
}

catch(Exception $e){
	$error = $e->getMessage();
	echo "error=$error";
}

?>
