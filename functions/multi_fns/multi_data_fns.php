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
function userify($player, $name, $power = null)
{
    global $group_colors;
    if (isset($player) && isset($player->group) && is_null($power)) {
        $color = $group_colors[$player->group];
        $link = "event:user`" . $player->group . '`' . $name;
        $url = urlify($link, $name, "#$color");
        return $url;
    } elseif (!is_null($power)) {
        $color = $group_colors[$power];
        $link = "event:user`" . $power . '`' . $name;
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
