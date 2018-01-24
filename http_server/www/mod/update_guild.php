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
    output_header('Update Guild');
    echo 'error=' . ($e->getMessage());
    output_footer();
}


function output_form($db, $guild_id) {
    
    output_header('Update Guild');
    
    echo '<form name="input" action="update_guild.php" method="get">';
    
	$guild = $db->grab_row('guild_select', array($guild_id));
	echo "guild_id: $guild->guild_id <br>---<br>";

	
	echo 'Guild Name: <input type="text" size="" name="guild_name" value="'.htmlspecialchars($guild->name).'"><br>';
	echo 'Guild Owner: <input type="text" size"" name="owner_id" value="'.htmlspecialchars($guild->owner_id).'"><br>';
	echo 'Prose: <input type="text" size="100" name="note" value="'.htmlspecialchars($guild->note).'"><br>';
	echo 'Delete Emblem? <input type="checkbox" name="delete_emblem"><br>';
	echo 'Recount Members? <input type="checkbox" name="recount_members"><br>';
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

if (!empty($_GET['delete_emblem'])) {
	$emblem = "default-emblem.jpg";
}
else {
	$emblem = $guild->emblem;
}

if (!empty($_GET['recount_members'])) {
	$members = $db->call('guild_select_members', array($guild->guild_id));
	$member_count = mysqli_num_rows($members);
}
else {
	$member_count = $guild->member_count;
}

function update($db) {
    $guild_id = (int) find('guild_id');
    $owner_id = (int) find('owner_id');
    
    $guild = $db->grab_row('guild_select', array($guild_id));

    $db->call( 'guild_update', array(
	    $guild_id,
	    find('guild_name'),
	    $emblem,
	    find('note'),
	    $owner_id,
	    $member_count), 'A guild already exists with that name.' );
    
    header("Location: http://pr2hub.com/mod/guild_deep_info.php?guild_id=" . urlencode(find('guild_id')));
    /*echo('updated! <br>---<br>');
    output_form($db, $user_id);*/
}

?>
