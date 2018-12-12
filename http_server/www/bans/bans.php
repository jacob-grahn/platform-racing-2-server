<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/bans/retrieve_ban_list.php';

$start = (int) default_get('start', 0);
$count = (int) default_get('count', 100);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('list-bans-'.$ip, 5, 3);

    // connect
    $pdo = pdo_connect();

    // header, also check if mod and output the mod links if so
    $staff = is_staff($pdo, $user_id, false);
    output_header('Ban Log', $staff->mod, $staff->admin);

    // navigation
    output_pagination($start, $count);
    echo '<p>---</p>';

    if ($staff->mod === false) {
        rate_limit('list-bans-'.$ip, 60, 10);
        $count = $count > 100 ? 100 : $count;
    }

    // retrieve the ban list
    $bans = retrieve_ban_list($pdo, $start, $count);
    if (!$bans) {
        throw new Exception('Could not retrieve the ban list.');
    }

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

        $display_name = '';
        if ($account_ban === 1) {
            $display_name .= $banned_name;
        }
        if ($ip_ban === 1 && $staff->mod) {
            if ($display_name != '') {
                $display_name .= ' ';
            }
            $display_name .= "[$banned_ip]";
        }

        $display_name = htmlspecialchars($display_name, ENT_QUOTES);
        $f_dur = format_duration($duration);

        echo "<p>"
            ."$formatted_time <a href='show_record.php?ban_id=$ban_id'>$mod_name banned $display_name for $f_dur.</a>"
            ."<br/>Reason: $reason"
            ."</p>";
    }

    echo '<p>---</p>';
    output_pagination($start, $count);
    output_footer();
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    output_header('Error');
    echo "Error: $error";
    output_footer();
}
