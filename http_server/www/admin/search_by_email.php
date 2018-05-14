<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/search_by_email_fns.php';
require_once QUERIES_DIR . '/users/users_select_by_email.php';

$ip = get_ip();
$email = find_no_cookie('email', '');
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

// admin check try block
try {
    // rate limiting
    rate_limit('email-search-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('email-search-'.$ip, 5, 2);

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
    output_header('Deep Email Search', true, true);

    // sanity check: no email in search box
    if (is_empty($email)) {
        output_search('', false);
        output_footer();
        die();
    }

    // if there's an email set, let's get data from the db
    $users = users_select_by_email($pdo, $email);

    // protect the user
    $disp_email = htmlspecialchars($email);

    // show the search form
    output_search($disp_email);

    // output the number of results
    $count = count($users);
    if ($count == 1) {
        $res = 'result';
    } else {
        $res = 'results';
    }
    echo "$count $res found for the email address \"$disp_email\".<br><br>";

    // only gonna get here if there were results
    foreach ($users as $row) {
        // make nice variables for our data
        $url_name = urlencode($row->name); // url encode the name
        $safe_name = htmlspecialchars($row->name); // html friendly name
        $safe_name = str_replace(' ', '&nbsp;', $safe_name); // multiple spaces in name
        $group = (int) $row->power; // power
        $group_color = $group_colors[$group]; // group color
        $active_date = $row->active_date; // active date -- get data
        $active_date = date_create($active_date); // active date -- create a date
        $active_date = date_format($active_date, 'j/M/Y'); // active date -- format the created date
        if ($active_date == '30/Nov/-0001') {
            $active_date = 'Never';
        }

        // display the name with the color and link to the player search page
        echo "<a href='player_deep_info.php?name1=$url_name' style='color: #$group_color; text-decoration: underline;'>
            $safe_name</a> | Last Active: $active_date<br>";
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    output_search($safe_email);
    echo "<i>Error: $message</i>";
} finally {
    output_footer();
    die();
}
