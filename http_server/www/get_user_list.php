<?php

require_once('../fns/all_fns.php');

$mode = $_POST['mode'];


try {
	
	if($mode == 'friends'){
		$table = 'friends';
		$var = 'friend_id';
	}
	else if($mode == 'ignored'){
		$table = 'ignored';
		$var = 'ignore_id';
	}
	else {
		throw new Exception('invalid mode.');
	}
	
	
	$db = new DB();
	
	//check thier login
	$user_id = token_login($db);
	
	$result = $db->query("select users.name, users.power, users.status, pr2.rank, pr2.hat_array, rank_tokens.used_tokens
									from $table
									inner join users
									on users.user_id = $table.$var
									left join pr2
									on users.user_id = pr2.user_id
									left join rank_tokens
									on users.user_id = rank_tokens.user_id
									where $table.user_id = '$user_id'
									limit 0, 250");
	
	if(!$result){
		throw new Exception('Could not retrieve player list.');
	}
	
	$num = 0;

	while($row = $result->fetch_object()){
		$name = urlencode($row->name);
		$group = $row->power;
		$status = $row->status;
		$rank = $row->rank;
		
		if(isset($row->used_tokens)) {
			$used_tokens = $row->used_tokens;
		}
		else {
			$used_tokens = 0;
		}
		
		$active_rank = $rank + $used_tokens;
		$hats = count(explode(',', $row->hat_array)) - 1;
		
		if(strpos($status, 'Playing on ') !== false){
			$status = substr($status, 11);
		}
		
		if($num > 0){
			echo "&";
		}
		
		echo ("name$num=$name"
				."&group$num=$group"
				."&status$num=$status"
				."&rank$num=$active_rank"
				."&hats$num=$hats");
		$num++;
	}
}

catch(Exception $e){
	echo 'error='.($e->getMessage());
}

?>
