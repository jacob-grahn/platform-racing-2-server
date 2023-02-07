<?php


// player_info.php, view_priors.php
function create_ban_list($bans)
{
    $str = '<p><ul>';
    foreach ($bans as $ban) {
        $ban_date = date("F j, Y, g:i a", $ban->time);
        $reason = htmlspecialchars($ban->reason, ENT_QUOTES);
        $ban_id = (int) $ban->ban_id;
        $lifted_reason = (int) $ban->lifted === 1 ? htmlspecialchars($ban->lifted_reason, ENT_QUOTES) : '';
        $lifted = (int) $ban->lifted === 1 ? " | <b>LIFTED</b> ($lifted_reason)" : '';
        $scope = $ban->scope === 'g' ? '' : ' (social)';
        $str .= "<li><a href='/bans/show_record.php?ban_id=$ban_id'>$ban_date</a><u><i>$scope</i></u>: $reason$lifted";
    }
    return $str . '</ul></p>';
}


// user select expanded by name
function find_user($pdo, $name)
{
    // get id from name
    $user_id = name_to_id($pdo, $name, true);
    if ($user_id === false) {
        return false;
    }

    // get player info from id
    $user = user_select_expanded($pdo, $user_id);

    return $user;
}


// output gwibble option for /mod/player_info.php vs player_search.php
function output_search($name = '', $gwibble = true)
{
    // safety first
    $safe_name = htmlspecialchars($name, ENT_QUOTES);

    // gwibble output
    if ($gwibble === true) {
        echo '<center>'
            .'<font face="Gwibble" class="gwibble">-- Player Search --</font>'
            .'<br><br>';
    }

    // output search
    echo '<form method="get">'
        ."Username: <input type='text' name='name' value='$safe_name'> "
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

    // make some variables
    $group = get_group_info($user);
    $name_color = $group->color;
    $guild_id = (int) $user->guild; // guild id
    $rank = $user->rank + $user->used_tokens; // rank
    $hats = count(explode(',', $user->hat_array)) - 1; // hats
    $login_date = date('j/M/Y', $user->time); // active
    $register_date = date('j/M/Y', $user->register_time); // joined

    // aoh check
    $register_date = $register_date === '1/Jan/1970' ? 'Age of Heroes' : $register_date;

    // guild id to name
    if ($guild_id !== 0) {
        $guild = guild_select($pdo, $guild_id);
        $guild_name = $guild->guild_name;
    } else {
        $guild_name = "<i>none</i>";
    }

    // group html change if staff
    $group_name = $group->name;
    if ($group->num >= 2) {
        $group_name = "<a href='/staff.php' style='color: #000000; font-weight: bold'>$group_name</a>";
    }

    // safety first
    $name = htmlspecialchars($user->name, ENT_QUOTES);
    $status = htmlspecialchars($user->status, ENT_QUOTES);
    if ($guild_name == '<i>none</i>') {
        $safe_guild = $guild_name;
    } else {
        $url_guild_name = urlencode($guild_name);
        $html_guild_name = htmlspecialchars($guild_name, ENT_QUOTES);
        $safe_guild = "<a href='/guild_search.php?name=$url_guild_name'>$html_guild_name</a>";
    }

    // icons
    $st = 'https://jiggmin2.com/forums/showthread.php?tid';
    $verified = $user->verified ? "<a href='$st=4227'><img src='/img/verified.svg' width='15'></a>" : '';
    $hof = $user->hof ? "<a href='$st=4226'><img src='/img/hof.svg' width='15'></a>" : '';


    // --- Start the Page --- \\

    echo '<br><br>'
        ."-- <font style='color: #$group->color; text-decoration: underline; font-weight: bold'>$name</font> --<br>"
        ."<i>$status</i><br>"
        .(empty($verified) && empty($hof) ? '<br>' : "$verified $hof<br>")
        ."Group: $group_name<br>"
        ."Guild: $safe_guild<br>"
        ."Rank: $rank<br>"
        ."Hats: $hats<br>"
        ."Joined: $register_date<br>"
        ."Active: $login_date</center>";
}
