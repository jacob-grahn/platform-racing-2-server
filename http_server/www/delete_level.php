<?php

require_once('../fns/all_fns.php');
require_once('../fns/classes/S3.php');
require_once('../fns/pr2_fns.php');

$level_id = find('level_id', 'none');

try {
	//error check
	if($level_id == 'none'){
		throw new Exception('No level id was given.');
	}
	
	//connect
	$db = new DB();
	$s3 = s3_connect();
	
	//check thier login
	$user_id = token_login($db, false);	
	
	//save this file to the backup system
	$row = $db->grab_row('levels_select_one', array($level_id, $user_id));
	backup_level($db, $s3, $user_id, $level_id, $row->version, $row->title, $row->live, $row->rating, $row->votes, $row->note, $row->min_level, $row->song, $row->play_count);
	
	//delete the level in the db
	$db->call('level_delete', array($level_id, $user_id));
	
	//delete the file from s3
	$result = $s3->deleteObject('pr2levels1', $level_id.'.txt');
	if(!$result) {
		throw new Exception('A server error was encountered. Your level could not be deleted.');
	}
	
	//
	echo 'success=true';
}

catch(Exception $e) {
	echo 'error='.$e->getMessage();
}

?>