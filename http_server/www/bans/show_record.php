<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$ban_id = $_GET['ban_id'];
$safe_ban_id = addslashes($ban_id);



try{
	$db = new DB();

	$result = $db->query("select *
							from bans
							where ban_id = '$safe_ban_id'
							limit 0, 1");
	if(!$result){
		throw new Exception('Could not display ban record');
	}
	$row = $result->fetch_assoc();

	//output navigation if you're a moderator
	$is_mod = is_moderator($db, false);
	output_header('View Ban', $is_mod);
}
catch(Exception $e){
	echo 'Error: '.$e->getMessage();
	exit;
}


//--- output the page ---
$ban_id = $row['ban_id'];
$banned_ip = $row['banned_ip'];
$mod_user_id = $row['mod_user_id'];
$banned_user_id = $row['banned_user_id'];
$time = $row['time'];
$expire_time = $row['expire_time'];
$reason = $row['reason'];
$record = $row['record'];
$mod_name = $row['mod_name'];
$banned_name = $row['banned_name'];
$lifted = $row['lifted'];
$lifted_by = $row['lifted_by'];
$lifted_reason = $row['lifted_reason'];
$ip_ban = $row['ip_ban'];
$account_ban = $row['account_ban'];
$notes = $row['notes'];

$formatted_time = date('M j, Y g:i A', $time);
$expire_formatted_time = date('M j, Y g:i A', $expire_time);
$duration = $expire_time - $time;
$f_duration = format_duration( $duration );

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

$html_lifted_by = htmlspecialchars($lifted_by);
$html_lifted_reason = htmlspecialchars($lifted_reason);
$html_mod_name = htmlspecialchars($mod_name);
$html_banned_name = htmlspecialchars($display_name);
$html_reason = htmlspecialchars($reason);
$html_record = str_replace("\r", '<br/>',htmlspecialchars($record));
$html_notes = str_replace("\n", '<br>', htmlspecialchars($notes));


if($lifted == 1) {
	echo 	'<b><p>-----------------------------------------------------------------------------------------------</p>'
			."<p>--- This ban has been lifted by $html_lifted_by ---</p>"
			."<p>--- Reason: $html_lifted_reason ---</p>"
			.'<p>-----------------------------------------------------------------------------------------------</p>'
			.'<p>&nbsp;</p></b>';
}



//make the names clickable for moderators
if($is_mod === true) {
	$html_mod_name = "<a href='/mod/player_info.php?user_id=$mod_user_id'>$html_mod_name</a>";
	if($banned_user_id != 0 && $account_ban == 1) {
		$html_banned_name = "<a href='/mod/player_info.php?user_id=$banned_user_id'>$html_banned_name</a>";
	}
	else {
		$html_banned_name = "<a href='/mod/player_info.php?ip=$banned_ip'>$html_banned_name</a>";
	}
}


echo "<p>$html_mod_name banned $html_banned_name for $f_duration on $formatted_time.</p>
		<p>Reason: $html_reason</p>
		<p>This ban will expire on $expire_formatted_time.</p>
		<p> --- </p>
		<p>$html_record</p>
		<p> --- </p>";

if($is_mod === true) {
    if(isset($notes) && $notes != '') {
	    echo "<p> --- notes</p>";
	    echo "<p>$html_notes</p>";
	    echo "<p> ---</p>";
    }
    if($lifted != 1) {
	echo "<p><a href='/mod/ban_edit.php?ban_id=$ban_id'>Edit Ban</a></p>";
	echo "<p><a href='/mod/lift_ban.php?ban_id=$ban_id'>Lift Ban</a></p>";
    }
}

echo '<p><a href="bans.php">Go Back</a></p>';

output_footer();
?>
