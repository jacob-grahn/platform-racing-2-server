<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$start = (int) default_val($_GET['start'], 0);
$count = (int) default_val($_GET['count'], 100);
$ip = get_ip();

try {
	
	// rate limiting
	rate_limit('list-bans-'.$ip, 5, 3);

	// connect
	$db = new DB();
	
	// header, also check if mod and output the mod links if so
	$is_mod = is_moderator($db, false);
	output_header('Ban Log', $is_mod);
	
	// navigation
	output_pagination($start, $count);
	echo('<p>---</p>');
	
	if ($is_mod === false) {
		rate_limit('list-bans-'.$ip, 60, 10);
		if ($count - $start > 100) {
			$count = $start + 100;
		}
	}
	
	$result = $db->query("SELECT *
									FROM bans
									ORDER BY time DESC
									LIMIT $start, $count");
	if(!$result){
		throw new Exception('Could not retrieve the ban list.');
	}
}
catch(Exception $e){
	echo 'Error: '.$e->getMessage();
	exit;
}


//--- output the page ---

$out = '';

while($row = $result->fetch_assoc()){
	$ban_id = $row['ban_id'];
	$banned_ip = $row['banned_ip'];
	$mod_user_id = $row['mod_user_id'];
	$banned_user_id = $row['banned_user_id'];
	$time = $row['time'];
	$expire_time = $row['expire_time'];
	$reason = $row['reason'];
	$mod_name = $row['mod_name'];
	$banned_name = $row['banned_name'];
	$ip_ban = $row['ip_ban'];
	$account_ban = $row['account_ban'];

	$formatted_time = date('M j, Y g:i A',$time);
	$duration = $expire_time - $time;

	$display_name = '';
	if( $account_ban == 1 ) {
		$display_name .= $banned_name;
	}
	if( $ip_ban == 1 && $is_mod ) {
		if( $display_name != '' ) {
			$display_name .= ' ';
		}
		$display_name .= "[$banned_ip]";
	}

	$reason = htmlspecialchars($reason);
	$f_duration = format_duration( $duration );

	$out .="<p>$formatted_time
			<a href='show_record.php?ban_id=$ban_id'>
			".htmlspecialchars($mod_name)." banned ".htmlspecialchars($display_name)." for $f_duration.</a><br/>
			Reason: ".htmlspecialchars($reason)."
			</p>";
}

//end_page($out);
echo $out;

echo('<p>---</p>');
output_pagination($start, $count);

output_footer();

function output_pagination($start, $count) {
	$next_start_num = $start + $count;
	$last_start_num = $start - $count;
	if($last_start_num < 0) {
		$last_start_num = 0;
	}
	echo('<p>');
	if($start > 0) {
		echo("<a href='?start=$last_start_num&count=$count'><- Last</a> |");
	}
	else {
		echo('<- Last |');
	}
	echo(" <a href='?start=$next_start_num&count=$count'>Next -></a></p>");
}

?>
