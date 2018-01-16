<?php
require_once('../fns/all_fns.php');

$message_id = $_POST['message_id'];

$ip = get_ip();
$time = time();

$safe_message_id = addslashes($message_id);
$safe_reporter_ip = addslashes($ip);
$safe_time = addslashes($time);


try{
	
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db, false);
	
	
	
	//pull the selected message from the db
	$result = $db->query("SELECT *
								 	FROM messages
									WHERE message_id = '$safe_message_id'
									LIMIT 0, 1");
	if(!$result){
		throw new Exception('Could not retrieve message.');
	}
	if($result->num_rows <= 0) {
		throw new Exception('The message was not found. '.$safe_message_id);
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
		throw new Exception('Could not record the reported message. Maybe this message has been reported already?');
	}
	
	
	
	
	
	//tell it to the world
	echo 'message=The message was reported succesfully!';		
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

?>