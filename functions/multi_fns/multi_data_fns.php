<?php


// sort the chat rooms by how many users are in them
function sort_chat_room_array($a, $b)
{
    if (count($a->player_array) === count($b->player_array)) {
        return 0;
    }
    return count($a->player_array) > count($b->player_array) ? -1 : 1;
}


// determine a user's display info
function userify($player, $name, $power = null, $mod_power = null)
{
    global $group_colors, $mod_colors;
    if (isset($player) && isset($player->group) && is_null($power)) {
        $mod_power = $player->modPower();
        $color = $mod_power > -1 ? $mod_colors[$mod_power] : $group_colors[$player->group];
        $link = "event:user`" . $player->groupStr() . '`' . $name;
        $url = urlify($link, $name, "#$color");
        return $url;
    } elseif (!is_null($power)) {
        $mod_power = !is_null($mod_power) && $mod_power >= 0 && $mod_power <= 2 ? $mod_power : null;
        $color = !is_null($mod_power) ? $mod_colors[$mod_power] : $group_colors[$power];
        $group_str = $power . (!is_null($mod_power) ? ",$mod_power" : '');
        $link = "event:user`" . $group_str . '`' . $name;
        $url = urlify($link, $name, "#$color");
        return $url;
    } else {
        return htmlspecialchars($name, ENT_QUOTES);
    }
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
    return $num;
}
