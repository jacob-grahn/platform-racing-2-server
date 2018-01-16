<?php

header("Content-type: text/plain");

require_once('../fns/all_fns.php');

$start = find('start', 0);
$count = find('count', 10);
$messages = array();
$largest_id = 0;

try{
	
	//connect the the db
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	
	$safe_user_id = $db->escape( $user_id );
	$safe_start = $db->escape( $start );
	$safe_count = $db->escape( $count );
	$result = $db->query("select messages.message_id, messages.message, messages.time, messages.from_user_id
									from messages
									where messages.to_user_id = '$safe_user_id'
									order by messages.time desc
									limit $safe_start, $safe_count");
	if(!$result){
		throw new Exception('Could not retrieve messages.');
	}
	
	
	while($row = $result->fetch_object()){
		
		if($row->message_id > $largest_id) {
			$largest_id = $row->message_id;
		}
		
		$from_user = $db->grab_row( 'user_select', array( $row->from_user_id ) );
		
		$message = new stdClass();
		$message->message_id = $row->message_id;
		$message->message = $row->message;
		$message->time = $row->time;
		$message->user_id = $row->from_user_id;
		$message->name = $from_user->name;
		$message->group = $from_user->power;
		
		$messages[] = $message;
	}
	
	if($start == 0) {
		$db->call('users_inc_read', array($user_id, $largest_id));
	}
	
	$r = new stdClass();
	$r->messages = $messages;
	$r->success = true;
	echo json_encode( $r );
}

catch(Exception $e){
	$r = new stdClass();
	$r->error = $e->getMessage();
	echo json_encode( $r );
}

?>