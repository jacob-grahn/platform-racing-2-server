<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$guild_id = find('guild_id');
$action = find('action', 'lookup');
$ip = get_ip();

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
	echo 'Description of Changes: <input type="text" size="100" name="guild_changes"><br>';
	echo '<input type="hidden" name="action" value="update">';
	echo '<input type="hidden" name="guild_id_submit" value="'.$guild->guild_id.'">';
	
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
	
	global $admin, $ip;

	//make some nice-looking variables out of the information in the form
	$guild_id = (int) find('guild_id_submit');
	$guild_name = find('guild_name');
	$note = find('note');
	$owner_id = (int) find('owner_id');
	$guild_changes = find('guild_changes');
	
	// call guild information
	$guild = $db->grab_row('guild_select', array($guild_id));
	$guild_owner = (int) $guild->owner_id;
	
	if($guild_owner !== $owner_id) {
		$code = 'manual-' . time();
		$db->call('guild_transfer_insert', array($guild->guild_id, $guild_owner, $owner_id, $code, $ip), 'Could not initiate the owner change.');
		$change_id = $db->grab('change_id', 'guild_transfer_select_by_code', array($code), 'Could not get the owner change ID.');
		$db->call('guild_transfer_complete', array($change_id, $ip), 'Could not complete the owner change.');
	}
	
	//check to see if the admin is trying to delete the guild emblem
	if (!empty($_GET['delete_emblem'])) {
		$emblem = "default-emblem.jpg";
	}
	else {
		$emblem = $guild->emblem;
	}
	
	try {

		// check to make sure the description of changes exists
		if(is_empty($guild_changes)) {
			throw new Exception('The description of changes cannot be blank.');
		}
		
		// do it
		$db->call('guild_update', array($guild_id, $guild_name, $emblem, $note, $owner_id), 'Could not update the guild.');
	
		//admin log
		$admin_name = $admin->name;
		$admin_id = $admin->user_id;
		$ip = get_ip();
		$disp_changes = "Changes: " . $guild_changes;
		
		$db->call('admin_action_insert', array($admin_id, "$admin_name updated guild $guild_id from $ip. $disp_changes.", $admin_id, $ip), 'Could not insert the changes to the admin log.');

	}
	catch (Exception $e) {
		output_header('Update Guild', true, true);
		echo 'Error: ' . $e->getMessage();
		output_footer();
		die();
	}
	
	header("Location: guild_deep_info.php?guild_id=" . urlencode($guild->guild_id));
	die();

}

?>
