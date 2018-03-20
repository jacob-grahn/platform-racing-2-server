<?php

// output/misc functions
require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';

// guild select/update functions
require_once __DIR__ . '/../../queries/guilds/guild_select.php'; // pdo
require_once __DIR__ . '/../../queries/guilds/guild_update.php'; // pdo

// guild transfer functions
require_once __DIR__ . '/../../queries/guild_transfers/guild_transfer_insert.php'; // pdo
require_once __DIR__ . '/../../queries/guild_transfers/guild_transfer_select.php'; // pdo
require_once __DIR__ . '/../../queries/guild_transfers/guild_transfer_complete.php'; // pdo

$guild_id = find('guild_id');
$action = find('action', 'lookup');
$ip = get_ip();

try {
    // connect
    $db = new DB();
    $pdo = pdo_connect();


    // make sure you're an admin
    $admin = check_moderator($pdo, true, 3);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // lookup
    if ($action === 'lookup') {
        output_form($pdo, $guild_id);
    }


    // update
    if ($action === 'update') {
        update($db, $pdo, $admin, $ip);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Update Guild', true, true);
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    output_footer();
    die();
}


function output_form($pdo, $guild_id)
{

    output_header('Update Guild', true, true);

    echo '<form name="input" action="update_guild.php" method="get">';

    $guild = guild_select($pdo, $guild_id);
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

function update($db, $pdo, $admin, $ip)
{
    // check to make sure the description of changes exists
    $guild_changes = find('guild_changes');
    if (is_empty($guild_changes)) {
        throw new Exception('The description of changes cannot be blank.');
    }
    
    //make some nice-looking variables out of the information in the form
    $guild_id = (int) find('guild_id_submit');
    $guild_name = find('guild_name');
    $owner_id = (int) find('owner_id');
    $note = find('note');
    $delete_emblem = $_GET['delete_emblem'];
    
    // call guild information
    $guild = guild_select($pdo, $guild_id);
    
    // check if changes need to be made
    if ($guild_name == $guild->guild_name && $owner_id == $guild->owner_id && $note == $guild->note) {
        throw new Exception('No changes to be made.');
    }

    // log an owner transfer
    if ($guild->owner_id !== $owner_id) {
        $code = 'manual-' . time();
        $old_owner = $guild->owner_id;
        $new_owner = $owner_id;
        
        guild_transfer_insert($pdo, $guild->guild_id, $guild->owner_id, $owner_id, $code, $ip);
        $transfer = guild_transfer_select($pdo, $code);
        guild_transfer_complete($pdo, $transfer->transfer_id, $ip);
    }

    // delete a guild emblem
    if (!empty($delete_emblem)) {
        $emblem = "default-emblem.jpg";
    } else {
        $emblem = $guild->emblem;
    }

    // do it
    guild_update($pdo, $guild_id, $guild_name, $emblem, $note, $owner_id);

    // admin log
    $admin_name = $admin->name;
    $admin_id = $admin->user_id;
    $disp_changes = "Changes: " . $guild_changes;

    $db->call('admin_action_insert', array($admin_id, "$admin_name updated guild $guild_id from $admin_ip. $disp_changes.", $admin_id, $admin_ip), 'Could not insert the changes to the admin log.');

    // redirect
    header("Location: guild_deep_info.php?guild_id=" . urlencode($guild->guild_id));
    die();
}
