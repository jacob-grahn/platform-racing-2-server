<?php

header("Content-type: text/plain");

require_once('../fns/all_fns.php');

$message_id = (int) $_POST['message_id'];
$time = (int) time();
$ip = get_ip();

try {

	// POST check
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception('Invalid request method.');
	}
	
	// rate limiting
	rate_limit('message-report-'.$ip, 5, 1, "Please wait at least 5 seconds before trying to report another PM.");
	rate_limit('message-report-'.$ip, 60, 5);
	
	// connect
	$db = new DB();
	
	// check their login
	$user_id = token_login($db, false);
	
	// more rate limiting
	rate_limit('message-report-'.$user_id, 60, 5);
	
	// make sure the message isn't already reported
	$result = $db->query("SELECT COUNT(*)
								AS count
								FROM messages_reported
							 	WHERE message_id = '$message_id'
								");
	if(!$result) {
		throw new Exception('Could not check if the message was already reported.');
	}
	$row = $result->fetch_object();
	$count = (int) $row->count;
	if ($count > 0) {
		throw new Exception("It looks like you've already reported this message before.");
	}

	// pull the selected message from the db
	$result = $db->query("SELECT *
								FROM messages
								WHERE message_id = '$message_id'
								LIMIT 0, 1");
	if(!$result){
		throw new Exception('Could not retrieve message.');
	}
	if($result->num_rows <= 0) {
		throw new Exception("The message you tried to report ($message_id) doesn't exist.");
	}
	
	
	// make sure this user is the recipient of this message
	$row = $result->fetch_object();
	if($row->to_user_id != $user_id) {
		throw new Exception('This message was not sent to you.');
	}
	
	
	// insert the message into the reported messages table
	$to_user_id = (int) $row->to_user_id;
	$from_user_id = (int) $row->from_user_id;
	$sent_time = (int) $row->time;
	$safe_message = mysqli_real_escape_string($row->message);
	$safe_from_ip = mysqli_real_escape_string($row->ip);
	$safe_reporter_ip = mysqli_real_escape_string($ip);
	
	$result = $db->query("INSERT INTO messages_reported
								 	SET to_user_id = '$to_user_id',
										from_user_id = '$from_user_id',
										reporter_ip = '$safe_reporter_ip',
										from_ip = '$safe_from_ip',
										sent_time = '$sent_time',
										reported_time = '$time',
										message_id = '$message_id',
										message = '$safe_message'");
	
	if(!$result){
		throw new Exception('Could not record the reported message.');
	}
	
	
	
	
	
	// tell it to the world
	echo 'message=The message was reported successfully!';

}

catch(Exception $e){
	$error = $e->getMessage();
	echo "error=$error";
}

?>
