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
	
	//unpublish the level
	$db->call('level_unpublish', array($level_id));
	
	//tell it to the world
	echo 'message=This level has been removed successfully. It may take up to 60 seconds for this change to take effect.';
}

catch(Exception $e){
	echo 'error='.$e->getMessage();
}

?>