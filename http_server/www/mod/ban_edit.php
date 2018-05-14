<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/bans/ban_update.php';
require_once QUERIES_DIR . '/bans/ban_select.php';
require_once QUERIES_DIR . '/staff/actions/mod_action_insert.php';

$action = find_no_cookie('action', 'edit');
$ban_id = (int) find_no_cookie('ban_id', 0);
$ip = get_ip();

// non-validated try/catch
try {
    // rate limiting
    rate_limit('mod-ban-edit-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Error');
    echo "Error: $error";
    output_footer();
    die();
}

// mod validated try/catch
try {
    // sanity check: what ban id?
    if (is_empty($ban_id, false)) {
        throw new Exception('No ban ID specified.');
    }

    // if they're trying to update
    if ($action == 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // update the ban
        $ban_id = (int) find('ban_id');
        $account_ban = (int) (bool) find('account_ban');
        $ip_ban = (int) (bool) find('ip_ban');
        $expire_time = find('expire_time');
        $notes = find('notes');
        ban_update($pdo, $ban_id, $account_ban, $ip_ban, $expire_time, $notes);

        // action log
        $is_account_ban = check_value($account_ban, 1);
        $is_ip_ban = check_value($ip_ban, 1);
        if (is_empty($notes)) {
            $disp_notes = "no notes";
        } else {
            $disp_notes = "notes: $notes";
        }

        // record the change
        $mod_id = $mod->user_id;
        $mod_name = $mod->name;
        mod_action_insert(
            $pdo,
            $mod_id,
            "$mod_name edited ban $ban_id from $ip
                {
                    account_ban: $is_account_ban,
                    ip_ban: $is_ip_ban,
                    expire_time: $expire_time, $disp_notes
                }
            ",
            0,
            $ip
        );

        // redirect to the ban listing
        header("Location: https://pr2hub.com/bans/show_record.php?ban_id=$ban_id");
    } elseif ($action == 'edit') {
        $ban = ban_select($pdo, $ban_id);
        output_header('Edit Ban', true);

        // check if the boxes are checked courtesy of data_fns.php
        $ip_checked = check_value($ban->ip_ban, 1, 'checked="checked"', '');
        $acc_checked = check_value($ban->account_ban, 1, 'checked="checked"', '');

        // show the form
        echo "<form method='post'>
            <input type='hidden' value='update' name='action'>
            <input type='hidden' value='$ban->ban_id' name='ban_id'>
            <p>Expire Date <input type='text' value='$ban->expire_datetime' name='expire_time'></p>
            <p>IP Ban <input type='checkbox' $ip_checked name='ip_ban'></p>
            <p>Account Ban <input type='checkbox' $acc_checked name='account_ban'></p>
            <p>Notes <textarea rows='4' cols='50' name='notes'>$ban->notes</textarea>
            <p><input type='submit' value='submit'></p>
            </form>";

        output_footer();
    } else {
        throw new Exception('Unknown action specified.');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Edit Ban', true);
    echo "Error: $error";
    output_footer();
} finally {
    die();
}
