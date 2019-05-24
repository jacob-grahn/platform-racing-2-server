<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/mod/ban_edit_fns.php';
require_once QUERIES_DIR . '/bans.php';
require_once QUERIES_DIR . '/mod_actions.php';

$action = default_post('action', 'edit');
$ban_id = (int) default_get('ban_id', 0);
$ip = get_ip();

$header = false;

// non-validated try/catch
try {
    // rate limiting
    rate_limit('mod-ban-edit-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);

    // sanity check: what ban id?
    if (is_empty($ban_id, false)) {
        throw new Exception('No ban ID specified.');
    }

    // get ban info
    $ban = ban_select($pdo, $ban_id);

    // if they're trying to update
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // update the ban
        $ban_id = (int) default_post('ban_id');
        $acc_ban = (int) (bool) default_post('account_ban');
        $ip_ban = (int) (bool) default_post('ip_ban');
        $exp_time = default_post('expire_time');
        $notes = default_post('notes');

        // lift info
        $lifted = (int) (bool) default_post('lifted');
        $lift_reason = default_post('lifted_reason');
        $lift_time = !is_empty($ban->lifted_time) ? $ban->lifted_time : 0;
        $lifted_by = $ban->lifted == $lifted && $ban->lifted_reason === $lift_reason ? $ban->lifted_by : $mod->name;

        // lift logging
        $lift_changed = $lifted_by !== $ban->lifted_by || $lift_reason !== $ban->lifted_reason || $ban->lifted == 0;
        if ($lifted === 1) {
            $lift_reason = is_empty($lift_reason) ? 'They bribed me with skittles!' : $lift_reason;
            $lift_time = $lift_changed ? time() : $ban->lifted_time;
        }

        // update ban
        ban_update($pdo, $ban_id, $acc_ban, $ip_ban, $exp_time, $lifted, $lifted_by, $lift_reason, $lift_time, $notes);

        // action log
        $acc = check_value($acc_ban, 1);
        $ipc = check_value($ip_ban, 1);
        $lifted = $lifted === 1 ? "yes, lifted_by: $lifted_by, lift_reason: $lift_reason, lift_time: $lift_time" : 'no';
        $lifted = "lifted: $lifted";
        $notes = is_empty($notes) ? 'no notes' : "notes: $notes";

        // record the change
        $mod_id = (int) $mod->user_id;
        $info = "{acc_ban: $acc, ip_ban: $ipc, expire_time: $exp_time, $lifted, $notes}";
        $msg = "$mod->name ($mod_id) edited ban $ban_id from $ip $info";
        mod_action_insert($pdo, $mod_id, $msg, 0, $ip);

        // redirect to the ban listing
        header("Location: /bans/show_record.php?ban_id=$ban_id");
        die();
    } elseif ($action === 'edit') {
        $header = true;
        output_header('Edit Ban', $mod->power >= 2, (int) $mod->power === 3);

        // check if the boxes are checked
        $ip_checked = check_value($ban->ip_ban, 1, 'checked="checked"', '');
        $acc_checked = check_value($ban->account_ban, 1, 'checked="checked"', '');

        // prepare data to be shown
        $notes = htmlspecialchars($ban->notes, ENT_QUOTES);
        $expire_date = date('Y-m-d H:i:s', $ban->expire_time);
        $lifted_reason = !is_empty($ban->lifted_reason) ? $ban->lifted_reason : '';

        // output js
        echo get_lifted_js();

        // show the form
        echo "<form method='post'>"
            .'<input type="hidden" value="update" name="action">'
            ."<input type='hidden' value='$ban->ban_id' name='ban_id'>"
            ."<p>Expire Date: <input type='text' value='$expire_date' name='expire_time'></p>"
            ."<p>IP Ban? <input type='checkbox' $ip_checked name='ip_ban'></p>"
            ."<p>Account Ban? <input type='checkbox' $acc_checked name='account_ban'></p>"
            .'<p>' . get_lifted_html($ban->lifted, $lifted_reason) . '</p>'
            ."<p>Notes:<br><br> <textarea rows='4' cols='50' name='notes'>$notes</textarea>"
            .'<p><input type="submit" value="submit"></p>'
            .'</form>';
    } else {
        throw new Exception('Unknown action specified.');
    }
} catch (Exception $e) {
    if ($header === false) {
        output_header('Error');
    }
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
}
