<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/player_search_fns.php';
require_once QUERIES_DIR . '/bans/bans_select_by_ip.php';
require_once QUERIES_DIR . '/users/users_select_by_ip.php';

$ip = default_get('ip', '');
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];
$header = false;

// mod check try/catch
try {
    // rate limiting
    rate_limit('ip-search-'.$ip, 60, 10, 'Wait a minute at most before searching again.');
    rate_limit('ip-search-'.$ip, 30, 5);

    // connect
    $pdo = pdo_connect();

    //make sure you're a mod
    $mod = check_moderator($pdo, false);

    // sanity check: is a value entered for IP?
    if (empty($ip)) {
        throw new Exception("Invalid IP address entered.");
    }

    // get IP info
    $ip_info = json_decode(file_get_contents('https://tools.keycdn.com/geo.json?host=' . $ip));

    // check if it's valid
    $skip_fanciness = false;
    if ($ip_info->status != 'success') {
        $skip_fanciness = true;
    }

    // if the data retrieval was successful, define our fancy variables
    if ($skip_fanciness === false) {
        $ip_info = $ip_info->data->geo;

        // make some variables
        $html_host = htmlspecialchars($ip_info->host, ENT_QUOTES);
        $html_dns = htmlspecialchars($ip_info->dns, ENT_QUOTES);
        $html_isp = htmlspecialchars($ip_info->isp, ENT_QUOTES);
        $url_isp = htmlspecialchars(urlencode($ip_info->isp), ENT_QUOTES);
        $html_city = htmlspecialchars($ip_info->city, ENT_QUOTES);
        $html_region = htmlspecialchars($ip_info->region_name, ENT_QUOTES);
        $html_country = htmlspecialchars($ip_info->country_name, ENT_QUOTES);
        $html_country_code = htmlspecialchars($ip_info->country_code, ENT_QUOTES);

        // make a location string out of the location data
        $html_location = '';
        if (!empty($html_city)) {
            $html_location .= $html_city . ', '; // city
        }
        if (!empty($html_region)) {
            $html_location .= $html_region . ', '; // region/state/province
        }
        if (!empty($html_country)) {
            $html_location .= $html_country . ' (' . $html_country_code . ')'; // country/code
        }
    }

    // we can dance if we want to, we can leave your friends behind
    $html_ip = htmlspecialchars($ip, ENT_QUOTES);

    // header
    output_header('IP Info', true);
    $header = true;

    // start
    echo "<p>IP: $html_ip</p>";

    // if the data retrieval was successful, display our fancy variables
    if ($skip_fanciness === false) {
        if (!empty($html_host)) {
            echo "<p>Host: $html_host</p>";
        }
        if (!empty($html_dns)) {
            echo "<p>DNS: $html_dns</p>";
        }
        if (!empty($html_isp)) {
            echo "<p>ISP: <a href='https://www.google.com/search?q=$url_isp' target='_blank'>$html_isp</a></p>";
        }
        if (!empty($html_location)) {
            echo "<p>Location: $html_location</p>";
        }
    }

    // check if they are currently banned
    $banned = 'No';
    $row = query_if_banned($pdo, 0, $ip);

    // give some more info on the current ban in effect if there is one
    if ($row !== false) {
        $ban_id = $row->ban_id;
        $reason = htmlspecialchars($row->reason, ENT_QUOTES);
        $ban_end_date = date("F j, Y, g:i a", $row->expire_time);
        $banned = "<a href='/bans/show_record.php?ban_id=$ban_id'>Yes.</a>
            This IP is banned until $ban_end_date. Reason: $reason";
    }

    // look for all historical bans given to this ip address
    $ip_bans = bans_select_by_ip($pdo, $ip);
    $ip_ban_count = (int) count($ip_bans);
    $ip_ban_list = create_ban_list($ip_bans);
    $ip_lang = 'time';
    if ($ip_ban_count !== 1) {
        $ip_lang = 'times';
    }

    // echo ban status
    echo "<p>Currently banned: $banned</p>"
        ."<p>This IP has been banned $ip_ban_count $ip_lang.</p> $ip_ban_list";

    // get users associated with this IP
    $users = users_select_by_ip($pdo, $ip);
    $user_count = count($users);
    $res = 'account is';
    if ($user_count !== 1) {
        $res = 'accounts are';
    }

    // echo user count
    echo "$user_count $res associated with the IP address \"$html_ip\".<br><br>";

    foreach ($users as $user) {
        $user_id = (int) $user->user_id;
        $name = htmlspecialchars($user->name, ENT_QUOTES);
        $power_color = $group_colors[(int) $user->power];
        $active = date('j/M/Y', (int) $user->time);

        // echo results
        echo "<a href='https://pr2hub.com/mod/player_info.php?user_id=$user_id' style='color: #$power_color'>$name</a>
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
