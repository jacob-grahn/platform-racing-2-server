<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/staff/admin/users_count_from_ip_expanded.php';
require_once __DIR__ . '/../../queries/staff/admin/users_select_by_ip_expanded.php';

$ip = find_no_cookie('ip', '');
$start = (int) default_get('start', 0);
$count = (int) default_get('count', 25);
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

// this will echo the search box when called
function output_search($ip = '', $incl_br = true)
{
    echo "<form name='input' action='' method='get'>";
    echo "IP: <input type='text' name='ip' value='$ip'>&nbsp;";
    echo "<input type='submit' value='Search'></form>";
    if ($incl_br) {
        echo "<br><br>";
    }
}

// this will echo and control page counts when called
function output_pagination($start, $count, $ip, $is_end = false)
{
    $url_name = htmlspecialchars(urlencode($ip));
    $next_start_num = $start + $count;
    $last_start_num = $start - $count;
    if ($last_start_num < 0) {
        $last_start_num = 0;
    }
    echo('<p>');
    if ($start > 0) {
        echo("<a href='?name=$url_name&start=$last_start_num&count=$count'><- Last</a> |");
    } else {
        echo('<- Last |');
    }
    if ($is_end === true) {
        echo(" Next ->");
    } else {
        echo(" <a href='?name=$url_name&start=$next_start_num&count=$count'>Next -></a>");
    }
    echo('</p>');
}

// admin check try block
try {
    // rate limiting
    rate_limit('ip-deep-search-'.$ip, 60, 3, 'Wait a minute at most before searching again.');
    rate_limit('ip-deep-search-'.$ip, 30, 2);
    
    //connect
    $pdo = pdo_connect();

    //make sure you're an admin
    $admin = check_moderator($pdo, false, 3);
} catch (Exception $e) {
    $message = $e->getMessage();
    output_header('Error');
    echo "Error: $message";
    output_footer();
    die();
}

// admin validated try block
try {
    output_header('Deep IP Search', true, true);

    // sanity check: no IP in search box
    if (is_empty($ip)) {
        output_search('', false);
        output_footer();
        die();
    }
    
    // sanity check: ensure the database doesn't overload itself
    if ($count > 25) {
        $count = 25;
    }
    
    // if there's an IP set, let's get data from the db
    $user_count = users_count_from_ip_expanded($pdo, $ip);
    $users = users_select_by_ip_expanded($pdo, $ip, $start, $count);
    
    // calculate the number of results and the grammar to go with it
    $user_s = 'users';
    if ($user_count === 1) $user_s = 'user';
    
    // this determines if anything is shown on the page
    $is_end = false;
    if ($user_count > 0 && count($users) > 0) {
        $end = $start + count($users);
        echo "$user_count $user_s found associated with the IP \"$ip\".";
        echo "<br>Showing results $start - $end.<br><br>";
        if ($end == $user_count) {
            $is_end = true;
        }
    } else {
        echo "No results found for the search parameters.";
        output_footer();
        die();
    }
    
    if ($user_count > 0 && count($users) > 0) {
        echo '<p>---</p>';
        output_pagination($start, $count, $name, $is_end);
    }

    // output search
    $html_ip = htmlspecialchars($ip);
    output_search($html_ip);

    foreach ($users as $user) {
        $name = str_replace(' ', '&nbsp;', $user->name); // multiple spaces in name
        $url_name = htmlspecialchars(urlencode($user->name)); // url encode the name
        $group_color = $group_colors[(int) $user->power]; // group color
        $active_date = date('j/M/Y', (int) $user->time); // format the last active date
        if ($active_date == '30/Nov/-0001') $active_date = 'Never'; // show never if never logged in
    
        // display the name with the color and link to the player search page
        echo "<a href='player_deep_info.php?name1=$url_name' style='color: #$group_color; text-decoration: underline;'>$name</a> | Last Active: $active_date<br>";
    }
    
    // output page navigation
    if ($user_count > 0 && count($users) > 0) {
        output_pagination($start, $count, $ip, $is_end);
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    output_search($safe_ip);
    echo "<i>Error: $message</i>";
} finally {
    output_footer();
    die();
}
