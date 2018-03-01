<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$ban_id = (int) default_val($_POST['ban_id'], 0);
$reason = default_val($_POST['reason'], 'They bribed me with skittles!');
$safe_reason = addslashes($reason . ' @' . date('M j, Y g:i A'));
$ip = get_ip();

try {
	
	// POST check
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception("Invalid request method.");
	}

	// rate limiting
	rate_limit('mod-do-lift-ban-'.$ip, 5, 2);

	// connect
	$db = new DB();

	// make sure you're a moderator
	$mod = check_moderator($db);
	
}
catch(Exception $e) {
	$error = $e->getMessage();
	output_header("Error");
	echo "Error: $error";
	output_footer();
	die();
}

try {
	
	// sanity check: are any values blank?
	if(is_empty($ban_id, false) || is_empty($reason)) {
		throw new Exception('Some information is missing.');
	}
	
	// make some variables
	$user_id = $mod->user_id;
	$name = $mod->name;

	// safety first
	$safe_name = addslashes($name);

	// lift the ban
	$result = $db->query("UPDATE bans
									SET lifted = '1',
										lifted_by = '$safe_name',
										lifted_reason = '$safe_reason'
									WHERE ban_id = '$ban_id'
									LIMIT 1");
	if(!$result){
		throw new Exception('Could not lift ban.');
	}

	if ($reason != '') {
		$disp_reason = "Reason: $safe_reason";
	}
	else {
		$disp_reason = "There was no reason given";
	}
	
	//record the change
	$db->call('mod_action_insert', array($user_id, "$name lifted ban $ban_id from $ip. $disp_reason.", 0, $ip));


	//redirect to a page showing the lifted ban
	header("Location: //pr2hub.com/bans/show_record.php?ban_id=$ban_id");
	die();
	
}

catch(Exception $e) {
	$error = $e->getMessage();
	output_header('Lift Ban', true);
	echo "Error: $error";
	output_footer();
}


?>
