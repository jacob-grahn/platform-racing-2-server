<?php
require_once(__DIR__ . '/../fns/all_fns.php');

try{
	//connecto!!!
	$db = new DB();


	//select all records and make sure those users have a top hat
	$result = $db->query('select user_id, type, part
							from part_awards');
	if(!$result){
		throw new Exception('Could not retrieve part awards.');
	}

	while($row = $result->fetch_object()) {
	    if($row->part == 0) {
		$part = '*';
	    }
	    else {
		$part = $row->part;
	    }
		$parts = array();
		$type = $row->type;
		$parts[] = $part;
		$user_id = $row->user_id;
		try {

			award_parts($db, $user_id, $type, $parts, false);
			echo "user_id: $user_id, type: $type, part: $parts \n";
		}
		catch(Exception $e){
			echo "Error: $e";
		}
	}


	//delete older records
	$result = $db->query('delete from part_awards
								 	WHERE DATE_SUB(CURDATE(),INTERVAL 5 DAY) > dateline');
	if(!$result){
		throw new Exception('Could not delete old award records.');
	}
}
catch(Exception $e){
	echo "Error: $e";
	exit();
}

?>
