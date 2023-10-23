<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/player_deep_logins_fns.php';
require_once QUERIES_DIR . '/recent_logins.php';

$name = default_get('name', '');
$start = (int) default_get('start', 0);
$count = (int) default_get('count', 250);
$ip = get_ip();

// sanity check: ensure the database doesn't overload itself
$count = $count > 500 ? 500 : $count;

// admin check try block
try {
    // rate limiting
    rate_limit('player-deep-logins-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('player-deep-logins-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    is_staff($pdo, token_login($pdo), false, true, 3);

    // if there's a name set, let's get data from the db
    if ($name) {
        // check if the user exists, get user ID
        $user_id = name_to_id($pdo, $name, true);
        if ($user_id === false) {
            throw new Exception("Could not find a user with that name.");
        }

        // get login data
        $login_count = (int) recent_logins_count_by_user($pdo, $user_id);
        $logins = recent_logins_select($pdo, $user_id, true, $start, $count);
    }

    // output
    output_header('Player Deep Logins', true, true);

    if ($name) {
        // calculate the number of results and the grammar to go with it
        $logs = $login_count !== 1 ? 'logins' : 'login';

        // safety first
        $safe_name = htmlspecialchars($name, ENT_QUOTES);

        // show the search form
        output_search($safe_name);

        // this determines if anything is shown on the page
        $is_end = false;
        if ($login_count > 0 && count($logins) > 0) {
            $end = $start + count($logins);
            echo "$login_count $logs recorded for the user \"$safe_name\".";
            echo "<br>Showing results $start - $end.<br><br>";
            $is_end = $end === $login_count;
        } else {
            echo "No results found for the search parameters.";
        }

        if ($login_count > 0 && count($logins) > 0) {
            echo '<p>---</p>';
            $url_name = urlencode($name);
            output_pagination($start, $count, "&name=$url_name", $is_end);
        }

        // only gonna get here if there were results
        foreach ($logins as $login) {
            // make nice variables for our data
            $safe_ip = htmlspecialchars($login->ip, ENT_QUOTES);
            $url_ip = urlencode($login->ip);
            $ip = "<a href='/mod/ip_info.php?ip=$url_ip'>$safe_ip</a>"; // ip
            $country = htmlspecialchars($login->country, ENT_QUOTES); // country code
            $date = htmlspecialchars($login->date, ENT_QUOTES); // date

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
} catch (Exception $e) {
    output_error_page($e->getMessage());
} finally {
    output_footer();
}
