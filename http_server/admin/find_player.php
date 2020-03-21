<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/find_player_fns.php';

$ip = get_ip();
$query = find_no_cookie('query', '');
$header = false;

try {
    // rate limiting
    rate_limit('find-player-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('find-player-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    is_staff($pdo, token_login($pdo), false, true, 3);

    $header = true;
    output_header('Find Player by Keyword', true, true);

    // sanity check: no email in search box
    if (is_empty($query)) {
        output_search('', false);
    } else {
        // if there's an email set, let's get data from the db
        $users = users_search($pdo, $query);

        // protect the user
        $disp_query = htmlspecialchars($query, ENT_QUOTES);

        // show the search form
        output_search($disp_query);

        // output the number of results
        $count = count($users);
        $res = $count === 1 ? 'result' : 'results';
        echo "$count $res found for the keyword \"$disp_query\".<br><br>";

        // only gonna get here if there were results
        foreach ($users as $row) {
            // make nice variables for our data
            $url_name = urlencode($row->name); // url encode the name
            $safe_name = str_replace(' ', '&nbsp;', htmlspecialchars($row->name, ENT_QUOTES)); // html name w/ spaces
            $group = (int) $row->power; // power
            $group_color = $group_colors[$group]; // group color
            $active_date = date('j/M/Y', $row->time); // active date

            // display the name with the color and link to the player search page
            echo "<a href='player_deep_info.php?name1=$url_name'"
                ."style='color: #$group_color;"
                ."text-decoration: underline;'>"
                ."$safe_name</a> | Last Active: $active_date"
                ."<br>";
        }
    }
} catch (Exception $e) {
    if ($header === false) {
        output_header('Error');
    }
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
}
