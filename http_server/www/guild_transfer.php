<?php

require_once 'Mail.php';
require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/send_email.php';
require_once QUERIES_DIR . '/users/user_select.php';
require_once QUERIES_DIR . '/users/user_select_by_name.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/guild_transfers/guild_transfer_insert.php';

$ip = get_ip();

try {
    // rate limit
    rate_limit(
        'gui-guild-transfer-connect-'.$ip,
        5,
        2,
        'Please wait at least 5 seconds before trying to reload this page.'
    );

    // what is the user trying to do
    if (is_empty($_POST['action'])) {
        $action = 'form';
    } else {
        $action = $_POST['action'];
    }

    output_header('Guild Ownership Transfer');

    // connect
    $pdo = pdo_connect();

    // get user info
    $user_id = token_login($pdo); // user id with token from cookie
    $user = user_select($pdo, $user_id);

    // get user's guild id
    $guild_id = (int) $user->guild;

    // sanity check -- if guild id is 0, they aren't in a guild
    if ($guild_id === 0) {
        throw new Exception("You are not a member of a guild.");
    }

    // get guild info
    $guild = guild_select($pdo, $guild_id);

    // get owner id from guild info
    $owner_id = $guild->owner_id;

    // check if the logged in user is the owner of their guild
    if ($user_id == $owner_id && $action === 'form') {
        $safe_name = htmlspecialchars($user->name);
        $safe_guild_name = htmlspecialchars($guild->guild_name);

        echo "Welcome, <b>$safe_name</b>. You are currently the owner of $safe_guild_name.<br>"
            .'<br>'
            .'This page will allow you to transfer your guild ownership status to another user on PR2. '
            ."This means that this user will be able to edit your guild's information and buy/own private servers. "
            .'As a result, you will lose these privileges.<br>'
            .'<br>'
            .'If you would like to proceed, please fill out the form below.<br>'
            .'<br>';

        echo '<form name="input" action="guild_transfer.php" method="post">';

        echo 'Your Email Address: <input type="text" name="email"><br>';
        echo 'Your Password: <input type="password" name="pass"><br>';
        echo 'New Guild Owner\'s Username: <input type="text" name="new_owner"><br><br>';
        echo '<input type="hidden" name="action" value="submit">';

        echo 'NOTE: You may only transfer guild ownership once per week. '
            .'If you still want to proceed, click submit below.<br><br>';

        echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
        echo '</form>';

        output_footer();
    } elseif ($user_id != $owner_id && $action === 'form') {
        $safe_guild_name = htmlspecialchars($guild->guild_name);
        throw new Exception("You aren't the owner of $safe_guild_name.");
    }

    // start the transfer
    if ($action === 'submit') {
        require_trusted_ref('transfer your guild');

        $old_name = $user->name;

        // get the ip address of the requester
        $ip = get_ip();

        // rate limiting
        rate_limit(
            'gui-guild-transfer-'.$ip,
            10,
            1,
            'Please wait at least 10 seconds before attempting to transfer your guild again.'
        );

        // receive variables from post
        $email = $_POST['email'];
        $pass = $_POST['pass'];
        $new_name = $_POST['new_owner'];

        // check pass
        $old_user = pass_login($pdo, $old_name, $pass);
        $old_id = $old_user->user_id;
        $old_name = $old_user->name;
        $old_email = $old_user->email;

        // email sanity check
        if (!valid_email($email)) {
            $safe_email = htmlspecialchars($email);
            throw new Exception("'$safe_email' is not a valid email address.");
        }

        // check if the emails match
        if (strtolower($email) != strtolower($old_email)) {
            throw new Exception("The email address you entered is incorrect.");
        }

        // get new user's info
        $new_user = user_select_by_name($pdo, $new_name);
        $new_id = $new_user->user_id;

        // make some variables from the old user
        $safe_old_name = htmlspecialchars($old_user->name);
        $old_power = $old_user->power;

        // make some variables from the new user
        $safe_new_name = htmlspecialchars($new_user->name);
        $new_power = $new_user->power;

        // make some variables from the guild
        $safe_guild_name = htmlspecialchars($guild->guild_name);
        $current_owner = $guild->owner_id;

        // sanity check: make sure guests aren't getting any funny ideas
        if ($old_power < 1 || $new_power < 1) {
            throw new Exception("Guests can't even really own guilds...");
        }

        // sanity check: check again for guild ownership
        if ($old_id != $current_owner) {
            throw new Exception("You aren't the owner of $safe_guild_name.");
        }

        // don't let a guild change hands more than once in a week
        rate_limit(
            'guild-transfer-'.$guild->guild_id,
            604800,
            1,
            'Guild ownership can only be transferred once per week.'
        );

        // begin a guild transfer confirmation
        $code = random_str(24);
        guild_transfer_insert($pdo, $guild->guild_id, $old_id, $new_id, $code, $ip);

        // send a confirmation email
        $from = 'Fred the Giant Cactus <contact@jiggmin.com>';
        $to = $old_user->email;
        $subject = 'PR2 Guild Transfer Confirmation';
        $body = "Howdy $safe_old_name,\n\nWe received a request to change the
            owner of your guild $safe_guild_name to $safe_new_name. If you
            requested this change, please click the link below to complete the
            guild ownership transfer.\n\n
            https://pr2hub.com/confirm_guild_transfer.php?code=$code\n\n
            If you didn't request this change, you may need to change your password.\n\n
            All the best,\nFred";
        send_email($from, $to, $subject, $body);

        // tell the world
        echo "Almost done! We just sent a confirmation email to the email address
            on your account. You'll still own your guild until you confirm the transfer.";
        output_footer();
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    echo "Error: $message";
    output_footer();
    die();
}
