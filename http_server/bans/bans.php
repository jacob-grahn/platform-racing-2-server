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
    $staff = is_staff($pdo, token_login($pdo, true, true, 'n'), false);
    $full_mod = $staff->mod && !$staff->trial;
    $header = true;
    output_header('Ban Log', $staff->mod, $staff->admin);

    if (!$staff->mod) {
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
    foreach ($bans as $ban) {
        $ban_id = (int) $ban->ban_id;
        $banned_ip = $ban->banned_ip;
        $mod_user_id = (int) $ban->mod_user_id;
        $banned_user_id = (int) $ban->banned_user_id;
        $time = (int) $ban->time;
        $expire_time = (int) $ban->expire_time;
        $reason = empty($ban->reason) ? "<i>No reason was given.</i>" : htmlspecialchars($ban->reason, ENT_QUOTES);
        $mod_name = htmlspecialchars($ban->mod_name, ENT_QUOTES);
        $banned_name = $ban->banned_name;
        $ip_ban = (int) $ban->ip_ban;
        $account_ban = (int) $ban->account_ban;
        $scope = $ban->scope === 's' ? 'socially banned' : 'banned';

        // format expire time
        $formatted_time = date('M j, Y g:i A', $time);
        $duration = $expire_time - $time;

        // build display name
        $disp_name = $account_ban === 1 ? $banned_name : '';
        $sep = is_empty($disp_name) ? '' : ' ';
        $disp_name = $ip_ban === 1 && $full_mod ? $disp_name . $sep . "[$banned_ip]" : $disp_name;

        // prepare data for output
        $disp_name = htmlspecialchars($disp_name, ENT_QUOTES);
        $f_dur = format_duration($duration);

        // display "an IP" if ip ban and you're not a full mod
        if ($ip_ban === 1 && $account_ban === 0 && !$full_mod) {
            $disp_name .= "<i>an IP</i>";
        }

        // show ban text
        echo "<p>"
            ."$formatted_time <a href='show_record.php?ban_id=$ban_id'>$mod_name $scope $disp_name for $f_dur.</a>"
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
