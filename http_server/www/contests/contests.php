<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/contests/contests_select.php';
require_once __DIR__ . '/../../queries/users/user_select_name_and_power.php';

$ip = get_ip();
$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

try {
    // rate limiting
    rate_limit("contests-list-".$ip, 60, 15, "Wait a bit before trying to view the contests list again.");
    rate_limit("contests-list-".$ip, 5, 2);

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
    output_header("Contests", $is_mod, $is_admin);
    
    // output the text at the top of the page
    echo "<center><p>
    <font face='Gwibble' class='gwibble'>-- Contests --</font>
    <br>You can participate in contests to earn prizes!
    <br>
    <br>To learn more about a specific contest, click on one of the contests below.
    <br>To learn how to host your own contest, <a href='https://jiggmin2.com/forums/showthread.php?tid=28' target='_blank'>click here</a>.
    </p></center>";
    
    // get the right list of contests
    if ($is_admin == true) {
    	echo "<p><b>Admin: <a href='/admin/contests/add_contest.php'>Add New Contest</a></b></p>";
        $contests = contests_select($pdo, false);
    } else {
        $contests = contests_select($pdo, true);
    }
    
    if ($contests == false) {
    	throw new Exception('Could not find any contests. :(');
    }
    
    // url prefix for contest host links based on group
    if ($is_admin == true) {
        $host_base_url = "https://pr2hub.com/admin/player_deep_info.php?name1=";
    } else if ($is_admin == false && $is_mod == true) {
        $host_base_url = "https://pr2hub.com/mod/do_player_search.php?name=";
    } else {
        $host_base_url = "https://pr2hub.com/player_search.php?name=";
    }
    
    foreach ($contests as $contest) {
        $active_bool = check_value($contest->active, 1, true, false);
        $is_active = check_value($contest->active, 1, "Yes", "No");
        $contest_id = $contest->contest_id;
        $contest_name = $contest->contest_name;
        $desc = $contest->description;
        $awarding = $contest->awarding;
        $contest_url = $contest->url;
        $is_host = false;
        $host_id = $contest->user_id;
        $host = user_select_name_and_power($pdo, $host_id);
        $html_host_name = htmlspecialchars($host->name);
        $url_host_name = url_encode($host->name);
        $host_color = $group_colors[$host->power);
        
        // are they the host?
        if ($user_id == $host_id) {
            $is_host = true;
        }
        
        // start the paragraph
        echo "<p>";
        
        // contest name
        echo "<b><a href='$contest_url' target='_blank'>$contest_name</a></b><br>";
        
        // admin: is it active?
        if ($is_admin == true) {
            echo "Active: $is_active<br>"
        }
        
        // description
        echo "<br>Description: $desc<br>";
        
        // contest host
        $host_url = $host_base_url . $url_host_name;
        echo "Run by: <a href='$host_url' target='_blank' style='color: $host_color; text-decoration: underline;'>$html_host_name</a><br>";
        
        // awarding
        echo "Awarding: $awarding<br>";
        
        // admin
        if ($is_admin == true) {
            echo "<br>Admin: <a href='/admin/contests/edit_contest.php?contest_id=$contest_id'>edit</a> | ";
            if ($active_bool == true) {
                echo "<a href='/admin/contests/deactivate_contest.php?contest_id=$contest_id'>deactivate | ";
            } else {
                echo "<a href='/admin/contests/activate_contest.php?contest_id=$contest_id'>activate | ";
            }
            echo "<a href='/admin/contests/add_prize.php?contest_id=$contest_id'>add prize</a> | ";
            echo "<a href='/admin/contests/remove_prize.php?contest_id=$contest_id'>remove prize</a><br>";
        }
        
        // view winners
        echo "<a href='view_winners.php?contest_id=$contest_id'>-&gt; View Winners</a>";
        
        // award prize
        if ($is_host == true) {
            echo "<br><a href='award_prize.php?contest_id=$contest_id'>-&gt; Award Prize</a>";
        }
        
        // end contest, move onto the next one
        echo "</p>";
    }
    
    // end it
    output_footer();
    die();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    output_footer();
    die();
}
    
    
