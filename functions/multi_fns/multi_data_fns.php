<?php


// sort the chat rooms by how many users are in them
function sort_chat_room_array($a, $b)
{
    if (count($a->player_array) === count($b->player_array)) {
        return 0;
    }
    return count($a->player_array) > count($b->player_array) ? -1 : 1;
}


// gets the group string from a player object (either online or from the db)
function group_str($player)
{
    $group = (int) (isset($player->group) ? $player->group : $player->power);
    if ($group === 2) {
        $mod_power = mod_power($player);
        return "$group,$mod_power";
    }
    return (string) $group;
}


// gets the mod power from a player object (either online or from the db)
function mod_power($player)
{
    $group = (int) (isset($player->group) ? $player->group : $player->power);
    $trial = (bool) (int) $player->trial_mod;
    return $group === 2 ? ($trial ? 1 : 2) : -1;
}


// determine a user's display info
function userify($player, $name, $power = null, $mod_power = null)
{
    global $group_colors, $mod_colors;

    if (isset($player) && isset($player->group) && is_null($power)) {
        $group_str = group_str($player);
        $mod_power = mod_power($player);
        $color = $mod_power > -1 ? $mod_colors[$mod_power] : $group_colors[$player->group];
    } elseif (!is_null($power)) { // explicitly define group/mod power
        $mod_power = !is_null($mod_power) && $mod_power >= 0 && $mod_power <= 2 ? $mod_power : null;
        $color = !is_null($mod_power) ? $mod_colors[$mod_power] : $group_colors[$power];
        $group_str = $power . (!is_null($mod_power) ? ",$mod_power" : '');
    } else {
        return htmlspecialchars($name, ENT_QUOTES);
    }

    // return to user
    return urlify("event:user`$group_str`$name", $name, "#$color");
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
