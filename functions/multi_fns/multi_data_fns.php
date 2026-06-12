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
function userify($player, $name)
{
    if (!isset($player) || $player === false) {
        output('WARNING: userify invoked with a null $player object. Performing database lookup for missing data.');
        $player = db_op('user_select_by_name', array($name, true));
    }

    // get group
    $group = get_group_info($player);

    // return to user
    return urlify("event:user`$group->str`$name", $name, "#$group->color");
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
