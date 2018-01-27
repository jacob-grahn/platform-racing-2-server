<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$ban_id = find('ban_id');
$reason = find('reason');
$safe_ban_id = addslashes($ban_id);
$safe_reason = addslashes($reason . ' @' . date('M j, Y g:i A'));

try {

	//sanity
	if(!isset($ban_id) || !isset($reason)) {
		throw new Exception('Invalid paramaters provided');
	}


	//connect
	$db = new DB();


	//make sure you're a moderator
	$mod = check_moderator($db);

	$user_id = $mod->user_id;
	$name = $mod->name;

	$safe_name = addslashes($name);


	//lift the ban
	$result = $db->query("UPDATE bans
									SET lifted = '1',
										lifted_by = '$safe_name',
										lifted_reason = '$safe_reason'
									WHERE ban_id = '$safe_ban_id'
									LIMIT 1");
	if(!$result){
		throw new Exception('Could not lift ban');
	}

	if ($reason != '') {
		$disp_reason = "Reason: " . $safe_reason;
	}
	else {
		$disp_reason = "There was no reason given";
	}
	
	//record the change
	$db->call('mod_action_insert', array($user_id, "$name lifted ban $ban_id. $disp_reason.", 0, get_ip()));


	//redirect to a page showing the lifted ban
	header("Location: show_record.php?ban_id=$ban_id") ;
}

catch(Exception $e){
	output_header('Error');
	echo 'Error: '.$e->getMessage();
	output_footer();
}


?>
