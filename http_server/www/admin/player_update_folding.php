<?php

// get general functions
require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';

// select stats from fah db
require_once __DIR__ . '/../../queries/fah/stats/stats_select_by_name.php';
require_once __DIR__ . '/../../queries/fah/stats/stats_insert.php';

// folding_at_home data select/insert/update from/into/in pr2 db
require_once __DIR__ . '/../../queries/folding/folding_insert.php';
require_once __DIR__ . '/../../queries/folding/folding_select_by_user_id.php';
require_once __DIR__ . '/../../queries/folding/folding_select_list.php';
require_once __DIR__ . '/../../queries/folding/folding_update.php';
require_once __DIR__ . '/../../cron/fah_award_prizes_fns.php';

// message, insert rank token
require_once __DIR__ . '/../../queries/messages/message_insert.php';
require_once __DIR__ . '/../../queries/rank_tokens/rank_token_upsert.php';

// make some variables
$ip = get_ip();
$fah_name = str_replace(' ', '_', find('name')); // debugging

// check permission
try {
    // rate limiting
    rate_limit('refresh-user-folding-'.$ip, 60, 10);
    rate_limit('refresh-user-folding-'.$ip, 5, 2);

    // connect to the pr2 database
    $pr2_pdo = pdo_connect();
    
    // check if the user is an admin
    $admin = check_moderator($pr2_pdo, true, 3);
} catch (Exception $e) {
    output_header('Error');
    echo "Error: " . $e->getMessage();
    output_footer();
    die();
}

// admin validated check/update stats
try {
    // output header
    output_header('Update Folding Data', true, true);

    // get folding data from folding.stanford.edu
    $fah_data_server = json_decode(file_get_contents("http://folding.stanford.edu/stats/api/donors?team=143016&name=$fah_name"));

    // sanity check: was any data received?
    $kill = false;
    if (empty($fah_data_server)) {
        $kill = true;
        throw new Exception("Error: Could not connect to folding.stanford.edu.");
    } // sanity check: has this user folded anything for Team Jiggmin?
    if ($fah_data_server->description == 'No results') {
        $kill = true;
        $name = htmlspecialchars(find('name'));
        throw new Exception("Error: The user $name has not earned any Folding@Home points for Team Jiggmin.");
    }
    
    // new variables from fah server
    $fah_server = $fah_data_server->results;
    $pr2_name = str_replace('_', ' ', $fah_server['name']);
    $fah_points = (int) $fah_server['credit'];
    $fah_wus = (int) $fah_server['wus'];
    $fah_rank = (int) $fah_server['rank'];
    
    // connect to the fah database
    $fah_pdo = pdo_fah_connect();
    
    // get folding data from fah database
    $fah_db = stats_select_by_name($fah_pdo, $pr2_name);
    
    // check if a db entry exists
    $has_entry = true;
    if (empty($fah_db)) {
        $has_entry = false;
    }
    
    // if they have an entry, compare values and update
    if ($has_entry === true) {
        $db_points = (int) $fah_db->points;
        $db_wus = (int) $fah_db->wu;
        $db_rank = (int) $fah_db->rank;
    
        // check if they're equivalent
        if (
            $db_points === $fah_points
            && $db_wus === $fah_wus
            && $db_rank === $fah_rank
        ) {
            $name = htmlspecialchars($pr2_name);
            throw new Exception(
                "<span style='color: red;'>"
                ."The folding data for $name is up-to-date with the Folding@Home server."
                ."</span>"
                ."<br>"
            );
        }

        // if the database has values less than or equal to the folding server, update
        if ($db_points <= $fah_points
            && $db_wus <= $fah_wus
            && $db_rank <= $fah_rank
        ) {
            echo "Updating $pr2_name's Folding Data...<br>"
                ."Old Points: $db_points | New Points: $fah_points<br>"
                ."Old WUs: $db_wus | New WUs: $fah_wus<br>"
                ."Old Rank: $db_rank | New Rank: $fah_rank<br>";
            stats_insert($fah_pdo, $pr2_name, $fah_wus, $fah_points, $fah_rank);
            echo "<span style='color: green;'>Done! Moving on...</span><br>";
        }
    } // if they don't have a folding entry in the database, insert one
    elseif ($has_entry === false) {
        echo "Creating new Folding@Home database entry for $pr2_name...<br>"
            ."Points: $fah_points | "
            ."WUs: $fah_wus | "
            ."Rank: $fah_rank<br>";
        stats_insert($fah_pdo, $pr2_name, $fah_wus, $fah_points, $fah_rank);
        echo "<span style='color: green;'>Done! Moving on...</span><br>";
    } else {
        $kill = true;
        $name = htmlspecialchars($pr2_name);
        throw new Exception("CRITICAL ERROR: Could not determine if $name has an entry.");
    }
} catch (Exception $e) {
    echo $e->getMessage();
    if ($kill === true) {
        output_footer();
        die();
    }
}

// admin validated update awards
try {
    $folding_user = folding_select_by_user_id($pr2_pdo, name_to_id($pr2_name));
    $prize_array[strtolower($folding_user->name)] = $folding_user;
    $user = stats_select_by_name($fah_pdo, $pr2_name);
    
    add_prizes($pr2_pdo, $user->fah_name, $user->points, $prize_array, array());
    $url_name = htmlspecialchars(urlencode($pr2_name));
    $pr2_name = htmlspecialchars($pr2_name);
    echo "<br><br><span style='color: green;'>Great success! All operations completed successfully.</span>"
        ."<br><br><a href='player_deep_info.php?name1=$url_name'>&lt;- Go Back</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    output_footer();
    die();
}
