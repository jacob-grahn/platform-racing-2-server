<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';

$user_id = find('id');
$action = find('action', 'lookup');

try {
    //connect
    $db = new DB();
    $pdo = pdo_connect();


    //make sure you're an admin
    $admin = check_moderator($pdo, true, 3);
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . $e->getMessage();
    output_footer();
    die();
}

try {
    // header
    output_header('Update PR2 Account', true, true);

    // form
    if ($action === 'lookup') {
        output_form($db, $user_id);
        output_footer();
    }


    // update
    if ($action === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        update($db);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    output_footer();
    die();
}

// page
function output_form($db, $user_id)
{

    echo '<form name="input" action="update_account.php" method="post">';

    $user = $db->grab_row('user_select', array($user_id), 'Could not find a user with that ID.');
    $pr2 = $db->grab_row('pr2_select', array($user->user_id), '', true);
    $pr2_epic = $db->grab_row('epic_upgrades_select', array($user->user_id), '', true);
    echo "user_id: $user->user_id <br>---<br>";


    echo 'Name: <input type="text" size="" name="name" value="'.htmlspecialchars($user->name).'"><br>';
    echo 'Email: <input type="text" name="email" value="'.htmlspecialchars($user->email).'"><br>';
    echo 'Guild: <input type="text" name="guild" value="'.htmlspecialchars($user->guild).'"><br>';
    if ($pr2) {
        echo 'Hats: <input type="text" size="100" name="hats" value="'.$pr2->hat_array.'"><br>';
        echo 'Heads: <input type="text" size="100" name="heads" value="'.$pr2->head_array.'"><br>';
        echo 'Bodies: <input type="text" size="100" name="bodies" value="'.$pr2->body_array.'"><br>';
        echo 'Feet: <input type="text" size="100" name="feet" value="'.$pr2->feet_array.'"><br>';
    }
    if ($pr2_epic) {
        echo 'Epic Hats: <input type="text" size="100" name="eHats" value="'.$pr2_epic->epic_hats.'"><br>';
        echo 'Epic Heads: <input type="text" size="100" name="eHeads" value="'.$pr2_epic->epic_heads.'"><br>';
        echo 'Epic Bodies: <input type="text" size="100" name="eBodies" value="'.$pr2_epic->epic_bodies.'"><br>';
        echo 'Epic Feet: <input type="text" size="100" name="eFeet" value="'.$pr2_epic->epic_feet.'"><br>';
    }
    echo 'Description of Changes: <input type="text" size="100" name="account_changes"><br>';
    echo '<input type="hidden" name="action" value="update">';
    echo '<input type="hidden" name="id" value="'.$user->user_id.'">';

    echo '<br/>';
    echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
    echo '</form>';

    echo '<br>';
    echo '---';
    echo '<br>';
    echo '<pre>When making changes, use the "Description of Changes" box to summarize what you did.<br><br>Find what each part ID is <a href="part_ids.php" target="blank">here</a>.<br><br>NOTE: Make sure the user is logged out of PR2 before trying to change parts.</pre>';
}


// update function
function update($db)
{

    global $admin;

    // make some nice variables
    $guild_id = (int) find('guild');
    $user_id = (int) find('id');
    $email = find('email');
    $account_changes = find('account_changes');
    $hats = find('hats');
    $heads = find('heads');
    $bodies = find('bodies');
    $feet = find('feet');


    // call user information
    $user = $db->grab_row('user_select', array($user_id));
    $user_name = $user->name;

    // adjust guild member count
    if ($user->guild != $guild_id) {
        if ($user->guild != 0) {
            $db->call('guild_increment_member', array($user->guild, -1));
        }
        if ($guild_id != 0) {
            $db->call('guild_increment_member', array($guild_id, 1));
        }
    }

    // email change logging
    if ($user->email !== $email) {
        $code = 'manual-' . time();
        $db->call('changing_email_insert', array($user_id, $user->email, $email, $code, ''));
        $change_id = $db->grab('change_id', 'changing_email_select', array($code));
        $db->call('changing_email_complete', array($change_id, ''));
    }

    // make sure none of the part values are blank to avoid server crashes
    if (is_empty($hats, false)) {
        $hats = "1";
    }
    if (is_empty($heads, false)) {
        $heads = "1";
    }
    if (is_empty($bodies, false)) {
        $bodies = "1";
    }
    if (is_empty($feet, false)) {
        $feet = "1";
    }

    // check for description of changes
    if (is_empty($account_changes)) {
        throw new Exception('You must enter a description of your changes.');
    }

    // perform the action
    $db->call(
        'user_update',
        array(
        $user_id,
        find('name'),
        find('email'),
        $guild_id,
        $hats,
        $heads,
        $bodies,
        $feet,
        find('eHats'),
        find('eHeads'),
        find('eBodies'),
        find('eFeet')
        )
    );

    // log the action in the admin log
    $admin_name = $admin->name;
    $admin_id = $admin->user_id;
    $ip = get_ip();
    $disp_changes = "Changes: " . $account_changes;

    $db->call('admin_action_insert', array($admin_id, "$admin_name updated player $user_name from $ip. $disp_changes.", $admin_id, $ip));

    header("Location: player_deep_info.php?name1=" . urlencode(find('name')));
    die();
}
