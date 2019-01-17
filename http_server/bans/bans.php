<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/bans.php';

$start = (int) default_get('start', 0);
$count = (int) default_get('count', 100);
$ip = get_ip();

$header = false;

try {
    // rate limiting
    rate_limit('list-bans-'.$ip, 5, 3);

    // connect
    $pdo = pdo_connect();

    // header, also check if mod and output the mod links if so
    $staff = is_staff($pdo, token_login($pdo, true, true), false);
    $header = true;
    output_header('Ban Log', $staff->mod, $staff->admin);

    if ($staff->mod === false) {
        rate_limit('list-bans-'.$ip, 60, 10);
        $count = $count > 100 ? 100 : $count;
    }

    // retrieve the ban list
    $bans = retrieve_ban_list($pdo, $start, $count);
    if (!$bans) {
        throw new Exception('No bans found for the specified start.');
    }

    // navigation
    output_pagination($start, $count);
    echo '<p>---</p>';

    // output the page
    foreach ($bans as $row) {
        $ban_id = (int) $row->ban_id;
        $banned_ip = $row->banned_ip;
        $mod_user_id = (int) $row->mod_user_id;
        $banned_user_id = (int) $row->banned_user_id;
        $time = (int) $row->time;
        $expire_time = (int) $row->expire_time;
        $reason = htmlspecialchars($row->reason, ENT_QUOTES);
        $mod_name = htmlspecialchars($row->mod_name, ENT_QUOTES);
        $banned_name = $row->banned_name;
        $ip_ban = (int) $row->ip_ban;
        $account_ban = (int) $row->account_ban;

        $formatted_time = date('M j, Y g:i A', $time);
        $duration = $expire_time - $time;

        $display_name = $account_ban === 1 ? $banned_name : '';
        $sep = is_empty($display_name) ? '' : ' ';
        $display_name = $ip_ban === 1 && $staff->mod ? $display_name . $sep . "[$banned_ip]" : $display_name;

        $display_name = htmlspecialchars($display_name, ENT_QUOTES);
        $f_dur = format_duration($duration);

        // display "an IP" if ip ban and you're not a mod
        if ($ip_ban === 1 && $account_ban === 0 && !$staff->mod) {
            $display_name .= "<i>an IP</i>";
        }

        echo "<p>"
            ."$formatted_time <a href='show_record.php?ban_id=$ban_id'>$mod_name banned $display_name for $f_dur.</a>"
            ."<br/>Reason: $reason"
            ."</p>";
    }

    echo '<p>---</p>';
    output_pagination($start, $count);
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    output_header('Error');
    echo "Error: $error";
} finally {
    output_footer();
}
