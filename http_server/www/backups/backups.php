<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');
require_once('../../fns/classes/S3.php');

output_header('Your Backups');

try {
	
	//connect
	$db = new DB();
	$user_id = token_login($db);
	
	
	//restore a backup
	$action = find('action');
	
	if($action == 'restore') {
		
		//get the level_id that this backup_id points to
		$backup_id = find('backup_id');
		$row = $db->grab_row('level_backups_select_one', array($backup_id));
		if($row->user_id != $user_id) {
			throw new Exception('You do not own this backup.');
		}
		
		//initialize some variables
		$level_id = $row->level_id;
		$version = $row->version;
		$title = $row->title;
		$ip = get_ip();
		$time = time();
		
		//connect
		$s3 = s3_connect();
		
		//pull the backup
		$file = $s3->getObject('pr2backups', "$level_id-v$version.txt");
		if(!$file) {
			throw new Exception('Could not load backup contents.');
		}
		$body = $file->body;
		
		//restore this backup to the db
		$new_version = $db->grab('version', 'levels_restore_backup', array($user_id, $title, $row->note, $row->live, $time, $ip, $row->min_level, $row->song, $level_id, $row->play_count, $row->votes, $row->rating, $version));
		
		
		//increment the version and recalculate the hash of the level body
		$str1 = "&version=$version";
		$str2 = "&version=$new_version";
		$body = str_replace($str1, $str2, $body);
		$len = strlen($body) - 32;
		$body = substr($body, 0, $len);
		$str_to_hash = $new_version . $level_id . $body . '0kg4%dsw';
		$hash = md5($str_to_hash);
		$body = $body . $hash;
		
		//write the backup to the level system
		$result = $s3->putObjectString($body, 'pr2levels1', "$level_id.txt");
		if(!$result) {
			throw new Exception('Could not restore backup.');
		}
		
		//success
		echo "<p><b>".htmlspecialchars($title)." v$version</b> restored successfully!</p>";
	}
	
	else {
		echo '<p>Welcome to PR2\'s level restore system. You can restore any level that was modified or deleted in the past month.</p>';
	}
	
	
	//display available backups
	echo '<br/>';
	$result = $db->call('level_backups_select', array($user_id));
	while($row = $result->fetch_object()) {
		echo "<p>$row->date: <b>".htmlspecialchars($row->title)."</b> v$row->version <a href='?action=restore&backup_id=$row->backup_id'>restore</a></p>";
	}
	
}

catch(Exception $e){
	echo '<p>Error: ' . $e->getMessage() . '</p>';
}

output_footer();
?>
