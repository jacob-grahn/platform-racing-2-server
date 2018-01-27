<?php

require_once('../fns/all_fns.php');

$level_id = find('level_id', 'none');

try{	
	//error check
	if($level_id == 'none'){
		throw new Exception('No level id was given.');
	}
	
	//connect
	$db = new DB();
	
	//make sure the user is a moderator
	$mod = check_moderator($db);
	
	if($mod->can_unpublish_level != 1) {
		throw new Exception('You can not unpublish levels.');
	}
	
	//check for the level's information
	$level = $db->grab_row( 'level_select', array($level_id) );
	$l_title = $level->title;
	$l_creator = $level->user_id;
	$l_note = $level->note;
	
	
	//unpublish the level
	$db->call('level_unpublish', array($level_id));
	
	//tell it to the world
	echo 'message=This level has been removed successfully. It may take up to 60 seconds for this change to take effect.';
	
	//action log
	$name = $mod->name;
	$user_id = $mod->user_id;
	$ip = $mod->ip;
	
	//record the change
	$db->call('mod_action_insert', array($user_id, "$name unpublished level $level_id from $ip {level_title: $l_title, creator_id: $l_creator, level_note: $l_note}", $user_id, $ip));
	
}

catch(Exception $e){
	echo 'error='.$e->getMessage();
}

?>
