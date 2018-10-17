<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/player_deep_logins_fns.php';
require_once QUERIES_DIR . '/users/user_select_by_name.php';
require_once QUERIES_DIR . '/recent_logins/recent_logins_select.php';
require_once QUERIES_DIR . '/recent_logins/recent_logins_count_by_user.php';


$name = find('name', '');
$start = (int) default_get('start', 0);
$count = (int) default_get('count', 250);
$ip = get_ip();

// sanity check: ensure the database doesn't overload itself
if ($count > 500) {
    $count = 500;
}

// admin check try block
try {
    // rate limiting
    rate_limit('player-deep-logins-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('player-deep-logins-'.$ip, 5, 2);

    //connect
    $pdo = pdo_connect();

    //make sure you're an admin
    $admin = check_moderator($pdo, false, 3);

    // if there's a name set, let's get data from the db
    if ($name) {
        // check if the user exists, get user ID
        $user_id = name_to_id($pdo, $name, true);
        if ($user_id === false) {
            throw new Exception("Could not find a user with that name.");
        }

        // get login data
        $login_count = recent_logins_count_by_user($pdo, $user_id);
        $logins = recent_logins_select($pdo, $user_id, true, $start, $count);
    }

    // output
    output_header('Player Deep Logins', true, true);
    if ($name) {
        // calculate the number of results and the grammar to go with it
        if ($login_count != 1) {
            $logs = 'logins';
        } else {
            $logs = 'login';
        }

        // safety first
        $safe_name = htmlspecialchars($name, ENT_QUOTES);

        // show the search form
        output_search($safe_name);

        // make dat variable
        $is_end = false;

        // this determines if anything is shown on the page
        if ($login_count > 0 && count($logins) > 0) {
            $end = $start + count($logins);
            echo "$login_count $logs recorded for the user \"$safe_name\".";
            echo "<br>Showing results $start - $end.<br><br>";
            if ($end == $login_count) {
                $is_end = true;
            }
        } else {
            echo "No results found for the search parameters.";
        }

        if ($login_count > 0 && count($logins) > 0) {
            echo '<p>---</p>';
            $url_name = urlencode($name);
            output_pagination($start, $count, "&name=$url_name", $is_end);
        }

        // only gonna get here if there were results
        foreach ($logins as $row) {
            // make nice variables for our data
            $ip = htmlspecialchars($row->ip, ENT_QUOTES); // ip
            $country = htmlspecialchars($row->country, ENT_QUOTES); // country code
            $date = htmlspecialchars($row->date, ENT_QUOTES); // date

            // display the data
            echo "IP: $ip | Country Code: $country | Date: $date<br>";
        }

        // output page navigation
        if ($login_count > 0 && count($logins) > 0) {
            $url_name = urlencode($name);
            output_pagination($start, $count, "&name=$url_name", $is_end);
        }
    } else {
        output_search('', false);
    }

    output_footer();
} catch (Exception $e) {
    $message = $e->getMessage();
    output_header('Error');
    echo "Error: $message";
    output_footer();
}
