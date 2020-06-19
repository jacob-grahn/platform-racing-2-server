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
        $b_id = (int) default_post('ban_id');
        $type = default_post('type', 'both');
        $scope = default_post('scope');
        $exp = default_post('expire_time');
        $notes = default_post('notes');

        // determine types to be inserted into db
        $acc_ban = $ip_ban = 0;
        switch ($type) {
            case 'both':
                $acc_ban = $ip_ban = 1;
                break;
            case 'ip':
                $ip_ban = 1;
                break;
            case 'acc':
                $acc_ban = 1;
                break;
        }

        // make sure scope is s or g
        $scope = $scope === 's' || $scope === 'g' ? $scope : 'g';

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
        ban_update($pdo, $b_id, $acc_ban, $ip_ban, $scope, $exp, $lifted, $lifted_by, $lift_reason, $lift_time, $notes);

        // action log
        $lifted = $lifted === 1 ? "yes, lifted_by: $lifted_by, lift_reason: $lift_reason, lift_time: $lift_time" : 'no';
        $lifted = "lifted: $lifted";
        $notes = is_empty($notes) ? 'no notes' : "notes: $notes";

        // record the change
        $mod_id = (int) $mod->user_id;
        $info = "{type: $type, scope: $scope, expire_time: $exp, $lifted, $notes}";
        $msg = "$mod->name ($mod_id) edited ban $b_id from $ip $info";
        mod_action_insert($pdo, $mod_id, $msg, 'ban-edit', $ip);

        // redirect to the ban listing
        header("Location: /bans/show_record.php?ban_id=$b_id");
        die();
    } elseif ($action === 'edit') {
        $header = true;
        output_header('Edit Ban', $mod->power >= 2, (int) $mod->power === 3);

        // establish if ip/account ban
        $ip_ban = (bool) (int) $ban->ip_ban;
        $account_ban = (bool) (int) $ban->account_ban;

        // prepare data to be shown
        $notes = htmlspecialchars($ban->notes, ENT_QUOTES);
        $expire_date = date('Y-m-d H:i:s', $ban->expire_time);
        $lifted_reason = !is_empty($ban->lifted_reason) ? $ban->lifted_reason : '';

        // output js
        echo get_lifted_js();

        // show the form
        $date_ph = 'placeholder="YYYY-MM-DD HH:MM:SS"';
        echo "<form method='post'>"
            .'<input type="hidden" value="update" name="action">'
            ."<input type='hidden' value='$ban->ban_id' name='ban_id'>"
            .'<p><label for="expire_time">Expire Date: </label>' . get_exp_date_html($expire_date) . "</p>"
            .'<p><label for="type">Type: </label>' . get_type_html($ip_ban, $account_ban) . '</p>'
            .'<p><label for="scope">Scope: </label>' . get_scope_html($ban->scope) . '</p>'
            .'<p><label for="lifted">Lifted: </label>' . get_lifted_html($ban->lifted, $lifted_reason) . '</p>'
            ."<p><label>Notes:<br><textarea rows='4' cols='50' name='notes'>$notes</textarea></label></p>"
            .'<p><input type="submit" value="Submit"></p>'
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
