<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';

$action = find('action', 'none');
$ban_id = (int) default_val($_GET['ban_id'], 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-ban-edit-'.$ip, 3, 2);

    //connect
    $db = new DB();

    //make sure you're a moderator
    $mod = check_moderator($db);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Error');
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // sanity check: what ban id?
    if (is_empty($ban_id, false)) {
        throw new Exception('No ban ID specified.');
    }

    // get mod info
    $user_id = $mod->user_id;
    $name = $mod->name;
    $safe_name = addslashes($name);


    // ------------------------------------------------------------------
    // --- edit an existing ban, then redirect to that ban listing
    // ------------------------------------------------------------------
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $safe_ban_id = $db->escape($ban_id);
        $safe_account_ban = $db->escape(0+!!find('account_ban'));
        $safe_ip_ban = $db->escape(0+!!find('ip_ban'));
        $safe_expire_time = $db->escape(find('expire_time'));
        $safe_notes = $db->escape(find('notes'));

        //update the ban
        $query = "UPDATE bans
				SET account_ban = '$safe_account_ban',
					ip_ban = '$safe_ip_ban',
					expire_time = UNIX_TIMESTAMP('$safe_expire_time'),
					notes = '$safe_notes',
					modified_time = NOW()
				WHERE ban_id = '$safe_ban_id'
				LIMIT 1";
        $db->query($query, 'ban_update', 'Could not update ban. query: ' . $query);

        //action log
        $expire_time = find('expire_time');
        $notes = find('notes');
        $is_account_ban = check_value($safe_account_ban, 1);
        $is_ip_ban = check_value($safe_ip_ban, 1);

        if (is_empty($notes)) {
            $disp_notes = "no notes";
        } else {
            $disp_notes = "notes: $notes";
        }

        //record the change
        $db->call('mod_action_insert', array($mod->user_id, "$mod->name edited ban $ban_id {account_ban: $is_account_ban, ip_ban: $is_ip_ban, expire_time: $expire_time, $disp_notes}", 0, get_ip()));

        //redirect to the ban listing
        header("Location: https://pr2hub.com/bans/show_record.php?ban_id=$ban_id");
        die();
    } // --------------------------------------------------------------------------
    // --- display a form containing the current ban data
    // --------------------------------------------------------------------------
    else {
        $ban = $db->grab_row('ban_select', array($ban_id));
        output_header('Edit Ban');
        output_form($ban);
        output_footer();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Edit Ban', true);
    echo "Error: $error";
    output_footer();
}

function output_form($ban)
{

    //check if the boxes are checked courtesy of data_fns.php
    $ip_checked = check_value($ban->ip_ban, 1, 'checked="checked"', '');
    $acc_checked = check_value($ban->account_ban, 1, 'checked="checked"', '');

    echo "
	<form method='post'>
	<input type='hidden' value='edit' name='action'>
	<input type='hidden' value='$ban->ban_id' name='ban_id'>
	<p>Expire Date <input type='text' value='$ban->expire_datetime' name='expire_time'></p>
	<p>IP Ban <input type='checkbox' $ip_checked name='ip_ban'></p>
	<p>Account Ban <input type='checkbox' $acc_checked name='account_ban'></p>
	<p>Notes <textarea rows='4' cols='50' name='notes'>$ban->notes</textarea>
	<p><input type='submit' value='submit'></p>
	</form>";
}
