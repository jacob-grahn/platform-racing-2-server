<?php

require_once '../../fns/all_fns.php';
require_once '../../fns/output_fns.php';

$email = find_no_cookie('email', '');
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

// this will echo the search box when called
function output_search($email = '', $incl_br = true)
{
    echo "<form name='input' action='' method='get'>";
    echo "Email: <input type='text' name='email' value='$email'>&nbsp;";
    echo "<input type='submit' value='Search'></form>";
    if ($incl_br) {
        echo "<br><br>";
    }
}

// admin check try block
try {
    //connect
    $db = new DB();
    
    //make sure you're an admin
    $admin = check_moderator($db, false, 3);
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
    
    // protect the db
    $safe_email = addslashes($email);

    // if there's an email set, let's get data from the db
    $result = $db->query(
        "SELECT power, name, active_date
							FROM users
							WHERE email = '$safe_email'
							ORDER BY active_date DESC
							"
    );
    $row_count = $result->num_rows;
    if ($row_count <= 0) {
        throw new Exception("No accounts found with the email address \"$safe_email\".");
    }
    
    // protect the user
    $disp_email = htmlspecialchars($email);
  
    // show the search form
    output_search($disp_email);
    
    // output the number of results
    if ($row_count == 1) {
        $res = 'result';
    } else {
        $res = 'results';
    }
    echo "$row_count $res found for the email address \"$disp_email\".<br><br>";
    
    // only gonna get here if there were results
    while ($row = $result->fetch_object()) {
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
        echo "<a href='player_deep_info.php?name1=$url_name' style='color: #$group_color; text-decoration: underline;'>$safe_name</a> | Last Active: $active_date<br>";
    }

    // end it all
    output_footer();
    die();
} catch (Exception $e) {
    $message = $e->getMessage();
    output_search($safe_email);
    echo "<i>Error: $message</i>";
    output_footer();
    die();
}
