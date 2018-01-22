<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$user_id = find('user_id');
$ip = find('ip');
$force_ip = find('force_ip');

$safe_user_id = addslashes($user_id);

$banned = 'No';

try {

	//connect
	$db = new DB();

	//make sure you're a moderator
	$mod = check_moderator($db, false);

	// header
	output_header('Player Info', true);

	if(isset($user_id) && $user_id != 0) {

		//get dem infos
		$result = $db->query("select pr2.rank, pr2.hat_array, users.power, users.status, users.name, users.ip
										from users left join pr2
										on users.user_id = pr2.user_id
										where users.user_id = '$safe_user_id'
										limit 0, 1");

		if(!$result){
			throw new Exception('Could not retieve player info.');
		}

		$row = $result->fetch_object();
		$rank = $row->rank;
		$hat_array = $row->hat_array;
		$status = $row->status;
		$ip = $row->ip;
		$user_name = $row->name;

		$hats = count(explode(',', $hat_array))-1;

		//--- count how many times they have been banned
		$account_bans = retrieve_bans($db, $user_id, 'account');
		$account_ban_count = $account_bans->num_rows;
		$account_ban_list = create_ban_list($account_bans);
		if($account_ban_count == 1) {
			$s1 = '';
		}
		else {
			$s1 = 's';
		}
	}

	//override ip
	$overridden_ip = '';
	if( isset( $force_ip ) && $force_ip != '' ) {
		$overridden_ip = $ip;
		$ip = $force_ip;
	}

	//check if they are currently banned
	$row = query_if_banned( $db, $user_id, $ip );

	//give some more info on the current ban in effect if there is one
	if( $row !== false ) {
		$ban_id = $row->ban_id;
		$reason = htmlspecialchars($row->reason);
		$ban_end_date = date("F j, Y, g:i a", $row->expire_time);
		if( $row->ip_ban == 1 && $row->account_ban == 1 && $row->banned_name == $user_name ) {
			$ban_type = 'account and ip are';
		}
		else if( $row->ip_ban == 1 ) {
			$ban_type = 'ip is';
		}
		else if( $row->account_ban == 1 ) {
			$ban_type = 'account is';
		}
		$banned = "<a href='../bans/show_record.php?ban_id=$ban_id'>Yes.</a> This $ban_type banned until $ban_end_date. Reason: $reason";
	}


	//look for all historical bans given to this ip address
	$ip_bans = retrieve_bans($db, $ip, 'ip');
	$ip_ban_count = $ip_bans->num_rows;
	$ip_ban_list = create_ban_list($ip_bans);
	if($ip_ban_count == 1) {
		$s2 = '';
	}
	else {
		$s2 = 's';
	}


	//output the results
	if(isset($user_id) && $user_id != 0) {
		$html_user_name = htmlspecialchars($user_name);
		echo "<p>Name: <b>$html_user_name</b></p>"
				."<p>IP: <del>".htmlspecialchars($overridden_ip)."</del> ".htmlspecialchars($ip)."</p>"
				."<p>Status: $status</p>"
				."<p>Rank: $rank<p>"
				."<p>Hats: $hats<p>"
				."<p>Currently banned: $banned</p>"
				."<p>Account has been banned $account_ban_count time$s1.</p> $account_ban_list"
				."<p>IP has been banned $ip_ban_count time$s2.</p> $ip_ban_list"
				.'<p>---</p>'
				."<p><a href='ban.php?user_id=$user_id&force_ip=$force_ip'>Ban User</a></p>";
	}
	else {
		echo "<p>IP: $ip</p>"
				."<p>Currently banned: $banned</p>"
				."<p>IP has been banned $ip_ban_count time$s2.</p> $ip_ban_list";
	}
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

output_footer();



function create_ban_list($result) {
	if($result->num_rows <= 0) {
		return '';
	}
	else {
		$str = '<p><ul>';
		while($row = $result->fetch_object()) {
			$ban_date = date("F j, Y, g:i a", $row->time);
			$reason = htmlspecialchars($row->reason);
			$ban_id = $row->ban_id;
			$str .= "<li><a href='../bans/show_record.php?ban_id=$ban_id'>$ban_date:</a> $reason";
		}
		$str .= '</ul></p>';
		return $str;
	}
}

?>
