<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/search_by_email_fns.php';
require_once QUERIES_DIR . '/users/users_select_by_email.php';

$ip = get_ip();
$email = find_no_cookie('email', '');

// admin check try block
try {
    // rate limiting
    rate_limit('email-search-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('email-search-'.$ip, 5, 2);

    //connect
    $pdo = pdo_connect();

    //make sure you're an admin
    is_staff($pdo, token_login($pdo), false, true, 3);

    output_header('Deep Email Search', true, true);

    // sanity check: no email in search box
    if (is_empty($email)) {
        output_search('', false);
    } else {
        // if there's an email set, let's get data from the db
        $users = users_select_by_email($pdo, $email);

        // protect the user
        $disp_email = htmlspecialchars($email, ENT_QUOTES);

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
            $safe_name = htmlspecialchars($row->name, ENT_QUOTES); // html friendly name
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
            echo "<a href='player_deep_info.php?name1=$url_name'
                style='color: #$group_color;
                text-decoration: underline;'>
                $safe_name</a> | Last Active: $active_date<br>";
        }
    }
    output_footer();
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    output_header('Error');
    echo "Error: $error";
    output_footer();
}
