<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';

$start = (int) default_get('start', 0);
$count = (int) default_get('count', 100);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('leaderboard-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // header, also check if mod and output the mod links if so
    $staff = is_staff($pdo, token_login($pdo, true, true, 'g'), false);
    output_header('Leaderboard', $staff->mod, $staff->admin);

    // limit amount of entries to be obtained from the db at a time
    if ($staff->mod === true) {
        $count = ($count - $start) > 100 ? 100 : $count;
    } elseif ($staff->mod === false) {
        $rl_msg = 'Please wait at least one minute before trying to view the leaderboard again.';
        rate_limit('leaderboard-'.$ip, 60, 10, $rl_msg);
        $count = ($count - $start) > 50 ? 50 : $count;
    } else {
        throw new Exception('Could not determine user staff boolean.');
    }

    $users = users_select_top($pdo, $start, $count);

    echo '<center>'
        .'<font face="Gwibble" class="gwibble">-- Leaderboard --</font>'
        .'<br /><br />'
        .'<table>'
        .'<tr>'
        .'<th>#</th>'
        .'<th>Username</th>'
        .'<th>Rank</th>'
        .'<th>Hats</th>'
        .'</tr>';

    // get row number
    $i = $start;
    foreach ($users as $user) {
        // increment row number
        $i++;

        // name
        $name = $user->name;
        $safe_name = htmlspecialchars($name, ENT_QUOTES);
        $safe_name = str_replace(' ', "&nbsp;", $safe_name);

        // group
        $group_color = get_group_info($user)->color;

        // rank
        $active_rank = (int) $user->active_rank;
        $tokens_used = (int) $user->tokens_used;
        $rank_str = $tokens_used > 0 ? "$active_rank<br>($tokens_used tokens used)" : $active_rank;

        // hats
        $hat_array = $user->hats;
        $hats = count(explode(',', $hat_array))-1;

        // player details link
        $url_name = urlencode($name);
        if ($staff->admin === true) {
            $info_link = "/admin/player_deep_info.php?name1=$url_name";
        } elseif ($staff->mod === true) {
            $info_link = "/mod/player_info.php?name=$url_name";
        } else {
            $info_link = "player_search.php?name=$url_name";
        }

        // echo the row
        echo '<tr>'
            ."<td>$i</td>"
            ."<td><a href='$info_link' style='color: #$group_color; text-decoration: underline;'>$safe_name</a></td>"
            ."<td>$rank_str</td>"
            ."<td>$hats</td>"
            .'</tr>';
    }

    echo "</table>";
    output_pagination($start, $count);
    echo "</center>";
} catch (Exception $e) {
    output_error_page($e->getMessage(), @$staff);
} finally {
    output_footer();
}
