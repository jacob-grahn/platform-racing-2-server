<?php

require_once('../../fns/all_fns.php');

$name = find('name', '');
$safe_name = addslashes($name);

try {

	//sanity
	if($name == '') {
		throw new Exception('Invalid search name');
	}

	//connect
	$db = new DB();


	//make sure you're a moderator
	$mod = check_moderator($db);


	//look for a player with provided name
	$result = $db->query("select user_id
								 	from users
									where name = '$safe_name'
									limit 0, 1");
	if(!$result) {
		throw new Exception('Could not search for user.');
	}
	if($result->num_rows <= 0) {
		throw new Exception("User $name was not found.");
	}

	$row = $result->fetch_object();
	$user_id = $row->user_id;

	header('Location: player_info.php?user_id='.$user_id);
}

catch(Exception $e){
	header('Location: player_search.php?message='.$e->getMessage());
}

?>
