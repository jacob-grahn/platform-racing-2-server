<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/player_search_fns.php';
require_once QUERIES_DIR . '/bans.php';

$ip = default_get('ip', '');
$header = false;

try {
    // rate limiting
    rate_limit('ip-search-'.$ip, 60, 10, 'Wait a minute at most before searching again.');
    rate_limit('ip-search-'.$ip, 30, 5);

    // connect
    $pdo = pdo_connect();

    // make sure you're a mod
    $staff = is_staff($pdo, token_login($pdo), false, true);

    // header
    $header = true;
    output_header('IP Info', $staff->mod, $staff->admin);

    // sanity check: is a value entered for IP?
    if (empty($ip)) {
        throw new Exception("Invalid IP address entered.");
    }

    // get IP info
    try {
        $ip_info = @file_get_contents('https://tools.keycdn.com/geo.json?host=' . $ip);
        if ((bool) $ip_info === false) {
            throw new Exception('Couldn\'t get IP data...');
        }
        $ip_info = json_decode($ip_info);
        $skip_fanciness = false;
    } catch (Exception $e) {
        $skip_fanciness = true;
    }

    // check if it's valid
    //$skip_fanciness = $ip_info->status !== 'success';

    // if the data retrieval was successful, define our fancy variables
    if ($skip_fanciness === false) {
        $ip_info = $ip_info->data->geo;

        // make some variables
        $html_host = htmlspecialchars($ip_info->host, ENT_QUOTES);
        $html_dns = htmlspecialchars($ip_info->rdns, ENT_QUOTES);
        $html_isp = htmlspecialchars($ip_info->isp, ENT_QUOTES);
        $url_isp = 'https://www.google.com/search?q=' . htmlspecialchars(urlencode($ip_info->isp), ENT_QUOTES);
        $html_city = htmlspecialchars($ip_info->city, ENT_QUOTES);
        $html_region = htmlspecialchars($ip_info->region_name, ENT_QUOTES);
        $html_country = htmlspecialchars($ip_info->country_name, ENT_QUOTES);
        $html_country_code = htmlspecialchars($ip_info->country_code, ENT_QUOTES);

        // make a location string out of the location data
        $loc = '';
        $loc = !is_empty($html_city) ? $loc . $html_city . ', ' : $loc;
        $loc = !is_empty($html_region) ? $loc . $html_region . ', ' : $loc;
        $loc = !is_empty($html_country) ? $loc . $html_country . ' (' . $html_country_code . ')' : $loc;
    }

    // we can dance if we want to, we can leave your friends behind
    $html_ip = htmlspecialchars($ip, ENT_QUOTES);

    // start
    echo "<p>IP: $html_ip</p>";

    // if the data retrieval was successful, display our fancy variables
    if ($skip_fanciness === false) {
        echo is_empty($html_host) ?: "<p>Host: $html_host</p>";
        echo is_empty($html_dns) ?: "<p>DNS: $html_dns</p>";
        echo is_empty($html_isp) ?: "<p>ISP: <a href='$url_isp' target='_blank'>$html_isp</a></p>";
        echo is_empty($loc) ?: "<p>Location: $loc</p>";
    }

    // check if they are currently banned
    $banned = 'No';
    $row = query_if_banned($pdo, 0, $ip);

    // give some more info on the current ban in effect if there is one
    if ($row !== false) {
        $ban_id = $row->ban_id;
        $reason = htmlspecialchars($row->reason, ENT_QUOTES);
        $ban_end_date = date("F j, Y, g:i a", $row->expire_time);
        $banned = "<a href='/bans/show_record.php?ban_id=$ban_id'>Yes.</a>"
            ."This IP is banned until $ban_end_date. Reason: $reason";
    }

    // look for all historical bans given to this ip address
    $ip_bans = bans_select_by_ip($pdo, $ip);
    $ip_ban_count = (int) count($ip_bans);
    $ip_ban_list = create_ban_list($ip_bans);
    $ip_lang = $ip_ban_count !== 1 ? 'times' : 'time';

    // echo ban status
    echo "<p>Currently banned: $banned</p>"
        ."<p>This IP has been banned $ip_ban_count $ip_lang.</p>"
        .$ip_ban_list;

    // get users associated with this IP
    $users = users_select_by_ip($pdo, $ip);
    $user_count = count($users);
    $res = $user_count !== 1 ? 'accounts are' : 'account is';

    // echo user count
    echo "$user_count $res associated with the IP address \"$html_ip\".<br><br>";

    foreach ($users as $user) {
        $user_id = (int) $user->user_id;
        $name = htmlspecialchars($user->name, ENT_QUOTES);
        $power_color = $group_colors[(int) $user->power];
        $active = date('j/M/Y', (int) $user->time);

        // echo results
        echo "<a href='/mod/player_info.php?user_id=$user_id' style='color: #$power_color'>$name</a>
            | Last Active: $active<br>";
    }
} catch (Exception $e) {
    if ($header === false) {
        output_header('Error');
    }
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
