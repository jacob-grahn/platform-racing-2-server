<?php


//-----------------------------------------------------------------
function start_perk($slug, $user_id, $guild_id)
{
    output("start_perk - slug: $slug, user_id: $user_id, guild_id: $guild_id");
    $seconds_duration = 3700;
    if ($slug == \pr2\multi\Perks::GUILD_FRED) {
        assign_guild_part('body', 29, $user_id, $guild_id, $seconds_duration);
    } elseif ($slug == \pr2\multi\Perks::GUILD_GHOST) {
        assign_guild_part('head', 31, $user_id, $guild_id, $seconds_duration);
        assign_guild_part('body', 30, $user_id, $guild_id, $seconds_duration);
        assign_guild_part('feet', 27, $user_id, $guild_id, $seconds_duration);
    } elseif ($slug == \pr2\multi\Perks::GUILD_ARTIFACT) {
        assign_guild_part('hat', 14, $user_id, $guild_id, $seconds_duration);
        assign_guild_part('eHat', 14, $user_id, $guild_id, $seconds_duration);
    } elseif ($slug == \pr2\multi\Perks::HAPPY_HOUR) {
        \pr2\multi\HappyHour::activate();
    }
}


//--------------------------------------------------------------------------------
function assign_guild_part($type, $part_id, $user_id, $guild_id, $seconds_duration)
{
    global $player_array;

    \pr2\multi\TemporaryItems::add($type, $part_id, $user_id, $guild_id, $seconds_duration);

    foreach ($player_array as $player) {
        if ($player->guild_id == $guild_id) {
            // $player->gainPart('e'.ucfirst($type), $part_id);
            $player->setPart($type, $part_id);
            $player->sendCustomizeInfo();
        }
    }
}
