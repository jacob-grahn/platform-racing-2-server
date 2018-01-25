<?php
require_once('../fns/all_fns.php');

$banned_name = find('banned_name', '');
$duration = find('duration', 60);
$reason = find('reason', '');
$record = find('record', '');
$using_mod_site = find('using_mod_site', 'no');
$redirect = find('redirect', 'no');
$type = find('type', 'both');
$force_ip = find('force_ip');

$safe_banned_name = addslashes($banned_name);
$safe_reason = addslashes($reason);
$safe_record = addslashes($record);

try{
	$db = new DB();
	
	//sanity
	if($banned_name == '') {
		throw new Exception('invalid name provided');
	}
	if($type != 'both' && $type != 'ip' && $type != 'account') {
		throw new Exception('Invalid ban type');
	}
	
	$mod = check_moderator($db);
	$mod_user_id = $mod->user_id;
	$mod_user_name = $mod->name;
	$mod_power = 2;
	
	
	//limit ban length
	if($duration > $mod->max_ban) {
		$duration = $mod->max_ban;
	}
	$time = time();
	$expire_time = $time + $duration;
	
	
	//limit number of bans per hour
	$safe_mod_user_id = addslashes($mod_user_id);
	$safe_min_time = addslashes(time()-(60*60));
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
	
	
	//get the banned user's info
	$result = $db->call('user_select_by_name', array($banned_name));
	
	if($result->num_rows <= 0){
		throw new Exception('The account you are trying to ban does not exist.');
	}
	
	$row = $result->fetch_object();
	$banned_ip = $row->ip;
	$banned_power = $row->power;
	$banned_user_id = $row->user_id;
	
	
	//override ip
	if( isset( $force_ip ) && $force_ip != '' ) {
		$banned_ip = $force_ip;
	}
	
	
	//throw out non banned-info
	if($type == 'both') {
		$ip_ban = 1;
		$account_ban = 1;
	}
	else if($type == 'account') {
		$ip_ban = 0;
		$account_ban = 1;
		//$banned_ip = '';
	}
	else if($type == 'ip') {
		$ip_ban = 1;
		$account_ban = 0;
		//$banned_user_id = 0;
	}
	
	
	//error check
	if($mod_power <= $banned_power || $mod_power < 2){
		throw new Exception('You lack the power to ban '.$banned_name." $mod_power <= $banned_power");
	}
	
	
	//don't ban guest accounts, just the ip
	if($banned_power == 0){
		$banned_user_id = '0';
		$banned_name = '';
	}
	
	//add the ban
	$safe_mod_user_name = $db->real_escape_string($mod_user_name);
	$safe_ip_ban = $db->real_escape_string($ip_ban);
	$safe_account_ban = $db->real_escape_string($account_ban);
	$result = $db->query("insert into bans
									set banned_ip = '$banned_ip',
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
		header('Location: http://pr2hub.com/mod/player_info.php?user_id='.$banned_user_id.'&force_ip='.$force_ip);
	}
	else {
		if($banned_user_id == 0) {
			echo("message=Guest [$banned_ip] has been banned for $duration seconds.");
		}
		else {
			echo("message=$banned_name has been banned for $duration seconds.");
		}
	}
	
	// ------- action log stuff below this point --------
	// make duration pretty
	switch ($duration) {
		case 60:
			$disp_duration = 'minute';
			break;
		case 3600:
			$disp_duration = 'hour';
			break;
		case 86400:
			$disp_duration = 'day';
			break;
		case 604800:
			$disp_duration = 'pr2 week (8 days)';
			break;
		case 2419200:
			$disp_duration = 'pr2 month (28 days)';
			break;
		case 29030400:
			$disp_duration = 'pr2 year (11 months)';
			break;
		// if all else fails, echo the seconds
		default:
			$disp_duration = $duration.' seconds';
			break;
	}
	// make reason pretty
	if ($safe_reason != '') {
		$disp_reason = "reason: " . $safe_reason;
	}
	else {
		$disp_reason = "no reason given";
	}
	// get mod's IP
	$ip = $mod->ip;
	// make account/ip ban detection pretty
	if($safe_account_ban === 1) {
		$is_account_ban = 'yes';
	}
	else {
		$is_account_ban = 'no';
	}
	if($safe_ip_ban === 1) {
		$is_ip_ban = 'yes';
	}
	else {
		$is_ip_ban = 'no';
	}
	// make expire time pretty
	$disp_expire_time = date('Y-m-d H:i:s', $expire_time);
	
	//record the ban in the action log
	$db->call('mod_action_insert', array($mod->user_id, "$mod_user_name banned $banned_name from $ip {duration: $disp_duration, account_ban: $is_account_ban, ip_ban: $is_ip_ban, expire_time: $disp_expire_time, reason: $disp_reason}", 0, $ip));
	
}

catch(Exception $e){
	echo 'message=Error: '.$e->getMessage();
}

?>
