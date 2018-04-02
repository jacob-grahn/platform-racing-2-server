<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/recent_logins/recent_logins_user_select_by_ip.php';
require_once __DIR__ . '/../../queries/users/user_select_id_by_ip.php';
require_once __DIR__ . '/../../queries/users/user_select_name_active_power.php';

$ip = find_no_cookie('ip', '');
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

// sort user ids by descending
function sort_ids($first, $second) {
	return strtotime($first->active_date) < strtotime($second->active_date);
}

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

    // if there's an IP set, let's get data from the db
    $users = users_select_by_ip($pdo, $ip);
    $logins = recent_logins_user_select_by_ip($pdo, $ip);
    
    // put all the IDs in an array
    $all_ids = array();
    foreach ($users as $user) {
    	$user_id = (int) $user->user_id;
    	array_push($all_ids, $user_id);
    }
    foreach ($logins as $login) {
    	$user_id = (int) $login->user_id;
    	array_push($all_ids, $user_id);
    }
    
    // remove duplicates
    $all_ids = array_unique($all_ids);

    // protect the user
    $disp_ip = htmlspecialchars($ip);

    // show the search form
    output_search($disp_ip);

    // output the number of results
    $count = count($users);
    if ($count == 1) {
        $res = 'result';
    } else {
        $res = 'results';
    }
    echo "$count $res found for the IP address \"$disp_ip\".<br><br>";

    // only gonna get here if there were results
    $all_data = array();
    foreach ($all_ids as $user_id) {
    	$user = user_select_name_active_power($pdo, $user_id, true);
    	if ($user != false) {
        	array_push($all_data, $user);
        }
    }
    
    // sort the data by time descending
    usort($all_data, "sort_ids");
    
    foreach ($all_data as $user) {
        $url_name = urlencode($user->name); // url encode the name
        $safe_name = htmlspecialchars($user->name); // html friendly name
        $safe_name = str_replace(' ', '&nbsp;', $safe_name); // multiple spaces in name
        $group = (int) $user->power; // power
        $group_color = $group_colors[$group]; // group color
        $active_date = $user->active_date; // active date -- get data
        $active_date = date_create($active_date); // active date -- create a date
        $active_date = date_format($active_date, 'j/M/Y'); // active date -- format the created date
        if ($active_date == '30/Nov/-0001') {
            $active_date = 'Never';
        }
    
    	// display the name with the color and link to the player search page
        echo "<a href='player_deep_info.php?name1=$url_name' style='color: #$group_color; text-decoration: underline;'>$safe_name</a> | Last Active: $active_date<br>";
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
