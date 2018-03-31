<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/contest_winners/contest_winners_select_by_contest.php';
require_once __DIR__ . '/../../queries/users/user_select_power_by_name.php';

$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];
$ip = get_ip();

$contest_id = (int) default_get('contest_id', 0);

try {
    // rate limiting
    rate_limit("contest-winners-".$ip, 60, 5, "Wait a bit before trying to view the winners for a contest again.");
    rate_limit("contest-winners-".$ip, 5, 1);
    
    // sanity check: is it a valid contest id?
    if (is_empty($contest_id, false)) {
        throw new Exception("Could not find a contest with that ID.");
    }

    // connect
    $pdo = pdo_connect();
    
    // determine the user's group
    $user_id = token_login($pdo, true, true);
    $is_staff = is_staff($pdo, $user_id);
    $is_mod = $is_staff->mod;
    $is_admin = $is_staff->admin;
} catch (Exception $e) {
    output_header("Error");
    echo 'Error: ' . $e->getMessage();
    output_footer();
    die();
}

try {
    // output the correct header
    output_header("View Winners", $is_mod, $is_admin);
    
    // get the contest info
    $contest = contest_select($pdo, $contest_id);
    
    // make some variables
    $contest_id = $contest->contest_id;
    $contest_name = $contest->contest_name;
    
    // get the winners
    if ($is_mod == true) {
        $winners = contest_winners_select_by_contest($pdo, $contest_id, false);
    } else {
        $winners = contest_winners_select_by_contest($pdo, $contest_id);
    }
    
    if (empty($winners)) {
        $contest_name = htmlspecialchars($contest_name);
        throw new Exception("Could not find any winners for $contest_name. :(");
    }
    
    // url prefix for contest host links based on group
    if ($is_admin == true) {
        $base_url = "/admin/player_deep_info.php?name1=";
    } else if ($is_admin == false && $is_mod == true) {
        $base_url = "/mod/do_player_search.php?name=";
    } else {
        $base_url = "/player_search.php?name=";
    }
    
    // winners table
    echo "<table class='noborder'>" // start table
        ."<tbody>"
        ."<tr>" // start header row
        ."<th class='noborder'><b>Date</b></th>" // date column
        ."<th class='noborder'><b>Name</b></th>"; // name column
    if ($is_mod == true) {
        echo "<th class='noborder'><b>Awarded by</b></th>" // awarder column (for staff)
            ."<th class='noborder'><b>From IP</b></th>" // from IP column (for staff)
            ."<th class='noborder'><b>Comments</b></th>"; // award comments (for staff)
    }
    echo "</tr>"; // end title row
    
    foreach ($winners as $winner) {
        // awarder name
        $awarder_html_name = htmlspecialchars($winner->awarded_by);
        $awarder_url = $base_url . urlencode($winner->awarded_by);
        
        // winner name and color
        $winner_power = user_select_power_by_name($pdo, $winner->winner_name);
        $winner_color = $group_colors[$winner_power];
        $winner_html_name = htmlspecialchars($winner->winner_name);
        $winner_url = $base_url . url_encode($winner->winner_name);
        
        // win time
        $win_time = (int) $winner->win_time;
        $short_win_time = date("d/M/Y", $win_time);
        $full_win_time = date("g:i:s A \o\\n l, F jS, Y", $win_time);
        
        // other variables
        $host_ip = $winner->host_ip;
        $comment = $winner->comment;
        
        // start row
        echo "<tr>"
            ."<td class='noborder' title='Awarded at $full_win_time'>$short_win_time</td>" // date row
            ."<td class='noborder'><a href='$winner_url' style='color: $winner_color; text-decoration: underline;'>$winner_html_name</td>"; // name row
        if ($is_mod == true) {
            echo "<td class='noborder'><a href='$awarder_url'>$awarder_html_name</a></td>"
                ."<td class='noborder'>$host_ip</td>"
                ."<td class='noborder'>$comment</td>";
        }
        
        // end row
        echo "</tr>";
    }
    
    // end table
    echo "</tbody></table>";
    
    // back link
    echo "<br><br><a href='contests.php'>&lt;- All Contests";
    
    // end it
    output_footer();
    die();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    output_footer();
    die();
}
