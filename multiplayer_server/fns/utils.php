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


// check if the user is banned
function query_if_banned($pdo, $user_id, $ip)
{
    $ban = false;
    if (isset($user_id) && $user_id != 0) {
        $ban = ban_select_active_by_user_id($pdo, $user_id);
    }
    if (!$ban && isset($ip)) {
        $ban = ban_select_active_by_ip($pdo, $ip);
    }
    return $ban;
}


// get ban priors (for lazy mods)
function get_priors($pdo, $mod, $name)
{
    $safe_name = htmlspecialchars($name, ENT_QUOTES);

    // sanity: make sure they're online and a staff member
    if (!isset($mod) || $mod->group < 2 || $mod->temp_mod == true || $mod->server_owner == true) {
        $mod->write("message`Error: You lack the power to view priors for $safe_name.");
        return false;
    }

    // get player info for mod
    $mod_power = (int) user_select_power($pdo, $mod->user_id, true);
    if ($mod_power < 2 || $mod_power === false) {
        $mod->write("message`Error: You lack the power to view priors for $safe_name.");
        return false;
    }

    // get user info
    $user = user_select_by_name($pdo, $name, true);
    $user_id = (int) $user->user_id;
    if ($user_id === 0) {
        $mod->write("message`Error: Could not find a user with that name.");
        return false;
    }

    // make user vars
    $name = $user->name;
    $safe_name = htmlspecialchars($name, ENT_QUOTES);
    $ip = $user->ip;
    $power = (int) $user->power;

    // initialize return string var
    $url_name = htmlspecialchars(urlencode($name), ENT_QUOTES);
    $user_link = urlify("https://pr2hub.com/mod/player_info.php?name=$url_name", $safe_name, '#'.group_color($power));
    $str = "<b>Ban Data for $user_link</b><br><br>";

    // check if the user is currently banned
    $str .= "Currently Banned: ";
    $is_banned = 'No';
    $row = query_if_banned($pdo, $user_id, $ip);
    if ($row !== false) {
        $ban_id = $row->ban_id;
        $reason = htmlspecialchars($row->reason, ENT_QUOTES);
        $ban_end_date = date("F j, Y, g:i a", $row->expire_time);
        if ($row->ip_ban == 1 && $row->account_ban == 1 && $row->banned_name == $name) {
            $ban_type = 'account and IP are';
        } elseif ($row->ip_ban == 1) {
            $ban_type = 'IP is';
        } elseif ($row->account_ban == 1) {
            $ban_type = 'account is';
        }
        $ban_link = urlify("https://pr2hub.com/bans/show_record.php?ban_id=$ban_id", 'Yes');
        $str .= "$ban_link, this $ban_type banned until $ban_end_date. Reason: $reason<br><br>";
    } else {
        $str .= $is_banned . '<br><br>';
    }

    // get account bans of target user
    $account_bans = bans_select_by_user_id($pdo, $user_id);
    $account_ban_count = (int) count($account_bans);
    $str .= "This account has been banned $account_ban_count times.<br><br>";

    // make account bans list
    if ($account_ban_count !== 0) {
        $str .= '<ul>';
        foreach ($account_bans as $ban) {
            $str .= '<li>';
            $ban_id = (int) $ban->ban_id;
            $date = date("M j, Y g:i A", $ban->time);
            $mod_name = htmlspecialchars($ban->mod_name, ENT_QUOTES);
            $url_mod_name = htmlspecialchars(urlencode($ban->mod_name), ENT_QUOTES);
            $banned_name = htmlspecialchars($ban->banned_name, ENT_QUOTES);
            $banned_ip = htmlspecialchars(urlencode($ban->banned_ip), ENT_QUOTES);
            $duration = format_duration($ban->expire_time - $ban->time);
            $reason = htmlspecialchars($ban->reason, ENT_QUOTES);
            $lifted = (bool) $ban->lifted;
            $acc_ban = (bool) $ban->account_ban;
            $ip_ban = (bool) $ban->ip_ban;

            // var init
            $nameip_str = '';
            $lifted_str = '';
            if (strlen(trim($reason)) === 0) {
                $reason = 'No reason was given.';
            }

            // make name/ip str
            if ($acc_ban === true) {
                $nameip_str .= $banned_name;
            }
            if ($ip_ban === true) {
                $nameip_str .= ' [' . $banned_ip . ']';
            }
            $nameip_str = trim($nameip_str);

            // check if lifted
            if ($lifted === true) {
                $lifted_reason = htmlspecialchars($ban->lifted_reason, ENT_QUOTES);
                $lifted_by = htmlspecialchars($ban->lifted_by, ENT_QUOTES);
                $lifted_date = '';
                $at_loc = strrpos($lifted_reason, '@');
                if ($at_loc !== false) {
                    $lifted_datetime = date('M j, Y \a\t g:i A', strtotime(trim(substr($lifted_reason, $at_loc + 1))));
                    $lifted_reason = trim(substr($lifted_reason, 0, $at_loc));
                }
                $lifted_str = "<b>^ LIFTED</b> on $lifted_datetime by $lifted_by. Reason: $lifted_reason";
            }

            // craft ban string
            $date_url = urlify("https://pr2hub.com/bans/show_record.php?ban_id=$ban_id", $date);
            $ban_str = "$date_url: $mod_name banned $nameip_str for $duration. Reason: $reason";

            // add to the output string
            if ($lifted === true) {
                $str .= $ban_str . '<br>' . $lifted_str;
            } else {
                $str .= $ban_str;
            }

            // move to the next ban
            $str .= '</li>';
        }

        // end this group of bans
        $str .= '</ul><br>';
    }

    // get IP bans of target user's IP
    $ip_bans = bans_select_by_ip($pdo, $ip);
    $ip_ban_count = (int) count($ip_bans);
    $ip_link = urlify("https://pr2hub.com/mod/ip_info.php?ip=$ip", $ip);
    $str .= "This IP ($ip_link) has been banned $ip_ban_count times.<br><br>";

    // make account bans list
    if ($ip_ban_count !== 0) {
        $str .= '<ul>';
        foreach ($ip_bans as $ban) {
            $str .= '<li>';
            $ban_id = (int) $ban->ban_id;
            $date = date("M j, Y g:i A", $ban->time);
            $mod_name = htmlspecialchars($ban->mod_name, ENT_QUOTES);
            $url_mod_name = htmlspecialchars(urlencode($ban->mod_name), ENT_QUOTES);
            $banned_name = htmlspecialchars($ban->banned_name, ENT_QUOTES);
            $banned_ip = htmlspecialchars(urlencode($ban->banned_ip), ENT_QUOTES);
            $duration = format_duration($ban->expire_time - $ban->time);
            $reason = htmlspecialchars($ban->reason, ENT_QUOTES);
            $lifted = (bool) $ban->lifted;
            $acc_ban = (bool) $ban->account_ban;
            $ip_ban = (bool) $ban->ip_ban;

            // var init
            $nameip_str = '';
            $lifted_str = '';
            if (strlen(trim($reason)) === 0) {
                $reason = 'No reason was given.';
            }

            // make name/ip str
            if ($acc_ban === true) {
                $nameip_str .= $banned_name;
            }
            if ($ip_ban === true) {
                $nameip_str .= ' [' . $banned_ip . ']';
            }
            $nameip_str = trim($nameip_str);

            // check if lifted
            if ($lifted === true) {
                $lifted_reason = htmlspecialchars($ban->lifted_reason, ENT_QUOTES);
                $lifted_by = htmlspecialchars($ban->lifted_by, ENT_QUOTES);
                $lifted_date = '';
                $at_loc = strrpos($lifted_reason, '@');
                if ($at_loc !== false) {
                    $lifted_datetime = date('M j, Y \a\t g:i A', strtotime(trim(substr($lifted_reason, $at_loc + 1))));
                    $lifted_reason = trim(substr($lifted_reason, 0, $at_loc));
                }
                $lifted_str = "<b>^ LIFTED</b> on $lifted_datetime by $lifted_by. Reason: $lifted_reason";
            }

            // craft ban string
            $date_url = urlify("https://pr2hub.com/bans/show_record.php?ban_id=$ban_id", $date);
            $ban_str = "$date_url: $mod_name banned $nameip_str for $duration. Reason: $reason";

            // add to the output string
            if ($lifted === true) {
                $str .= $ban_str . '<br>' . $lifted_str;
            } else {
                $str .= $ban_str;
            }

            // move to the next ban
            $str .= '</li>';
        }

        // end this group of bans
        $str .= '</ul>';
    }

    // tell the mod
    $mod->write("message`$str");
    return true;
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


// graceful shutdown
function shutdown_server($socket = null)
{
    global $player_array, $socket;

    // disconnect everyone
    output('Disconnecting all players...');
    foreach ($player_array as $player) {
        $player->write('message`The server is restarting, hold on a sec...');
        $player->remove();
    }

    // tell the world
    output('All players disconnected. Shutting down...');
    output('The shutdown was successful.');
    if (!is_null($socket)) {
        $socket->write('The shutdown was successful.');
    }

    // socketDaemon shutdown
    sleep(1);
    die();
}


// handy output function; never leave home without it!
function output($str)
{
    echo "* $str\n";
}
