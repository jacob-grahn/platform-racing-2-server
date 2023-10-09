<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/bans.php';

$ban_id = (int) default_get('ban_id', 0);
$ip = get_ip();

try {
    // rate limiting
    rate_limit("show-ban-record-$ip", 5, 3);

    // sanity check: valid ban id?
    if ($ban_id === 0) {
        header('Location: /bans');
        die();
    }

    // connect
    $pdo = pdo_connect();

    // sanity check: are they a moderator?
    $staff = is_staff($pdo, token_login($pdo, true, true, 'n'), false);
    $full_mod = $staff->mod && !$staff->trial;
    if (!$staff->mod) {
        rate_limit("show-ban-record-$ip", 60, 10, "Please wait at least one minute before trying to view another ban.");
    }

    // get the ban details
    $ban = ban_select($pdo, $ban_id);

    // output header (w/ mod nav if they're a mod)
    output_header('View Ban', $staff->mod, $staff->admin);

    // output the page
    $ban_id = (int) $ban->ban_id;
    $banned_ip = $ban->banned_ip;
    $mod_user_id = (int) $ban->mod_user_id;
    $banned_user_id = (int) $ban->banned_user_id;
    $time = date('M j, Y \a\t g:i A', $ban->time);
    $expire_time = date('M j, Y \a\t g:i A', $ban->expire_time);
    $duration = format_duration($ban->expire_time - $ban->time);
    $rel_expiry = ($ban->expire_time > time() ? 'in ' : '') . format_duration($ban->expire_time - time());
    $expiry_lang = $ban->expire_time > time() ? "will expire on" : "expired on";
    $reason = empty($ban->reason) ? '<i>No reason was given.</i>' : htmlspecialchars($ban->reason, ENT_QUOTES);
    $record = nl2br(htmlspecialchars($ban->record, ENT_QUOTES));
    $mod_name = htmlspecialchars($ban->mod_name, ENT_QUOTES);
    $banned_name = htmlspecialchars($ban->banned_name, ENT_QUOTES);
    $lifted = (int) $ban->lifted;
    $lifted_by = htmlspecialchars($ban->lifted_by, ENT_QUOTES);
    $lifted_reason = htmlspecialchars($ban->lifted_reason, ENT_QUOTES);
    $lifted_time = $ban->lifted_time > 0 ? date('M j, Y \a\t g:i A', $ban->lifted_time) : '';
    $ip_ban = (int) $ban->ip_ban;
    $account_ban = (int) $ban->account_ban;
    $scope = $ban->scope === 's' ? 'socially banned' : 'banned';
    $notes = nl2br(htmlspecialchars($ban->notes, ENT_QUOTES));

    $display_name = $account_ban === 1 ? $banned_name : '';
    $sep = is_empty($display_name) ? '' : ' ';
    $display_name = $ip_ban === 1 && $full_mod ? $display_name . $sep . "[$banned_ip]" : $display_name;

    // make the names clickable for moderators
    if ($staff->mod) {
        $lifted_by = "<a href='/mod/player_info.php?name=$lifted_by'>$lifted_by</a>";
        $mod_name = "<a href='/mod/player_info.php?user_id=$mod_user_id'>$mod_name</a>";
        if ($banned_user_id !== 0 && $account_ban === 1) {
            $display_name = "<a href='/mod/player_info.php?user_id=$banned_user_id'>$display_name</a>";
        } elseif (!$staff->trial) {
            $display_name = "<a href='/mod/ip_info.php?ip=$banned_ip'>$display_name</a>";
        } else {
            $display_name = "<i>an IP</i>";
        }
    } elseif (is_empty(trim($display_name)) && $ip_ban === 1) {
        $display_name = "<i>an IP</i>";
    }

    // lifted
    if ($lifted === 1) {
        echo '<b><p>-----------------------------------------------------------------------------------------------</p>'
               ."<p>--- This ban was lifted by $lifted_by on $lifted_time. ---</p>"
               ."<p>--- Reason: $lifted_reason ---</p>"
               .'<p>-----------------------------------------------------------------------------------------------</p>'
               .'<p>&nbsp;</p></b>';
    }

    echo "<p>$mod_name $scope $display_name for $duration on $time.</p>"
        ."<p>Reason: $reason</p>"
        ."<p>This ban $expiry_lang $expire_time ($rel_expiry).</p>"
        .'<p> --- </p>';

    if (!is_empty($record)) {
        echo "<p>$record</p>"
            ."<p> --- </p>";
    }

    if ($full_mod) {
        if (!is_empty($notes)) {
            echo "<p> --- notes</p>";
            echo "<p>$notes</p>";
            echo "<p> ---</p>";
        }
        echo "<p><a href='/mod/ban_edit.php?ban_id=$ban_id'>Edit Ban</a></p>";
    }

    echo '<p><a href="bans.php">Go Back</a></p>';
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Error Fetching Ban', $staff->mod, $staff->admin);
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
