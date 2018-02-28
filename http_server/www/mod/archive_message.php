<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$message_id = (int) default_val($_GET['message_id'], 0);
$safe_message_id = mysqli_real_escape_string($message_id);

try {

	// rate limiting
	rate_limit('mod-archive-message-'.$ip, 5, 1);
	rate_limit('mod-archive-message-'.$ip, 30, 5);

	// connect
	$db = new DB();	
	
	// make sure you're a moderator
	$mod = check_moderator($db);
		
	// look for a player with provided name
	$result = $db->query("UPDATE messages_reported
							SET archived = 1
							WHERE message_id = '$safe_message_id'
							LIMIT 1");
	if(!$result) {
		throw new Exception('Could not mark message as archived.');
	}
	
	//action log
	$name = $mod->name;
	$ip = $mod->ip;
		
	//record the change
	$db->call('mod_action_insert', array($mod->user_id, "$name archived the report of PM $safe_message_id from $ip", $mod->user_id, $ip));

}
catch(Exception $e) {
	$error = $e->getMessage();
	echo "Error: $error";
	die();
}

?>
