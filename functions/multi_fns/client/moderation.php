<?php


// kick a player
function client_kick($socket, $data)
{
    global $pdo, $guild_id, $guild_owner, $server_name;
    $name = $data;

    // get players
    $kicked = name_to_player($name);
    $mod = $socket->getPlayer();

    // safety first
    $safe_kname = htmlspecialchars($name, ENT_QUOTES);

    // if they're a mod and not the person being kicked, proceed
    if ($mod->group >= 2 && $kicked != $mod) {
        // check if online and get user data if not
        $kicked_online = true;
        if (!isset($kicked)) {
            $kicked_online = false;
            $kicked = user_select_by_name($pdo, $name, true);
            if ($kicked === false) {
                $mod->write("message`Error: Could not find a user with the name \"$safe_kname\".");
                return false;
            } else {
                $kicked->group = $kicked->power;
                $kicked->server_owner = false;
                if ($kicked->user_id == $guild_owner) {
                    $kicked->server_owner = true;
                    $kicked->group = 3;
                }
            }
        }

        // remove existing kicks, then kick
        if (\pr2\multi\ServerBans::isBanned($name) === true) {
            \pr2\multi\ServerBans::remove($name);
        }

        // kick the user
        if (($kicked->group < 2 || $mod->server_owner === true) && $kicked->server_owner === false) {
            // add server ban
            \pr2\multi\ServerBans::add($name);

            // let people know that the player is kicking someone
            if (isset($mod->chat_room)) {
                $mod_url = userify($mod, $mod->name);
                $kicked_url = userify($kicked, $name);
                $message = "systemChat`$mod_url has kicked $kicked_url from this server for 30 minutes.";
                $mod->chat_room->sendChat($message);
            }

            // disconnect them
            if ($kicked_online === false) {
                $mod->write("message`$safe_kname is not currently on this server, but the kick was applied anyway.");
            } else {
                $kicked->remove();
                $mod->write("message`$safe_kname has been kicked from this server for 30 minutes.");
            }

            // log the action if it's on a public server
            if ($guild_id == 0) {
                $message = "$mod->name kicked $name from $server_name from $mod->ip.";
                mod_action_insert($pdo, $mod->user_id, $message, $mod->user_id, $mod->ip);
            }
        } else {
            $mod->write("message`Error: You lack the power to kick $safe_kname.");
        }
    } elseif ($kicked == $mod) {
        $mod->write("message`Error: You can't kick yourself out of a server, silly!");
    } else {
        $mod->write("message`Error: You lack the power to kick $safe_kname.");
    }
}


// unkick a player
function client_unkick($socket, $data)
{
    global $pdo, $guild_id, $server_name;
    $name = $data;

    // get some info
    $mod = $socket->getPlayer();
    $unkicked_name = htmlspecialchars($name, ENT_QUOTES);

    // if the player actually has the power to do what they're trying to do, then do it
    if (($mod->group >= 2 && $mod->temp_mod === false) || $mod->server_owner === true) {
        if (\pr2\multi\ServerBans::isBanned($name) === true) {
            \pr2\multi\ServerBans::remove($name);

            // unkick them, yo
            $mod->write("message`$unkicked_name has been unkicked! Hooray for second chances!");

            // log the action if it's on a public server
            if ($guild_id == 0) {
                $message = "$mod->name unkicked $name from $server_name from $mod->ip.";
                mod_action_insert($pdo, $mod->user_id, $message, $mod->user_id, $mod->ip);
            }
        } else {
            $mod->write("message`Error: $unkicked_name isn't kicked.");
        }
    } else {
        $mod->write("message`Error: You lack the power to unkick $unkicked_name.");
    }
}


