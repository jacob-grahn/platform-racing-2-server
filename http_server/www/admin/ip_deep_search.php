<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/ip_deep_search_fns.php';
require_once QUERIES_DIR . '/staff/admin/users_count_from_ip_expanded.php';
require_once QUERIES_DIR . '/staff/admin/users_select_by_ip_expanded.php';

$ip = default_get('ip', '');
$html_ip = htmlspecialchars($ip, ENT_QUOTES);
$start = (int) default_get('start', 0);
$count = (int) default_get('count', 25);
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];


// admin check try block
try {
    // rate limiting
    rate_limit('ip-deep-search-'.$ip, 60, 5, 'Wait a minute at most before searching again.');
    rate_limit('ip-deep-search-'.$ip, 30, 3);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, false, 3);

    // sanity check: ensure the database doesn't overload itself
    if ($count > 25) {
        $count = 25;
    }

    // if there's an IP set, let's get data from the db
    if ($ip) {
        $user_count = (int) users_count_from_ip_expanded($pdo, $ip);
        $users = users_select_by_ip_expanded($pdo, $ip, $start, $count);
    }

    // output page
    output_header('Deep IP Search', true, true);

    if ($ip) {
        // calculate the number of results and the grammar to go with it
        $user_s = 'accounts are';
        if ($user_count === 1) {
            $user_s = 'account is';
        }
        output_search($html_ip);

        // this determines if anything is shown on the page
        $is_end = false;
        if ($user_count > 0 && count($users) > 0) {
            $end = $start + count($users);
            echo "$user_count $user_s associated with the IP address \"$ip\".";
            echo "<br>Showing results $start - $end.<br>";
            if ($end == $user_count) {
                $is_end = true;
            }
        } else {
            echo "No results found for the search parameters.";
        }

        if ($user_count > 0 && count($users) > 0) {
            echo '<p>---</p>';
            $url_ip = urlencode($ip);
            output_pagination($start, $count, "&ip=$url_ip", $is_end);
        }

        foreach ($users as $user) {
            $name = str_replace(' ', '&nbsp;', $user->name); // multiple spaces in name
            $url_name = htmlspecialchars(urlencode($user->name)); // url encode the name
            $group_color = $group_colors[(int) $user->power]; // group color
            $active_date = date('j/M/Y', (int) $user->time); // format the last active date
            if ($active_date == '30/Nov/-0001') {
                $active_date = 'Never'; // show never if never logged in
            }

            // display the name with the color and link to the player search page
            echo "<a href='player_deep_info.php?name1=$url_name'
                style='color: #$group_color;
                text-decoration: underline;'>
                $name</a> | Last Active: $active_date<br>";
        }

        // output page navigation
        if ($user_count > 0 && count($users) > 0) {
            $url_ip = urlencode($ip);
            output_pagination($start, $count, "&ip=$url_ip", $is_end);
        }
    } else {
        output_search('', false);
    }
    output_footer();
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    output_header('Error');
    echo "Error: $error";
    output_footer();
}
