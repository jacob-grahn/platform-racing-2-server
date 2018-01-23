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
	
?>
