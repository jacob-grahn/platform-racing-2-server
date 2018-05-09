<?php


// sort the chat rooms by how many users are in them
function sort_chat_room_array($a, $b)
{
    if (count($a->player_array) == count($b->player_array)) {
        return 0;
    }
    return (count($a->player_array) > count($b->player_array)) ? -1 : 1;
}


// build a url
function urlify($link, $disp, $color = '#0000FF', $bt_replace = true)
{
    $link = htmlspecialchars($link);
    $disp = htmlspecialchars($disp);
    
    // replace backticks with html code to prevent errors
    if ($bt_replace === true) {
        $link = str_replace('`', '&#96;', $link);
        $disp = str_replace('`', '&#96;', $disp);
    }
    
    // return url
    return "<a href='$link' target='_blank'><u><font color='$color'>$disp</font></u></a>";
}


// determine a group color from a group value
function group_color($group)
{
    $colors = ['676666', '047B7B', '1C369F', '870A6F']; // colors
    return $colors[$group];
}


// determine a user's display info
function userify($player, $name, $power = null)
{
    $name = htmlspecialchars($name);

    if (isset($player) && isset($player->group) && is_null($power)) {
        $color = group_color($player->group);
        $link = "event:user`" . $player->group . '`' . $name;
        $url = urlify($link, $name, "#$color");
        return $url;
    } elseif (is_null($player) && !is_null($power)) {
        $color = group_color($power);
        $link = "event:user`" . $power . '`' . $name;
        $url = urlify($link, $name, "#$color");
        return $url;
    } else {
        return $name;
    }
}


// tests to see if a string contains obscene words
function is_obscene($str)
{
    $str = strtolower($str);
    $bad_array = array(
        'fuck',
        'shit',
        'nigger',
        'nigga',
        'whore',
        'bitch',
        'slut',
        'cunt',
        'cock',
        'dick',
        'penis',
        'damn',
        'spic'
    );
    $obscene = false;
    foreach ($bad_array as $bad) {
        if (strpos($str, $bad) !== false) {
            $obscene = true;
            break;
        }
    }
    return $obscene;
}


// make a readable time format from seconds
function format_duration($seconds)
{
    if ($seconds < 60) {
        $time_left = "$seconds second";
        if ($seconds != 1) {
            $time_left .= 's';
        }
    } elseif ($seconds < 60*60) {
        $minutes = round($seconds/60, 0);
        $time_left = "$minutes minute";
        if ($minutes != 1) {
            $time_left .= 's';
        }
    } elseif ($seconds < 60*60*24) {
        $hours = round($seconds/60/60, 0);
        $time_left = "$hours hour";
        if ($hours != 1) {
            $time_left .= 's';
        }
    } elseif ($seconds < 60*60*24*30) {
        $days = round($seconds/60/60/24, 0);
        $time_left = "$days day";
        if ($days != 1) {
            $time_left .= 's';
        }
    } elseif ($seconds < 60*60*24*30*12) {
        $months = round($seconds/60/60/24/30, 0);
        $time_left = "$months month";
        if ($months != 1) {
            $time_left .= 's';
        }
    } else {
        $years = round($seconds/60/60/24/30/12, 0);
        $time_left = "$years year";
        if ($years != 1) {
            $time_left .= 's';
        }
    }
    return $time_left;
}


// simple number limit function
function limit($num, $min, $max)
{
    if ($num < $min) {
        $num = $min;
    }
    if ($num > $max) {
        $num = $max;
    }
    return( $num );
}
