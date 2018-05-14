<?php


// player_info.php, view_priors.php
function create_ban_list($bans)
{
    $str = '<p><ul>';
    foreach ($bans as $row) {
        $ban_date = date("F j, Y, g:i a", $row->time);
        $reason = htmlspecialchars($row->reason);
        $ban_id = $row->ban_id;
        $str .= "<li><a href='/bans/show_record.php?ban_id=$ban_id'>$ban_date</a>: $reason";
    }
    return $str . '</ul></p>';
}


// user select expanded by name
function find_user($pdo, $name)
{
    // get id from name
    $user_id = name_to_id($pdo, $name);

    // get player info from id
    $user = user_select_expanded($pdo, $user_id);

    return $user;
}


// output gwibble option for /mod/player_info.php vs player_search.php
function output_search($name = '', $gwibble = true)
{
    // safety first
    $safe_name = htmlspecialchars($name);

    // gwibble output
    if ($gwibble === true) {
        echo '<center>'
            .'<font face="Gwibble" class="gwibble">-- Player Search --</font>'
            .'<br><br>';
    }

    // output search
    echo '<form method="get">'
        ."Username: <input type='text' name='name' value='$safe_name'>"
        .'<input type="submit" value="Search">'
        .'</form>';
}


// player_search.php
function output_page($pdo, $user)
{
    // sanity check: is the used tokens value set?
    if (!isset($user->used_tokens)) {
        $user->used_tokens = 0;
    }

    // group arrays
    $group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];
    $group_names = ['Guest', 'Member', 'Moderator', 'Admin'];

    // make some variables
    $user_name = $user->name; // name
    $group = (int) $user->power; // group
    $group_color = $group_colors[$group];
    $group_name = $group_names[$group];
    $status = $user->status; // status
    $guild_id = (int) $user->guild; // guild id
    $rank = (int) ($user->rank + $user->used_tokens); // rank
    $hats = (int) (count(explode(',', $user->hat_array)) - 1); // hats
    $login_date = date('j/M/Y', $user->time); // active
    $register_date = date('j/M/Y', $user->register_time); // joined

    // aoh check
    if ($register_date == '1/Jan/1970') {
        $register_date = "Age of Heroes";
    }

    // guild id to name
    if ($guild_id !== 0) {
        $guild = guild_select($pdo, $guild_id);
        $guild_name = $guild->guild_name;
    } else {
        $guild_name = "<i>none</i>";
    }

    // group html change if staff
    if ($group >= 2) {
        $group_name = "<a href='https://pr2hub.com/staff.php' style='color: #000000; font-weight: bold'>"
                        ."$group_name</a>";
    }

    // safety first
    $safe_name = htmlspecialchars($user_name);
    $safe_status = htmlspecialchars($status);
    if ($guild_name == '<i>none</i>') {
        $safe_guild = $guild_name;
    } else {
        $safe_guild = htmlspecialchars($guild_name);
    }

    // --- Start the Page --- \\

    echo '<br><br>'
        ."-- <font style='color: #$group_color; text-decoration: underline; font-weight: bold'>$safe_name</font> --<br>"
        ."<i>$safe_status</i><br><br>"
        ."Group: $group_name<br>"
        ."Guild: $safe_guild<br>"
        ."Rank: $rank<br>"
        ."Hats: $hats<br>"
        ."Joined: $register_date<br>"
        ."Active: $login_date</center>";
}
