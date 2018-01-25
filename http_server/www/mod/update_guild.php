<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$guild_id = find('guild_id');
$action = find('action', 'lookup');

try {
    
    //connect
    $db = new DB();


    //make sure you're an admin
    $mod = check_moderator($db, true, 3);
    
    
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
    output_header('Update Guild', true);
    echo 'error=' . ($e->getMessage());
    output_footer();
}


function output_form($db, $guild_id) {
    
    output_header('Update Guild', true);
    
    echo '<form name="input" action="update_guild.php" method="get">';
    
	$guild = $db->grab_row('guild_select', array($guild_id));
	echo "guild_id: $guild->guild_id <br>---<br>";

	
	echo 'Guild Name: <input type="text" size="" name="guild_name" value="'.htmlspecialchars($guild->guild_name).'"><br>';
	echo 'Guild Owner: <input type="text" size"" name="owner_id" value="'.htmlspecialchars($guild->owner_id).'"><br>';
	echo 'Prose: <textarea rows="4" name="note">'.htmlspecialchars($guild->note).'</textarea><br>';
	echo 'Delete Emblem? <input type="checkbox" name="delete_emblem"><br>';
	echo '<input type="hidden" name="action" value="update">';
	echo '<input type="hidden" name="guild_id" value="'.$guild->guild_id.'">';
    
    echo '<br/>';
    echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
    echo '</form>';
    
    echo '<br>';
    echo '---';
    echo '<br>';
    echo '<pre>To replace the guild owner, get the user ID of the user you want to make the owner.<br>Then, replace the previous one in the guild owner field.<br><br>NOTE: You MUST make sure that the person you\'re making the owner is already in the guild.</pre>';
    
    output_footer();
}

function update($db) {

    //make some nice-looking variables
    $guild_id = (int) find('guild_id');
    $guild_name = find('guild_name');
    $note = find('note');
    $owner_id = (int) find('owner_id');
    
    //check to see if the admin is trying to delete the guild emblem
    if (!empty($_GET['delete_emblem'])) {
	$emblem = "default-emblem.jpg";
    }
    else {
	$emblem = $guild->emblem;
    }
    
    $guild = $db->grab_row('guild_select', array($guild_id));

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
    
    header("Location: http://pr2hub.com/mod/guild_deep_info.php?guild_id=" . urlencode(find('guild_id')));

}

?>
