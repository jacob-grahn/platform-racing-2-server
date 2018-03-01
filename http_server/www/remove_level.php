<?php

header("Content-type: text/plain");

require_once(__DIR__ . '/../fns/all_fns.php');
require_once(__DIR__ . '/../queries/levels/level_unpublish.php');

$level_id = (int) default_val($_POST['level_id']);

try {
	
	// check for post
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new Exception("Invalid request method.");
	}
	
	// sanity check: was a level ID specified?
	if(is_empty($level_id, false)){
		throw new Exception('No level ID was specified.');
	}
	
	// rate limiting
	rate_limit('remove-level-'.$ip, 3, 1);
	
	// connect
	$db = new DB();
	$pdo = pdo_connect();
	
	// make sure the user is a moderator
	$mod = check_moderator($db);
	
	// more rate limiting
	rate_limit('remove-level-'.$mod->user_id, 3, 1);
	
	// make sure the user is a permanent moderator
	if($mod->can_unpublish_level != 1) {
		throw new Exception('You can not unpublish levels.');
	}
	
	// check for the level's information
	$level = $db->grab_row('level_select', array($level_id));
	$l_title = $level->title;
	$l_creator = id_to_name($db, $level->user_id);
	$l_note = $level->note;
	
	// unpublish the level
	// $db->call('level_unpublish', array($level_id));
	level_unpublish($pdo, $level_id);
	
	//tell it to the world
	echo 'message=This level has been removed successfully. It may take up to 60 seconds for this change to take effect.';
	
	//action log
	$name = $mod->name;
	$user_id = $mod->user_id;
	$ip = $mod->ip;
	
	//record the change
	$db->call('mod_action_insert', array($user_id, "$name unpublished level $level_id from $ip {level_title: $l_title, creator: $l_creator, level_note: $l_note}", $user_id, $ip));
	
}

catch (Exception $e){
	$error = $e->getMessage();
	echo "error=$error";
}

?>
