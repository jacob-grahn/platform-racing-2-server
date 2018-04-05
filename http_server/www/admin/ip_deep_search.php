<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/staff/admin/users_select_by_ip_expanded.php';

$ip = find_no_cookie('ip', '');
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

// this will echo the search box when called
function output_search($ip = '', $incl_br = true)
{
    echo "<form name='input' action='' method='get'>";
    echo "Email: <input type='text' name='ip' value='$ip'>&nbsp;";
    echo "<input type='submit' value='Search'></form>";
    if ($incl_br) {
        echo "<br><br>";
    }
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
    
    // safety first
    $html_ip = htmlspecialchars($ip);

    // output search
    output_search($html_ip);
    
    // if there's an IP set, let's get data from the db
    $users = users_select_by_ip_expanded($pdo, $ip);

    foreach ($users as $user) {
        $name = str_replace(' ', '&nbsp;', $user->name); // multiple spaces in name
        $url_name = htmlspecialchars(urlencode($user->name)); // url encode the name
        $group_color = $group_colors[(int) $user->power]; // group color
        $active_date = date('j/M/Y', (int) $user->time); // format the last active date
        if ($active_date == '30/Nov/-0001') $active_date = 'Never'; // show never if never logged in
    
        // display the name with the color and link to the player search page
        echo "<a href='player_deep_info.php?name1=$url_name' style='color: #$group_color; text-decoration: underline;'>$name</a> | Last Active: $active_date<br>";
    }

    // end it all
    output_footer();
    die();
} catch (Exception $e) {
    $message = $e->getMessage();
    output_search($safe_ip);
    echo "<i>Error: $message</i>";
    output_footer();
    die();
}
