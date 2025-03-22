<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/admin_actions.php';
require_once QUERIES_DIR . '/changing_emails.php';
require_once QUERIES_DIR . '/servers.php';

// variables
$ip = get_ip();
$user_id = (int) default_get('id', 0);
$action = default_post('action', 'lookup');

try {
    // rate limiting
    rate_limit('update-account-'.$ip, 60, 10);
    rate_limit('update-account-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, null, true, 3);

    // build page
    if ($action === 'lookup') {
        output_header('Update PR2 Account', true, true);
    
        echo '<form name="input" action="update_account.php" method="post">';

        $user = user_select($pdo, $user_id);
        $pr2 = pr2_select($pdo, $user_id, true);
        $epic = epic_upgrades_select($pdo, $user_id, true);

        $user_id = (int) $user->user_id;
        $name = htmlspecialchars($user->name, ENT_QUOTES);
        $whitelisted = check_value($user->whitelisted, 1, "checked='checked'", '');
        $verified = check_value($user->verified, 1, "checked='checked'", '');
        $ca = check_value($user->ca, 1, "checked='checked'", '');
        $hof = check_value($user->hof, 1, "checked='checked'", '');
        $email = htmlspecialchars($user->email, ENT_QUOTES);
        $guild = htmlspecialchars($user->guild, ENT_QUOTES);

        echo "user_id: $user_id";
        echo "<br>---<br>";
        echo "Name: <input type='text' name='name' value='$name'>";
        echo "<label><input type='checkbox' name='whitelisted' $whitelisted /> Whitelisted</label> | ";
        echo "<label><input type='checkbox' name='verified' $verified /> Verified</label> | ";
        echo "<label><input type='checkbox' name='ca' $ca /> Community Ambassador</label> | ";
        echo "<label><input type='checkbox' name='hof' $hof /> Hall of Fame</label><br>";
        echo "Email: <input type='text' name='email' value='$email'>";
        echo '<label id="pass"><input type="checkbox" name="reset_pass" /> Generate new pass (via email)?</label><br>';
        echo "Guild: <input type='text' name='guild' value='$guild'><br>";
        if ($pr2 !== false) {
            echo "Hats: <input type='text' size='100' name='hats' value='$pr2->hat_array'><br>";
            echo "Heads: <input type='text' size='100' name='heads' value='$pr2->head_array'><br>";
            echo "Bodies: <input type='text' size='100' name='bodies' value='$pr2->body_array'><br>";
            echo "Feet: <input type='text' size='100' name='feet' value='$pr2->feet_array'><br>";
        }
        if ($epic !== false) {
            echo "Epic Hats: <input type='text' size='100' name='eHats' value='$epic->epic_hats'><br>";
            echo "Epic Heads: <input type='text' size='100' name='eHeads' value='$epic->epic_heads'><br>";
            echo "Epic Bodies: <input type='text' size='100' name='eBodies' value='$epic->epic_bodies'><br>";
            echo "Epic Feet: <input type='text' size='100' name='eFeet' value='$epic->epic_feet'><br>";
        }
        echo 'Description of Changes: <input type="text" size="100" name="account_changes"><br>';
        if ($user->server_id > 0) {
            echo "<label>Disconnect? <input type='checkbox' name='disconnect' /></label><br>";
        }
        echo '<input type="hidden" name="action" value="update">';
        echo "<input type='hidden' name='post_id' value='$user_id'>";

        echo '<br/>';
        echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
        echo '</form>';

        echo '<br>';
        echo '---';
        echo '<br>';
        echo '<pre>When making changes, use the "Description of Changes" box to summarize what you did.<br>'
            .'<br>'
            .'Find what each part ID is <a href="/part_ids.php" target="blank">here</a>.<br><br>'
            .'NOTE: Make sure the user is logged out of PR2 before trying to change parts.</pre>';
    } elseif ($action === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }

        // make some nice variables
        $admin_ip = get_ip();
        $user_id = (int) default_post('post_id');
        $user_name = default_post('name');
        $email = default_post('email');
        $reset_pass = default_post('reset_pass');
        $whitelisted = (int) !empty(default_post('whitelisted'));
        $verified = (int) !empty(default_post('verified'));
        $ca = (int) !empty(default_post('ca'));
        $hof = (int) !empty(default_post('hof'));
        $guild_id = (int) default_post('guild');
        $hats = default_post('hats');
        $heads = default_post('heads');
        $bodies = default_post('bodies');
        $feet = default_post('feet');
        $ehats = default_post('eHats');
        $eheads = default_post('eHeads');
        $ebodies = default_post('eBodies');
        $efeet = default_post('eFeet');
        $account_changes = default_post('account_changes');
        $disconnect = (int) !empty(default_post('disconnect'));

        // check for description of changes
        if (is_empty($account_changes)) {
            throw new Exception('You must enter a description of your changes.');
        }

        // make sure none of the part values are blank to avoid server crashes
        $hats = is_empty($hats, false) ? '1' : $hats;
        $heads = is_empty($heads, false) ? '1' : $heads;
        $bodies = is_empty($bodies, false) ? '1' : $bodies;
        $feet = is_empty($feet, false) ? '1' : $feet;

        // call user information
        $user = user_select($pdo, $user_id);
        $pr2 = pr2_select($pdo, $user_id, true);
        $epic = epic_upgrades_select($pdo, $user_id, true);

        // specify what to change
        $update_user = false;
        $update_pr2 = false;
        $update_epic = false;
        if ($user->name != $user_name
            || $user->email != $email
            || $user->guild != $guild_id
            || $user->whitelisted != $whitelisted
            || $user->verified != $verified
            || $user->ca != $ca
            || $user->hof != $hof
        ) {
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
                $safe_name = htmlspecialchars($user_name, ENT_QUOTES);
                throw new Exception("There is already a user with the name \"$safe_name\" (ID #$id_exists).");
            }
        }

        // disconnect player if online
        if ($disconnect && $user->server_id != 0) {
            $server = server_select($pdo, $user->server_id);
            $data = new stdClass();
            $data->user_id = (int) $user->user_id;
            $data->message = 'An admin updated your user information. Please log in again.';
            $data = json_encode($data);
            talk_to_server($server->address, $server->port, $server->salt, "disconnect_player`$data", false, false);
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
            
            // send a password reset email (same as forgot_password.php)
            if (!is_empty($reset_pass)) {
                require_once HTTP_FNS . '/rand_crypt/to_hash.php';

                // generate a new password
                $pass = random_str(12);
                user_update_temp_pass($pdo, $user_id, to_hash($pass));

                // email the new pass
                $to = $new_email;
                $safe_name = htmlspecialchars($user_name, ENT_QUOTES); // safety first
                $subject = 'A password and chocolates from PR2';
                $message = "Hi $safe_name,\n\n"
                    ."An admin generated a new password for you. Here it is: $pass\n\n"
                    ."If you didn't request this email, then just ignore it. "
                    ."Your old password will still work as long as you don't log in with this one.\n\n"
                    ."All the best,\n"
                    ."Fred";
                send_email($to, $subject, $message);
            }
        }

        // update the account
        $updated_user = 'no';
        $updated_pr2 = 'no';
        $updated_epic = 'no';
        if ($update_user === true) {
            admin_user_update($pdo, $user_id, $user_name, $email, $guild_id, $whitelisted, $verified, $ca, $hof);
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
        $msg = "$admin_name updated player $user_name from $admin_ip. {"
            ."update_user: $updated_user, "
            ."update_pr2: $updated_pr2, "
            ."update_epic: $updated_epic, "
            ."changes: $account_changes}";
        admin_action_insert($pdo, $admin_id, $msg, 'account-update', $admin_ip);

        header("Location: player_deep_info.php?name1=" . urlencode($user_name));
        die();
    } else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    output_error_page($e->getMessage(), @$admin);
} finally {
    output_footer();
}
