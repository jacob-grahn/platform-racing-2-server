<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$message_id = (int) default_val($_GET['message_id'], 0);
$safe_message_id = mysqli_real_escape_string($message_id);
$ip = get_ip();

try {

	// rate limiting
	rate_limit('mod-archive-message-'.$ip, 3, 1);
	rate_limit('mod-archive-message-'.$ip, 15, 3);

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
	
	// more rate limiting
	$mod_id = $mod->user_id;
	rate_limit('mod-archive-message-'.$mod_id, 3, 1);
	rate_limit('mod-archive-message-'.$mod_id, 15, 3);
	
	// archive the message
	$result = $db->query("UPDATE messages_reported
							SET archived = 1
							WHERE message_id = '$safe_message_id'
							LIMIT 1");
	if(!$result) {
		throw new Exception('Could not archive the message.');
	}

	// action log
	$name = $mod->name;
	$ip = $mod->ip;

	// record the change
	$db->call('mod_action_insert', array($mod->user_id, "$name archived the report of PM $safe_message_id from $ip", $mod->user_id, $ip));

	// tell the sorry saps trying to debug
	$ret = new stdClass();
	$ret->success = true;
	$ret->message_id = $message_id;
	echo json_encode($ret);

}
catch(Exception $e) {
	$ret = new stdClass();
	$ret->success = false;
	$ret->error = $e->getMessage();
	$ret->message_id = $message_id;
	echo json_encode($ret);
}

?>
