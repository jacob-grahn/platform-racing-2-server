<?php

if (!headers_sent()){
	header("Content-Type: text/plain");	
}

require_once('../fns/all_fns.php');

$start = find('start');
$count = find('count');

$safe_start = addslashes($start);
$safe_count = addslashes($count);

try{
	
	//connect the the db
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	
	
	$result = $db->query("select messages.message_id, messages.message, messages.time, messages.from_user_id
									from messages
									where messages.to_user_id = '$user_id'
									order by messages.time desc
									limit $safe_start, $safe_count");
	if(!$result){
		throw new Exception('Could not retrieve messages.');
	}
	
	
	$num = 0;
	$largest_id = 0;
	while($row = $result->fetch_object()){
		$message_id = $row->message_id;
		$message = urlencode($row->message);
		$time = $row->time;
		$from_user_id = $row->from_user_id;
		
		if($message_id > $largest_id) {
			$largest_id = $message_id;
		}
		
		//lookup the user who sent this pm
		$user_result = $db->query("select name, power
										  	from users
											where user_id = '$from_user_id'
											limit 0, 1");
		
		if(!$user_result){
			throw new Exception('Could not look up user.');
		}
		
		$user_row = $user_result->fetch_object();
		$name = urlencode($user_row->name);
		$group = $user_row->power;
		
		//output the results
		if($num > 0) {
			echo '&';
		}
		echo "message_id$num=$message_id"
				."&name$num=$name"
				."&group$num=$group"
				."&message$num=$message"
				."&time$num=$time"
				."&user_id$num=$from_user_id";
		$num++;
	}
	
	if($start == 0) {
		$db->call('users_inc_read', array($user_id, $largest_id));
	}
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

?>