// administer a chat warning
function client_warn($socket, $data)
{
    global $pdo, $guild_owner;
    list($name, $num) = explode("`", $data);

    // get player info
    $warned = name_to_player($name);
    $mod = $socket->getPlayer();

    // safety first
    $num = (int) $num;
    $safe_wname = htmlspecialchars($name, ENT_QUOTES);

    // warning number and duration
    $num = limit($num, 1, 3);
    $w_str = $num !== 1 ? 'warnings' : 'warning';
    $time = $num === 3 ? 120 : $num * 30;
    $time_str = format_duration($time);

    // if they're a mod, and the user is on this server, warn the user
    if ($mod->group >= 2 && $warned != $mod) {
        $warned_online = true;
        if (!isset($warned)) {
            $warned_online = false;
            $warned = user_select_by_name($pdo, $name, true);
            if ($warned === false) {
                $mod->write("message`Error: Could not find a user with the name \"$safe_wname\".");
                return false;
            } else {
                $warned->group = $warned->power;
                $warned->server_owner = false;
                if ($warned->user_id == $guild_owner) {
                    $warned->server_owner = true;
                    $warned->group = 3;
                }
            }
        }

        // remove existing mutes, then mute
        if (\pr2\multi\Mutes::isMuted($name) === true) {
            \pr2\multi\Mutes::remove($name);
        }

        // warn the user if they're not a mod
        if (($warned->group < 2 || $mod->server_owner === true) && $warned->server_owner === false) {
            \pr2\multi\Mutes::add($name, $time);
            if ($warned_online === false) {
                $mod->write("message`$safe_wname is not currently on this server, but the mute was applied anyway.");
            }
        } else {
            $mod->write("message`Error: You lack the power to warn $safe_wname.");
        }
    } elseif ($warned == $mod) {
        $mod->write("message`Error: You can't warn yourself, silly!");
    } else {
        $mod->write("message`Error: You lack the power to warn $safe_wname.");
    }

    // tell the world
    if (isset($mod->chat_room) && $mod->group >= 2) {
        $mod_url = userify($mod, $mod->name);
        $warned_url = userify($warned, $name);

        $msg = "$mod_url has given $warned_url $num $w_str. They have been muted from the chat for $time_str.";
        $mod->chat_room->sendChat("systemChat`$msg");
    }
}


// unmute a player
function client_unmute($socket, $data)
{
    $name = $data;

    // get some info
    $mod = $socket->getPlayer();
    $unmuted_name = htmlspecialchars($name, ENT_QUOTES);

    // if the player actually has the power to do what they're trying to do, then do it
    if (($mod->group >= 2 && $mod->temp_mod === false) || $mod->server_owner === true) {
        if (\pr2\multi\Mutes::isMuted($name) === true) {
            \pr2\multi\Mutes::remove($name);

            // unmute them, yo
            $mod->write("message`$unmuted_name has been unmuted! Hooray for speech!");
        } else {
            $mod->write("message`Error: $unmuted_name isn't muted.");
        }
    } else {
        $mod->write("message`Error: You lack the power to unmute $unmuted_name.");
    }
}


// ban a player
function client_ban($socket, $data)
{
    list($banned_name, $seconds, $reason) = explode("`", $data);

    // get player info
    $mod = $socket->getPlayer();
    $banned = name_to_player($banned_name);

    // reason
    $safe_reason = htmlspecialchars($reason, ENT_QUOTES);
    $disp_reason = $reason === '' ? 'There was no reason given' : "Reason: $safe_reason";

    // make friendly time
    $disp_time = format_duration($seconds);

    // tell the world
    if ($mod->group >= 2 && isset($banned)) {
        $mod_url = userify($mod, $mod->name);
        $banned_url = userify($banned, $banned_name);

        if (isset($mod->chat_room)) {
            $log = urlify('https://pr2hub.com/bans', 'the ban log');
            $msg = "$mod_url has banned $banned_url for $disp_time. $disp_reason. This ban has been recorded on $log.";
            $mod->chat_room->sendChat("systemChat`$msg");
        }
        if (isset($banned) && ($banned->group < 2 || $banned->temp_mod === true)) {
            $banned->remove();
        }
    }
}


// promote a player to a moderator
function client_promote_to_moderator($socket, $data)
{
    list($name, $type) = explode("`", $data);

    // get player info
    $admin = $socket->getPlayer();
    $promoted = name_to_player($name);

    // safety first
    $safe_pname = htmlspecialchars($name, ENT_QUOTES);

    // if they're an admin and not a server owner, continue with the promotion (1st line of defense)
    if ($admin->group >= 3 && $admin->server_owner === false) {
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

        if (isset($admin->chat_room) && (isset($promoted) || $type !== 'temporary') && $result === true) {
            $admin_url = userify($admin, $admin->name);
            $promoted_url = userify($promoted, $name, 2);
            $mod_guide = urlify('https://jiggmin2.com/forums/showthread.php?tid=12', 'moderator guidelines');

            $msg = "$admin_url has promoted $promoted_url to a $type moderator! "
                ."May they reign in $reign_time of peace and prosperity! Make sure you read the $mod_guide.";
            $admin->chat_room->sendChat("systemChat`$msg", $admin->user_id);
        }
    } // if they're not an admin, tell them
    else {
        $admin->write("message`Error: You lack the power to promote $safe_pname to a $type moderator.");
    }
}


// demote a moderator
function client_demote_moderator($socket, $name)
{
    // get player info
    $admin = $socket->getPlayer();
    $demoted = name_to_player($name);

    if ($admin->group === 3 && $admin->server_owner === false) {
        demote_mod($name, $admin, $demoted);
    }
}
