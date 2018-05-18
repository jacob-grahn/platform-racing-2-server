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
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Error');
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // header, also check if mod and output the mod links if so
    $is_mod = is_moderator($pdo, false);
    output_header('Ban Log', $is_mod);

    // navigation
    output_pagination($start, $count);
    echo '<p>---</p>';

    if ($is_mod === false) {
        rate_limit('list-bans-'.$ip, 60, 10);
        if ($count > 100) {
            $count = 100;
        }
    }

    // retrieve the ban list
    $bans = retrieve_ban_list($pdo, $start, $count);
    if (!$bans) {
        throw new Exception('Could not retrieve the ban list.');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Ban List", $is_mod);
    echo "Error: $error";
    output_footer();
    die();
}


//--- output the page ---

foreach ($bans as $row) {
    $ban_id = $row->ban_id;
    $banned_ip = $row->banned_ip;
    $mod_user_id = $row->mod_user_id;
    $banned_user_id = $row->banned_user_id;
    $time = $row->time;
    $expire_time = $row->expire_time;
    $reason = $row->reason;
    $mod_name = $row->mod_name;
    $banned_name = $row->banned_name;
    $ip_ban = $row->ip_ban;
    $account_ban = $row->account_ban;

    $formatted_time = date('M j, Y g:i A', $time);
    $duration = $expire_time - $time;

    $display_name = '';
    if ($account_ban == 1) {
        $display_name .= $banned_name;
    }
    if ($ip_ban == 1 && $is_mod) {
        if ($display_name != '') {
            $display_name .= ' ';
        }
        $display_name .= "[$banned_ip]";
    }

    $reason = htmlspecialchars($reason);
    $f_duration = format_duration($duration);

    echo "<p>$formatted_time
			<a href='show_record.php?ban_id=$ban_id'>
			".htmlspecialchars($mod_name)." banned ".htmlspecialchars($display_name)." for $f_duration.</a><br/>
			Reason: ".htmlspecialchars($reason)."
			</p>";
}

echo '<p>---</p>';
output_pagination($start, $count);

output_footer();
die();
