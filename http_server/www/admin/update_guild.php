<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$guild_id = find('guild_id');
$action = find('action', 'lookup');

try {
	
	//connect
	$db = new DB();


	//make sure you're an admin
	$admin = check_moderator($db, true, 3);
	
	
	//lookup
	if($action === 'lookup') {
	output_form($db, $guild_id);
	}
	
	
	//update
	if($action === 'update') {
	update($db);
	}


}

catch (Exception $e) {
	output_header('Error');
	echo 'Error: ' . $e->getMessage();
	output_footer();
}


function output_form($db, $guild_id) {
	
	output_header('Update Guild', true, true);
	
	echo '<form name="input" action="update_guild.php" method="get">';
	
	$guild = $db->grab_row('guild_select', array($guild_id));
	echo "guild_id: $guild->guild_id <br>---<br>";

	
	echo 'Guild Name: <input type="text" size="" name="guild_name" value="'.htmlspecialchars($guild->guild_name).'"><br>';
	echo 'Guild Owner: <input type="text" size"" name="owner_id" value="'.htmlspecialchars($guild->owner_id).'"><br>';
	echo 'Prose: <textarea rows="4" name="note">'.htmlspecialchars($guild->note).'</textarea><br>';
	echo 'Delete Emblem? <input type="checkbox" name="delete_emblem"><br>';
	echo 'Description of Changes: <input type="text" size="100" name="changes"><br>';
	echo '<input type="hidden" name="action" value="update">';
	echo '<input type="hidden" name="guild_id" value="'.$guild->guild_id.'">';
	
	echo '<br/>';
	echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
	echo '</form>';
	
	echo '<br>';
	echo '---';
	echo '<br>';
	echo '<pre>When making changes, use the "Description of Changes" box to summarize what you did.<br><br>To replace the guild owner, get the user ID of the user you want to make the owner.<br>Then, replace the previous one in the guild owner field.<br><br>NOTE: You MUST make sure that the person you\'re making the owner is already in the guild.</pre>';
	
	output_footer();
}

function update($db) {
	
	global $admin;

	//make some nice-looking variables
	$guild = $db->grab_row('guild_select', array($guild_id));
	$guild_id = (int) find('guild_id');
	$guild_name = find('guild_name');
	$note = find('note');
	$owner_id = (int) find('owner_id');
	$changes = find('changes');
	
	//check to see if the admin is trying to delete the guild emblem
	if (!empty($_GET['delete_emblem'])) {
		$emblem = "default-emblem.jpg";
	}
	else {
		$emblem = $guild->emblem;
	}
	
	try {

		if($changes == "" || empty($changes) || !isset($changes) || strlen(trim($changes)) === 0) {
			throw new Exception('The description of changes cannot be blank.');
		}
		
		$db->call(
			'guild_update',
			array(
			$guild_id,
			$guild_name,
			$emblem,
			$note,
			$owner_id
			)
		);
	
		//admin log
		$admin_name = $admin->name;
		$admin_id = $admin->user_id;
		$ip = get_ip();
		$disp_changes = "Changes: " . $changes;
		
		$db->call('admin_action_insert', array($admin_id, "$admin_name updated guild $guild_id from $ip. $disp_changes.", $admin_id, $ip));

	}
	catch (Exception $e) {
		output_header('Update PR2 Account', true, true);
		echo 'Error: ' . $e->getMessage();
		output_footer();
	}
	
	header("Location: guild_deep_info.php?guild_id=" . urlencode(find('guild_id')));
	die();

}

?>
