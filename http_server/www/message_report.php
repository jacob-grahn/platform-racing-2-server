<?php
require_once('../fns/all_fns.php');

$message_id = $_POST['message_id'];

$ip = get_ip();
$time = time();

$safe_message_id = addslashes($message_id);
$safe_reporter_ip = addslashes($ip);
$safe_time = addslashes($time);


try {
	
	$db = new DB();
	
	//check their login
	$user_id = token_login($db, false);
	
	
	//make sure the message isn't already reported
	$result = $db->query("SELECT COUNT(*)
								FROM messages_reported
							 	WHERE message_id = '$safe_message_id'
								");
	$count = $result->fetch_object();
	if(!$result) {
		throw new Exception('Could not check if the message was already reported.');
	}
	// debugging
	if($user_id === 3483035) { // 3483035 is bls1999's user ID. trying to figure out what the heckin problem is.
		$data = var_dump($result);
		$data_count = var_dump($count);
		throw new Exception("Welcome, bls1999. Below, you will find the data that was returned from the server.<br><br>Raw data: $data<br><br>Count data: $data_count");
	}
	
	// DEBUGGING; TEMPORARILY DISABLES MESSAGE REPORTING
	throw new Exception("Message reporting is currently disabled. If you still wish to report a PM, you may do so by registering and posting in the Ask a Mod forum on https://jiggmin2.com/forums.<br><br>We apologize for the inconvenience and hope to have message reporting up and running again soon.<br><br>- PR2 Staff");
	
	//pull the selected message from the db
	$result = $db->query("SELECT *
								FROM messages
								WHERE message_id = '$safe_message_id'
								LIMIT 0, 1");
	if(!$result){
		throw new Exception('Could not retrieve message.');
	}
	if($result->num_rows <= 0) {
		throw new Exception("The message you tried to report ($safe_message_id) doesn't exist.");
	}
	
	
	//make sure this user is the recipient of this message
	$row = $result->fetch_object();
	if($row->to_user_id != $user_id) {
		throw new Exception('This message was not sent to you.');
	}
	
	
	//insert the message into the reported messages table
	$safe_to_user_id = addslashes($row->to_user_id);
	$safe_from_user_id = addslashes($row->from_user_id);
	$safe_message = addslashes($row->message);
	$safe_sent_time = addslashes($row->time);
	$safe_from_ip = addslashes( $row->ip );
	
	$result = $db->query("INSERT INTO messages_reported
								 	SET to_user_id = '$safe_to_user_id',
										from_user_id = '$safe_from_user_id',
										reporter_ip = '$safe_reporter_ip',
										from_ip = '$safe_from_ip',
										sent_time = '$safe_sent_time',
										reported_time = '$safe_time',
										message_id = '$safe_message_id',
										message = '$safe_message'");
	
	if(!$result){
		throw new Exception('Could not record the reported message.');
	}
	
	
	
	
	
	//tell it to the world
	echo 'message=The message was reported successfully!';

}

catch(Exception $e){
	echo 'error=' . $e->getMessage();
}

?>
