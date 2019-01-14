<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/contests.php';
require_once QUERIES_DIR . '/contest_winners.php';

$ip = get_ip();

try {
    // rate limiting
    rate_limit("contests-list-".$ip, 60, 10, "Wait a bit before trying to view the contests list again.");
    rate_limit("contests-list-".$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // determine the user's group
    $user_id = (int) token_login($pdo, true, true);
    $staff = is_staff($pdo, $user_id, false);

    // output the correct header
    output_header("Contests", $staff->mod, $staff->admin);

    // output the text at the top of the page
    echo '<center><p>'
        .'<font face="Gwibble" class="gwibble">-- Contests --</font>'
        .'<br>You can participate in contests to earn prizes!'
        .'<br>'
        .'<br>To learn more about a specific contest, click on one of the contests below.'
        .'<br>To learn how to host your own contest, '
        .'<a href="https://jiggmin2.com/forums/showthread.php?tid=28" target="_blank">click here</a>.'
        .'</p></center>';

    // show the link to create a new contest if an admin
    if ($staff->admin === true) {
        echo "<p><b>Admin: <a href='/admin/contests/add_contest.php'>Add New Contest</a></b></p>";
    }

    // get the right list of contests
    $contests = contests_select($pdo, !$staff->admin);
    if ($contests === false) {
        throw new Exception('Could not find any contests. :(');
    }

    // url prefix for contest host links based on group
    if ($staff->admin === true) {
        $base_url = "/admin/player_deep_info.php?name1=";
    } elseif ($staff->admin === false && $staff->mod === true) {
        $base_url = "/mod/player_info.php?name=";
    } else {
        $base_url = "/player_search.php?name=";
    }

    foreach ($contests as $contest) {
        $is_active = check_value($contest->active, 1, "Yes", "No");
        $contest_id = (int) $contest->contest_id;
        $contest_name = $contest->contest_name;
        $desc = $contest->description;
        $contest_url = $contest->url;
        $host_id = (int) $contest->user_id;
        $awarding = $contest->awarding;

        // get some info
        $host = user_select_name_and_power($pdo, $host_id);
        $host_color = $group_colors[(int) $host->power];
        $is_host = $user_id === $host_id ? true : false;

        // safety first
        $html_contest_name = htmlspecialchars($contest_name, ENT_QUOTES);
        $html_desc = htmlspecialchars($desc, ENT_QUOTES);
        $html_awarding = htmlspecialchars($awarding, ENT_QUOTES);
        $html_contest_url = htmlspecialchars($contest_url, ENT_QUOTES);
        $html_host_name = htmlspecialchars($host->name, ENT_QUOTES);
        $html_url_host_name = htmlspecialchars(urlencode($host->name), ENT_QUOTES);

        // start the paragraph
        echo "<p>";

        // contest name
        echo "<b><a href='$html_contest_url' target='_blank'>$html_contest_name</a></b><br>";

        // admin: is it active?
        if ($staff->admin === true) {
            echo "Active: $is_active<br>";
        }

        // description
        echo "Description: $html_desc<br>";

        // contest host
        $host_url = $base_url . $html_url_host_name;
        echo "Run by: <a href='$host_url' style='color: #$host_color; text-decoration: underline;'>"
            ."$html_host_name</a><br>";

        // awarding
        echo "Awarding: $html_awarding<br>";

        // mod
        if (($staff->mod === true || $is_host === true) && $host->power < 2) {
            $max_awards = (int) $contest->max_awards;
            $used_awards = (int) throttle_awards($pdo, $contest_id, $host_id);
            echo "Used Awards (this week): $used_awards<br>"
                ."Max Awards (per week): $max_awards<br>";
        }

        // admin
        if ($staff->admin === true) {
            echo 'Admin: '
                ."<a href='/admin/contests/edit_contest.php?contest_id=$contest_id'>edit</a> | "
                ."<a href='/admin/contests/add_prize.php?contest_id=$contest_id'>add prize</a> | "
                ."<a href='/admin/contests/remove_prize.php?contest_id=$contest_id'>remove prize</a><br>";
        }

        // view winners
        echo "<a href='view_winners.php?contest_id=$contest_id'>-&gt; View Winners</a>";

        // award prize
        if ((($is_host === true || $staff->mod === true) && (int) $contest->active === 1) || $staff->admin === true) {
            echo "<br><a href='award_prize.php?contest_id=$contest_id'>-&gt; Award Prize</a>";
        }

        // end contest, move onto the next one
        echo "</p>";
    }
} catch (Exception $e) {
    output_header("Error");
    echo 'Error: ' . $e->getMessage();
} finally {
    output_footer();
}
