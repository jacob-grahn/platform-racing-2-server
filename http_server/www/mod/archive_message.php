<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$message_id = find('message_id');
$safe_message_id = addslashes($message_id);


	//connect
	$db = new DB();	


	//make sure you're a moderator
	$mod = check_moderator($db);
	
	//look for a player with provided name
	$result = $db->query("update messages_reported
							set archived = 1
							where message_id = '$safe_message_id'
							LIMIT 1");
	if(!$result) {
		throw new Exception('Could not mark message as archived.');
	}

	//action log
	$name = $mod->name;
	$ip = $mod->ip;
		
	//record the change
	$db->call('mod_action_insert', array($mod->user_id, "$name archived the report of PM $safe_message_id from $ip", $mod->user_id, $ip));
	

?>
