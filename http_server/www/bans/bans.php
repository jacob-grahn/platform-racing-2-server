<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

try{
	rate_limit('list-bans-' . get_ip(), 60, 10);

	$db = new DB();
	$result = $db->query('select *
									from bans
									order by time desc
									limit 0, 100');
	if(!$result){
		throw new Exception('Could not retireve bans');
	}

	$is_mod = is_moderator($db, false);
	output_header('Ban Log', $is_mod);
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

output_footer();

?>
