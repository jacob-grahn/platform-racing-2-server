<?php


// sort the chat rooms by how many users are in them
function sort_chat_room_array($a, $b)
{
    if (count($a->player_array) === count($b->player_array)) {
        return 0;
    }
    return count($a->player_array) > count($b->player_array) ? -1 : 1;
}


function get_mod_power($player)
{
    $online = isset($player->rank);
    $group = (int) (isset($player->group) ? $player->group : $player->power);
    if (isset($group) && $group === 2) {
        if ($online && $player->temp_mod) {
            return 0;
        } elseif ((bool) (int) $player->trial_mod) {
            return 1;
        } else {
            return 2;
        }
    }
    return -1;
}


// determine a user's display info
function userify($player, $name, $power = null, $mod_power = null)
{
    global $group_colors, $mod_colors;
    if (isset($player) && isset($player->group) && is_null($power)) {
        $mod_power = get_mod_power($player);
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
