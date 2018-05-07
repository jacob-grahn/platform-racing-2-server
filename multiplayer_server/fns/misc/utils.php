<?php

// get dat dar login id
function get_login_id()
{
    static $cur_login_id = 0;
    $cur_login_id++;
    return $cur_login_id;
}


// takes an id and returns a player
function id_to_player($id, $throw_exception = true)
{
    global $player_array;
    $player = @$player_array[$id];
    if (!isset($player) && $throw_exception === true) {
        throw new Exception('Could not find a player with that ID.');
    }
    return $player;
}


// takes a name and returns a player
function name_to_player($name)
{
    global $player_array;
    $return_player = null;
    foreach ($player_array as $player) {
        if (strtolower($player->name) == strtolower($name)) {
            $return_player = $player;
            break;
        }
    }
    return $return_player;
}


// get an existing chat room, or make a new one
function get_chat_room($chat_room_name)
{
    global $chat_room_array;
    if (isset($chat_room_array[$chat_room_name])) {
        return $chat_room_array[$chat_room_name];
    } else {
        $chat_room = new \pr2\multi\ChatRoom($chat_room_name);
        return $chat_room;
    }
}


// accept bans from other servers
function apply_bans($bans)
{
    global $player_array;

    foreach ($bans as $ban) {
        foreach ($player_array as $player) {
            if ($player->ip == $ban->banned_ip || $player->user_id == $ban->banned_user_id) {
                $player->remove();
            }
        }
    }
}


// send new pm notifications out
function pm_notify($pms)
{
    global $player_array;

    foreach ($pms as $pm) {
        if (isset($player_array[ $pm->to_user_id ])) {
            $player = $player_array[ $pm->to_user_id ];
            $player->write('pmNotify`' . $pm->message_id);
        }
    }
}


// place the artifact
function place_artifact($artifact)
{
    output("place_artifact: " . json_encode($artifact));
    \pr2\multi\Artifact::$level_id = $artifact->level_id;
    \pr2\multi\Artifact::$x = $artifact->x;
    \pr2\multi\Artifact::$y = $artifact->y;
    \pr2\multi\Artifact::$updated_time = strtotime($artifact->updated_time);
    \pr2\multi\Artifact::$first_finder = $artifact->first_finder;
}


// get the plays we've been holding
function drain_plays()
{
    global $play_count_array;
    $cup = array();

    foreach ($play_count_array as $course => $plays) {
        $cup[ $course ] = $plays;
    }

    $play_count_array = array();

    return $cup;
}


// get server population
function get_population()
{
    global $player_array;
    $pop = count($player_array);
    return $pop;
}


// get server status
function get_status()
{
    global $player_array;
    global $max_players;
    $status = 'open';
    if (count($player_array) >= $max_players) {
        $status = 'full';
    }
    return $status;
}


// graceful shutdown
function shutdown_server()
{
    global $player_array;
    output('Disconnecting all players...');
    foreach ($player_array as $player) {
        $player->write('message`The server is restarting, hold on a sec...');
        $player->remove();
    }
    output('All players disconnected. Shutting down...');
    sleep(1);
    output('The shutdown was successful.');
    exit();
}


// send to all online players
function sendToAll_players($str)
{
    global $player_array;
    foreach ($player_array as $player) {
        $player->write($str);
    }
}


// send to all members of a guild
function send_to_guild($guild_id, $str)
{
    global $player_array;
    foreach ($player_array as $player) {
        if ($player->guild_id == $guild_id) {
            $player->write($str);
        }
    }
}


// vault: start a perk
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


// vault: assign a part bought for a guild
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


// limit the amount of times an action can be performed in a certain time period
function rate_limit($key, $interval, $max, $display_error = false, $player = null, $error = 'Slow down a bit, yo.')
{
    $unit = round(time() / $interval);
    $key .= '-'.$unit;
    $count = 0;
    if (apcu_exists($key)) {
        $count = apcu_fetch($key);
        if ($count >= $max) {
            output("$key triggered rate limit");
            if ($display_error === true && $player != null) {
                $player->write("message`Error: $error");
            }
        }
    }
    $count++;
    apcu_store($key, $count, $interval);
    return( $count );
}


// handy output function; never leave home without it!
function output($str)
{
    echo "* $str\n";
}
