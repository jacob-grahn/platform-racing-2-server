<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/contests/contest_select.php';
require_once QUERIES_DIR . '/contest_winners/contest_winners_select_by_contest.php';
require_once QUERIES_DIR . '/users/user_select_name_and_power.php';

$ip = get_ip();
$contest_id = (int) default_get('contest_id', 0);
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

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
    $user_id = (int) token_login($pdo, true, true);
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
    $contest = contest_select($pdo, $contest_id, !$is_mod);
    
    // make some variables
    $contest_id = (int) $contest->contest_id;
    $contest_name = htmlspecialchars($contest->contest_name);
    $contest_url = htmlspecialchars($contest->url);
    
    // get the winners
    $winners = contest_winners_select_by_contest($pdo, $contest_id, !$is_mod);
    
    // sanity check: are there any winners?
    if (empty($winners)) {
        throw new Exception("Could not find any winners for $contest_name. :(");
    }
    
    // url prefix for contest host links based on group
    if ($is_admin === true) {
        $base_url = "/admin/player_deep_info.php?name1=";
    } elseif ($is_admin === false && $is_mod === true) {
        $base_url = "/mod/do_player_search.php?name=";
    } else {
        $base_url = "/player_search.php?name=";
    }
    
    // contest name
    echo "<p>Viewing Winners for <a href='$contest_url' target='_blank'>$contest_name</a></p>";
    
    // winners table
    echo "<table class='noborder'>" // start table
        ."<tbody>"
        ."<tr>" // start header row
        ."<th class='noborder'><b>Date</b></th>" // date column
        ."<th class='noborder'><b>Name</b></th>"; // name column
    if ($is_mod == true) {
        echo "<th class='noborder'><b>Awarded By</b></th>" // awarder column (for staff)
            ."<th class='noborder'><b>From IP</b></th>" // from IP column (for staff)
            ."<th class='noborder'><b>Prizes Awarded</b></th>" // prizes awarded to this winner (for staff)
            ."<th class='noborder'><b>Comments</b></th>"; // award comments (for staff)
    }
    echo "</tr>"; // end title row
    
    foreach ($winners as $winner) {
        // win time
        $win_time = (int) $winner->win_time;
        $short_win_time = date("d/M/Y", $win_time);
        $full_win_time = date("g:i:s A \o\\n l, F jS, Y", $win_time);
        
        // get awards
        $prizes_awarded = $winner->prizes_awarded;
        $prizes_awarded = explode(",", $prizes_awarded);
        $last_prize = end($prizes_awarded);
        
        // other variables
        $awarder_ip = htmlspecialchars($winner->awarder_ip);
        $comment = htmlspecialchars($winner->comment);
        
        // awarder name and color (staff)
        if ($is_mod === true) {
            $awarder_id = (int) $winner->awarded_by;
            $awarder = user_select_name_and_power($pdo, $awarder_id);
            $awarder_html_name = htmlspecialchars($awarder->name);
            $awarder_url = $base_url . htmlspecialchars(urlencode($awarder->name));
            $awarder_color = $group_colors[(int) $awarder->power];
        }
        
        // winner name and color
        $winner = user_select_name_and_power($pdo, $winner->winner_id);
        $winner_color = $group_colors[$winner->power];
        $winner_html_name = htmlspecialchars($winner->name);
        $winner_url = $base_url . htmlspecialchars(urlencode($winner->name));
        
        // start row
        echo "<tr>"
            ."<td class='noborder' title='Awarded at $full_win_time'>$short_win_time</td>" // date row
            ."<td class='noborder'><a href='$winner_url' style='color: #$winner_color; text-decoration: underline;'>$winner_html_name</td>"; // name row
        if ($is_mod === true) {
            echo "<td class='noborder'><a href='$awarder_url' style='color: #$awarder_color; text-decoration: underline;'>$awarder_html_name</a></td>" // who awarded
                ."<td class='noborder'>$awarder_ip</td>"; // awarder ip
            
            // output readable prizes
            echo "<td class='noborder'>";
            foreach ($prizes_awarded as $prize) {
                echo htmlspecialchars($prize);
                if ($prize != $last_prize) {
                    echo ', '; // separator; don't echo after last prize
                }
            }
            echo "</td>";
            
            
            // comment
            echo "<td class='noborder'>$comment</td>";
        }
        
        // end row
        echo "</tr>";
    }
    
    // end table
    echo "</tbody></table>";
    
    // back link
    echo "<br><br><a href='contests.php'>&lt;- All Contests";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
} finally {
    output_footer();
    die();
}
