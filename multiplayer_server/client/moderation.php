<?php

// call pro/demotion functions
require_once __DIR__ . '/../fns/promote_to_moderator.php';
require_once __DIR__ . '/../fns/demod.php';
require_once __DIR__ . '/../../http_server/queries/staff/actions/mod_action_insert.php';


//--- kick a player -------------------------------------------------------------
function client_kick($socket, $data)
{
    global $pdo, $guild_id, $server_name;
    $name = $data;

    // get players
    $kicked = name_to_player($name);
    $mod = $socket->get_player();

    // safety first
    $safe_kname = htmlspecialchars($name);
    $safe_mname = htmlspecialchars($mod->name);

    // if the player actually has the power to do what they're trying to do, then do it
    if ($mod->group >= 2 && ($kicked->group < 2 || ($mod->server_owner == true && $kicked != $mod))) {
        LocalBans::add($name);

        if (isset($kicked)) {
            $kicked->remove();
            $mod->write("message`$safe_kname has been kicked from this server for 30 minutes.");

            // let people know that the player kicked someone
            if (isset($mod->chat_room)) {
                $mod->chat_room->send_chat("systemChat`$safe_mname has kicked $safe_kname from this server for 30 minutes.");
            }

            // log the action if it's on a public server
            if ($guild_id == 0) {
                $mod_name = $mod->name;
                $mod_ip = $mod->ip;
                $mod_id = $mod->user_id;
                mod_action_insert($pdo, $mod_id, "$mod_name kicked $name from $server_name from $mod_ip.", $mod_id, $mod_ip);
            }
        } else {
            $mod->write("message`Error: Could not find a user with the name \"$safe_kname\" on this server.");
        }
    } // if the kicker is the server owner, tell them they're a silly goose
    elseif ($mod->server_owner == true && $kicked == $mod) {
        $mod->write("message`Error: You can't kick yourself out of your own server, silly!");
    } // if they don't have the power to do that, tell them
    else {
        $mod->write("message`Error: You lack the power to kick $safe_kname.");
    }
}


//--- warn a player -------------------------------------------------------------
function client_warn($socket, $data)
{
    list($name, $num) = explode("`", $data);

    // get player info
    $warned = name_to_player($name);
    $mod = $socket->get_player();

    // safety first
    $safe_mname = htmlspecialchars($mod->name);
    $safe_wname = htmlspecialchars($name);

    $w_str = '';
    $time = 0;

    switch ($num) {
        case 1:
            $w_str = 'warning';
            $time = 15;
            break;
        case 2:
            $w_str = 'warnings';
            $time = 30;
            break;
        case 3:
            $w_str = 'warnings';
            $time = 60;
            break;
        default:
            $mod->write('message`Error: Invalid warning number.');
            break;
    }

    // if they're a mod, and the user is on this server, warn the user
    if ($mod->group >= 2 && isset($warned) && ($warned->group < 2 || $mod->server_owner == true)) {
        $warned->chat_ban = time() + $time;
    } // if they're a mod but the user isn't online, tell them
    elseif ($mod->group >= 2 && !isset($warned)) {
        $mod->write("message`Error: Could not find a user with the name \"$safe_wname\" on this server.");
    } // if they aren't a mod, tell them
    else {
        $mod->write("message`Error: You lack the power to warn $safe_wname.");
    }

    // tell the world
    if (isset($mod->chat_room) && $mod->group >= 2) {
        $mod->chat_room->send_chat("systemChat`$safe_mname has given $safe_wname $num $w_str. They have been banned from the chat for $time seconds.");
    }
}




//--- ban a player -------------------------------------------------------
function client_ban($socket, $data)
{
    list($banned_name, $seconds, $reason) = explode("`", $data);

    // get player info
    $mod = $socket->get_player();
    $banned = name_to_player($banned_name);

    // safety first
    $safe_mname = htmlspecialchars($mod->name);
    $safe_bname = htmlspecialchars($banned_name);
    $safe_reason = htmlspecialchars($reason);

    // set a variable that uses seconds to make friendly times
    switch ($seconds) {
        case 60:
            $disp_time = '1 minute';
            break;
        case 3600:
            $disp_time = '1 hour';
            break;
        case 86400:
            $disp_time = '1 day';
            break;
        case 604800:
            $disp_time = '1 week';
            break;
        case 2419200:
            $disp_time = '1 month';
            break;
        case 29030400:
            $disp_time = '1 year';
            break;
     // if all else fails, echo the seconds
        default:
            $disp_time = $seconds.' seconds';
            break;
    }

    // instead of overwriting the $reason variable, set a new one
    $disp_reason = "Reason: $safe_reason";
    if ($reason == '') {
        $disp_reason = 'There was no reason was given';
    }

    // tell the world
    if ($mod->group >= 2 && isset($banned)) {
        if (isset($mod->chat_room)) {
            $mod->chat_room->send_chat("systemChat`$safe_mname has banned $safe_bname for $disp_time. $disp_reason. This ban has been recorded at https://pr2hub.com/bans.");
        }
        if (isset($banned) && $banned->group < 2) {
            $banned->remove();
        }
    }
}



//--- promote a player to a moderator -------------------------------------
function client_promote_to_moderator($socket, $data)
{
    list($name, $type) = explode("`", $data);

    // get player info
    $admin = $socket->get_player();
    $promoted = name_to_player($name);

    // safety first
    $safe_aname = htmlspecialchars($admin->name);
    $safe_pname = htmlspecialchars($name);

    // if they're an admin and not a server owner, continue with the promotion (1st line of defense)
    if ($admin->group >= 3 && $admin->server_owner == false) {
        $result = promote_to_moderator($name, $type, $admin, $promoted);

        switch ($type) {
            case 'temporary':
                $reign_time = 'hours';
                break;
            case 'trial':
                $reign_time = 'days';
                break;
            case 'permanent':
                $reign_time = '1000 years';
                break;
        }

        if (isset($admin->chat_room) && (isset($promoted) || $type != 'temporary') && $result == true) {
            $admin->chat_room->send_chat("systemChat`$safe_aname has promoted $safe_pname to a $type moderator! May they reign in $reign_time of peace and prosperity! Make sure you read the moderator guidelines at https://jiggmin2.com/forums/showthread.php?tid=12", $admin->user_id);
        }
    } // if they're not an admin, tell them
    else {
        $admin->write("message`Error: You lack the power to promote $safe_pname to a $type moderator.");
    }
}


//-- demote a moderator ------------------------------------------------------------------
function client_demote_moderator($socket, $name)
{
    // get player info
    $admin = $socket->get_player();
    $demoted = name_to_player($name);

    if ($admin->group == 3 && $admin->server_owner == false) {
        demote_mod($name, $admin, $demoted);
    }
}
