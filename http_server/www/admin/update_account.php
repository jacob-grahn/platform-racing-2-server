<?php

// output, misc functions
require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';

// user data update functions
require_once QUERIES_DIR . '/staff/admin/admin_account_update.php';

// guild, email functions
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guilds/guild_increment_member.php';
require_once QUERIES_DIR . '/changing_emails/changing_email_insert.php';
require_once QUERIES_DIR . '/changing_emails/changing_email_select.php';
require_once QUERIES_DIR . '/changing_emails/changing_email_complete.php';

// admin log
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php';

// variables
$user_id = find('id');
$action = find('action', 'lookup');

try {
    // rate limiting
    rate_limit('update-account-'.$ip, 60, 10);
    rate_limit('update-account-'.$ip, 5, 2);

    //connect
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
        echo '<form name="input" action="update_account.php" method="post">';

        $user = user_select($pdo, $user_id);
        $pr2 = pr2_select($pdo, $user_id, true);
        $epic = epic_upgrades_select($pdo, $user_id, true);
        echo "user_id: $user->user_id <br>---<br>";


        echo 'Name: <input type="text" size="" name="name" value="'.htmlspecialchars($user->name).'"><br>';
        echo 'Email: <input type="text" name="email" value="'.htmlspecialchars($user->email).'"><br>';
        echo 'Guild: <input type="text" name="guild" value="'.htmlspecialchars($user->guild).'"><br>';
        if ($pr2 !== false) {
            echo 'Hats: <input type="text" size="100" name="hats" value="'.$pr2->hat_array.'"><br>';
            echo 'Heads: <input type="text" size="100" name="heads" value="'.$pr2->head_array.'"><br>';
            echo 'Bodies: <input type="text" size="100" name="bodies" value="'.$pr2->body_array.'"><br>';
            echo 'Feet: <input type="text" size="100" name="feet" value="'.$pr2->feet_array.'"><br>';
        }
        if ($epic !== false) {
            echo 'Epic Hats: <input type="text" size="100" name="eHats" value="'.$epic->epic_hats.'"><br>';
            echo 'Epic Heads: <input type="text" size="100" name="eHeads" value="'.$epic->epic_heads.'"><br>';
            echo 'Epic Bodies: <input type="text" size="100" name="eBodies" value="'.$epic->epic_bodies.'"><br>';
            echo 'Epic Feet: <input type="text" size="100" name="eFeet" value="'.$epic->epic_feet.'"><br>';
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
        echo '<pre>When making changes, use the "Description of Changes" box to summarize what you did.<br>'
            .'<br>'
            .'Find what each part ID is <a href="part_ids.php" target="blank">here</a>.<br><br>'
            .'NOTE: Make sure the user is logged out of PR2 before trying to change parts.</pre>';
        output_footer();
    }


    // update
    if ($action === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        $user_id = $admin;
        // make some nice variables
        $admin_ip = get_ip();
        $user_id = (int) find('id');
        $user_name = find('name');
        $email = find('email');
        $guild_id = (int) find('guild');
        $hats = find('hats');
        $heads = find('heads');
        $bodies = find('bodies');
        $feet = find('feet');
        $ehats = find('eHats');
        $eheads = find('eHeads');
        $ebodies = find('eBodies');
        $efeet = find('eFeet');
        $account_changes = find('account_changes');

        // check for description of changes
        if (is_empty($account_changes)) {
            throw new Exception('You must enter a description of your changes.');
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

        // call user information
        $user = user_select($pdo, $user_id);
        $pr2 = pr2_select($pdo, $user_id, true);
        $epic = epic_upgrades_select($pdo, $user_id, true);

        // specify what to change
        $update_user = false;
        $update_pr2 = false;
        $update_epic = false;
        if ($user->name != $user_name || $user->email != $email || $user->guild != $guild_id) {
            $update_user = true;
        }
        if ($pr2->hat_array != $hats
            || $pr2->head_array != $heads
            || $pr2->body_array != $bodies
            || $pr2->feet_array != $feet
        ) {
            $update_pr2 = true;
        }
        if ($epic->epic_hats != $ehats
            || $epic->epic_heads != $eheads
            || $epic->epic_bodies != $ebodies
            || $epic->epic_feet != $efeet
        ) {
            $update_epic = true;
        }

        // if there's nothing to change, no need to query the database any further
        if ($update_user === false && $update_pr2 === false && $update_epic === false) {
            throw new Exception('No changes to be made.');
        }

        // make sure the name doesn't exist
        if (strtolower($user->name) != strtolower($user_name)) {
            $id_exists = name_to_id($pdo, $user_name, true);
            if ($id_exists != false) {
                $safe_name = htmlspecialchars($user_name);
                throw new Exception("There is already a user with the name \"$safe_name\" (ID #$id_exists).");
            }
        }

        // adjust guild member count
        if ($user->guild != $guild_id) {
            if ($guild_id != 0) {
                guild_select($pdo, $guild_id); // make sure the new guild exists
                guild_increment_member($pdo, $guild_id, 1);
            }
            if ($user->guild != 0) {
                guild_increment_member($pdo, $user->guild, -1);
            }
        }

        // email change logging
        if ($user->email !== $email) {
            $old_email = $user->email;
            $new_email = $email;
            $code = 'manual-' . time();

            // log it
            changing_email_insert($pdo, $user_id, $old_email, $new_email, $code, $admin_ip);
            $change = changing_email_select($pdo, $code);
            changing_email_complete($pdo, $change->change_id, $admin_ip);
        }

        // update the account
        $updated_user = 'no';
        $updated_pr2 = 'no';
        $updated_epic = 'no';
        if ($update_user === true) {
            admin_user_update($pdo, $user_id, $user_name, $email, $guild_id);
            $updated_user = 'yes';
        }
        if ($update_pr2 === true) {
            admin_pr2_update($pdo, $user_id, $hats, $heads, $bodies, $feet);
            $updated_pr2 = 'yes';
        }
        if ($update_epic === true) {
            admin_epic_upgrades_update($pdo, $user_id, $ehats, $eheads, $ebodies, $efeet);
            $updated_epic = 'yes';
        }

        // log the action in the admin log
        $admin_name = $admin->name;
        $admin_id = $admin->user_id;
        admin_action_insert(
            $pdo,
            $admin_id,
            "$admin_name updated player $user_name from $admin_ip. {
                update_user: $updated_user,
                update_pr2: $updated_pr2,
                update_epic: $updated_epic,
                changes: $account_changes}",
            0,
            $admin_ip
        );

        header("Location: player_deep_info.php?name1=" . urlencode($user_name));
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    output_footer();
} finally {
    die();
}
