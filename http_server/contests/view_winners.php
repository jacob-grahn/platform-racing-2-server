<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/contests.php';
require_once QUERIES_DIR . '/contest_winners.php';

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
    $user_id = (int) token_login($pdo, true, true);
    $staff = is_staff($pdo, $user_id);
    $is_mod = $staff->mod && !$staff->trial;
    $is_admin = $staff->admin;

    // get the contest info
    $contest = contest_select($pdo, $contest_id, !$is_mod);
    $is_host = (int) $contest->user_id === $user_id;

    // make some variables
    $contest_id = (int) $contest->contest_id;
    $contest_name = htmlspecialchars($contest->contest_name, ENT_QUOTES);
    $contest_url = htmlspecialchars($contest->url, ENT_QUOTES);

    // get the winners
    $winners = contest_winners_select_by_contest($pdo, $contest_id, !($is_mod || $is_host));

    // sanity check: are there any winners?
    if (empty($winners)) {
        throw new Exception("Could not find any winners for $contest_name. :(");
    }

    // url prefix for contest host links based on group
    if ($is_admin) {
        $base_url = "/admin/player_deep_info.php?name1=";
    } elseif (!$is_admin && $is_mod) {
        $base_url = "/mod/player_info.php?name=";
    } else {
        $base_url = "/player_search.php?name=";
    }

    // output
    output_header("View Winners", $staff->mod, $is_admin);

    // contest name
    echo "<p>Viewing Winners for <a href='$contest_url' target='_blank'>$contest_name</a></p>";

    // winners table
    echo "<table class='noborder'>" // start table
        ."<tbody>"
        ."<tr>" // start header row
        ."<th class='noborder'><b>Date</b></th>" // date column
        ."<th class='noborder'><b>Name</b></th>"; // name column
    if ($is_mod || $is_host) {
        $ip_col = $is_mod ? "<th class='noborder'><b>From IP</b></th>" : '';
        echo "<th class='noborder'><b>Awarded By</b></th>" // awarder column
            .$ip_col // from IP column (for staff)
            ."<th class='noborder'><b>Prizes Awarded</b></th>" // prizes awarded to this winner
            ."<th class='noborder'><b>Comments</b></th>"; // award comments
    }
    echo "</tr>"; // end title row

    foreach ($winners as $winner) {
        // win time
        $win_time = (int) $winner->win_time;
        $short_win_time = date("d/M/Y", $win_time);
        $full_win_time = date("g:i:s A \o\\n l, F jS, Y", $win_time);

        // get awards
        $prizes_awarded = $winner->prizes_awarded;
        $prizes_awarded = explode(";", $prizes_awarded);
        $last_prize = end($prizes_awarded);

        // other variables
        $awarder_ip = htmlspecialchars($winner->awarder_ip, ENT_QUOTES);
        $comment = htmlspecialchars($winner->comment, ENT_QUOTES);

        // awarder name and color
        if ($is_mod || $is_host) {
            $awarder_id = (int) $winner->awarded_by;
            $awarder = user_select_name_and_power($pdo, $awarder_id);
            $awarder_url = $base_url . urlencode($awarder->name);
            $awarder_color = get_group_info($awarder)->color;
        }

        // winner name and color
        $winner = user_select_name_and_power($pdo, $winner->winner_id);
        $winner_color = get_group_info($winner)->color;
        $winner_url = $base_url . htmlspecialchars(urlencode($winner->name), ENT_QUOTES);

        // start row
        echo "<tr>"
            ."<td class='noborder' title='Awarded at $full_win_time'>$short_win_time</td>" // date row
            .'<td class="noborder">' . urlify($winner_url, $winner->name, $winner_color) . '</td>'; // name row
        if ($is_mod || $is_host) {
            $ip_col = $is_mod ? "<td class='noborder'>$awarder_ip</td>" : '';
            echo '<td class="noborder">' . urlify($awarder_url, $awarder->name, $awarder_color) . '</td>' // who awarded
                .$ip_col; // awarder ip (for staff)

            // output readable prizes
            echo "<td class='noborder'>";
            foreach ($prizes_awarded as $prize) {
                echo htmlspecialchars($prize, ENT_QUOTES);
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
    $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    output_header("Error");
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
}
