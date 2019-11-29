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
    $staff = is_staff($pdo, token_login($pdo, true, true), false);
    output_header('Level Leaderboard', $staff->mod, $staff->admin);

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

    $levels = levels_select_top($pdo, $start, $count);

    echo '<center>'
        .'<font face="Gwibble" class="gwibble">-- Level Leaderboard --</font>'
        .'<br /><br />'
        .'<table>'
        .'<tr>'
        .'<th>#</th>'
        .'<th>Level</th>'
        .'<th>Plays</th>'
        .'</tr>';

    // get row number
    $i = $start;
    foreach ($levels as $level) {
        // increment row number
        $i++;
        
        // id
        $id = $level->level_id;

        // title
        $title = $level->title;
        $safe_title = htmlspecialchars($title, ENT_QUOTES);
        $safe_title = str_replace(' ', "&nbsp;", $safe_title);
        
        // user
        $user = $level->name;
        $safe_name = htmlspecialchars($user, ENT_QUOTES);
        $safe_name = str_replace(' ', "&nbsp;", $safe_name);

        // group
        $group = (int) $level->power;
        $group_color = $group_colors[$group];

        // plays
        $play_count = $level->play_count;
        
        // level link
        $level_link = "/levels/$id" + ".txt";

        // player details link
        $url_name = urlencode($name);
        if ($staff->admin === true) {
            $player_link = "/admin/player_deep_info.php?name1=$url_name";
        } elseif ($staff->mod === true) {
            $player_link = "/mod/player_info.php?name=$url_name";
        } else {
            $player_link = "player_search.php?name=$url_name";
        }

        // echo the row
        echo '<tr>'
            ."<td>$i</td>"
            ."<td><a href='$level_link' style='text-decoration: underline;'>$safe_title</a> 'by' <a href='$info_link' style='color: #$group_color; text-decoration: underline;'>$safe_name</a></td>"
            ."<td>$play_count</td>"
            .'</tr>';
    }

    echo "</table>";
    output_pagination($start, $count);
    echo "</center>";
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage(), ENT_QUOTES);
    echo "Error: $error";
} finally {
    output_footer();
}
