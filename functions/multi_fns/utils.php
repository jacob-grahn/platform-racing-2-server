<?php


// get the next login id
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
        if (strtolower($player->name) === strtolower($name)) {
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


// accept bans from other servers or PR2 Hub
function apply_bans($bans)
{
    global $player_array;

    foreach ($bans as $ban) {
        foreach ($player_array as $player) {
            if ($player->ip === $ban->ip || (int) $player->user_id === (int) $ban->user_id) {
                if ($ban->expire_time > time() && $ban->lifted == 0) { // active ban
                    if ($ban->scope === 'g') { // remove if game ban
                        $player->remove();
                    } elseif ($player->sban_exp_time < $ban->expire_time) {
                        $player->sban_id = $ban->ban_id;
                        $player->sban_exp_time = $ban->expire_time;
                    }
                } elseif ($ban->lifted == 1 || $ban->expire_time <= time()) { // expire lifted social ban
                    $player->sban_id = $player->sban_exp_time = 0;
                }
            }
        }
    }
}


// remove expired social bans
function socialBansRemoveExpired()
{
    global $player_array;

    $time = time();
    foreach ($player_array as $player) {
        if ($player->sban_exp_time > 0 || $player->sban_id > 0) {
            if ($player->sban_exp_time - $time <= 0) {
                $player->sban_id = $player->sban_exp_time = 0;
            }
        }
    }
}


// send new pm notifications out
function pm_notify($pms)
{
    global $player_array;

    foreach ($pms as $pm) {
        if (isset($player_array[$pm->to_user_id])) {
            $player = $player_array[$pm->to_user_id];
            $player->write('pmNotify`' . $pm->message_id);
        }
    }
}


// place the artifact
function place_artifact($artifact)
{
    \pr2\multi\Artifact::$level_id = (int) $artifact->level_id;
    \pr2\multi\Artifact::$x = (int) $artifact->x;
    \pr2\multi\Artifact::$y = (int) $artifact->y;
    \pr2\multi\Artifact::$rot = (int) $artifact->rot;
    \pr2\multi\Artifact::$set_time = (int) $artifact->set_time;
    \pr2\multi\Artifact::$first_finder = (int) $artifact->first_finder;
    \pr2\multi\Artifact::$bubbles_winner = (int) $artifact->bubbles_winner;
}


// get the plays we've been holding
function drain_plays()
{
    global $play_count_array;

    $cup = array();
    foreach ($play_count_array as $course => $plays) {
        $cup[$course] = $plays;
    }

    $play_count_array = array();

    return $cup;
}


// get server population
function get_population()
{
    global $player_array;
    return count($player_array);
}


// get server status
function get_status()
{
    global $player_array, $max_players;
    return count($player_array) >= $max_players ? 'full' : 'open';
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
        if ((int) $player->guild_id === $guild_id) {
            $player->write($str);
        }
    }
}


// vault: start a perk
function start_perk($slug, $user_id, $guild_id, $expire_time = 0, $start_time = 0)
{
    $seconds_elapsed = !empty($start_time) && time() > $start_time ? time() - $start_time : 0;
    $seconds_duration = $expire_time - ($seconds_elapsed > 0 ? $start_time + $seconds_elapsed : time());
    if ($seconds_duration <= 0) {
        return;
    }

    if ($slug === 'guild_fred') {
        assign_guild_part('body', 29, $user_id, $guild_id, $seconds_duration);
    } elseif ($slug === 'guild_ghost') {
        assign_guild_part('head', 31, $user_id, $guild_id, $seconds_duration);
        assign_guild_part('body', 30, $user_id, $guild_id, $seconds_duration);
        assign_guild_part('feet', 27, $user_id, $guild_id, $seconds_duration);
    } elseif ($slug === 'guild_artifact') {
        assign_guild_part('hat', 14, $user_id, $guild_id, $seconds_duration);
        assign_guild_part('eHat', 14, $user_id, $guild_id, $seconds_duration);
    } elseif ($slug === 'happy_hour') {
        \pr2\multi\HappyHour::activate($seconds_duration);
    }
}


// vault: assign a part bought for a guild
function assign_guild_part($type, $part_id, $user_id, $guild_id, $seconds_duration)
{
    global $player_array;

    \pr2\multi\TemporaryItems::add($type, $part_id, $user_id, $guild_id, $seconds_duration);

    foreach ($player_array as $player) {
        if ($player->guild_id === $guild_id) {
            $player->setPart($type, $part_id);
            $player->sendCustomizeInfo();
        }
    }
}


// get ban priors (for lazy mods)
function get_priors($mod, $name)
{
    global $guild_id;

    $safe_name = htmlspecialchars($name, ENT_QUOTES);

    // sanity: make sure they're online and a staff member
    $not_staff = $guild_id !== 183;
    if (!isset($mod) || $mod->group < 2 || $mod->temp_mod === true || ($mod->server_owner === true && $not_staff)) {
        $mod->write("message`Error: You lack the power to view priors for $safe_name.");
        return false;
    }

    // get player info for mod
    $power = db_op('user_select_power', array($mod->user_id, true));
    if ($power < 2) {
        $mod->write("message`Error: You lack the power to view priors for $safe_name.");
        return false;
    }

    // get user info
    $user = db_op('user_select_by_name', array($name, true));
    $user_id = (int) $user->user_id;
    if ($user_id === 0) {
        $mod->write("message`Error: Could not find a user with that name.");
        return false;
    }

    // make user vars
    $ip = $user->ip;
    $power = (int) $user->power;

    // initialize return string var
    $url_name = htmlspecialchars(urlencode($user->name), ENT_QUOTES);
    $user_group = get_group_info($user);
    $u_link = urlify("https://pr2hub.com/mod/player_info.php?name=$url_name", $user->name, "#$user_group->color");
    $str = "<b>Ban Data for $u_link</b><br><br>";

    // check if the user is currently banned
    $str .= "Currently Banned: ";
    $is_banned = 'No';
    $row = db_op('check_if_banned', array(0, $ip, 'b', false));
    if ($row !== false) {
        $ban_id = $row->ban_id;
        $reason = htmlspecialchars($row->reason, ENT_QUOTES);
        $ban_end_date = date("F j, Y, g:i a", $row->expire_time);
        if ($row->ip_ban == 1 && $row->account_ban == 1 && $row->banned_name == $user->name) {
            $ban_type = 'account and IP are';
        } elseif ($row->ip_ban == 1) {
            $ban_type = 'IP is';
        } elseif ($row->account_ban == 1) {
            $ban_type = 'account is';
        }
        $scope = $row->scope === 'g' ? '' : ' socially';
        $ban_link = urlify("https://pr2hub.com/bans/show_record.php?ban_id=$ban_id", 'Yes');
        $str .= "$ban_link, this $ban_type$scope banned until $ban_end_date. Reason: $reason<br><br>";
    } else {
        $str .= "$is_banned<br><br>";
    }

    // get account bans of target user
    $account_bans = db_op('bans_select_by_user_id', array($user_id));
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
            $banned_name = htmlspecialchars($ban->banned_name, ENT_QUOTES);
            $banned_ip = htmlspecialchars(urlencode($ban->banned_ip), ENT_QUOTES);
            $duration = format_duration($ban->expire_time - $ban->time);
            $reason = htmlspecialchars($ban->reason, ENT_QUOTES);
            $lifted = (bool) $ban->lifted;
            $acc_ban = (bool) $ban->account_ban;
            $ip_ban = (bool) $ban->ip_ban;
            $scope = $ban->scope === 'g' ? '' : ' socially';

            // var init
            $nameip_str = '';
            $lifted_str = '';
            $reason = is_empty($reason) ? 'No reason was given.' : $reason;

            // make name/ip str
            $nameip_str = $acc_ban ? $nameip_str . $banned_name : $nameip_str;
            $nameip_str = $ip_ban && !$mod->trial_mod ? $nameip_str . ' [' . $banned_ip . ']' : $nameip_str;
            $nameip_str = trim($nameip_str);

            // check if lifted
            if ($lifted) {
                $lifted_datetime = date('M j, Y \a\t g:i A', $ban->lifted_time);
                $lifted_by = htmlspecialchars($ban->lifted_by, ENT_QUOTES);
                $lifted_reason = htmlspecialchars($ban->lifted_reason, ENT_QUOTES);
                $lifted_str = "<b>^ LIFTED</b> on $lifted_datetime by $lifted_by. Reason: $lifted_reason";
            }

            // craft ban string
            $date_url = urlify("https://pr2hub.com/bans/show_record.php?ban_id=$ban_id", $date);
            $ban_str = "$date_url: $mod_name$scope banned $nameip_str for $duration. Reason: $reason";

            // add to the output string
            $str = $lifted ? $str . $ban_str . '<br>' . $lifted_str : $str . $ban_str;

            // move to the next ban
            $str .= '</li>';
        }

        // end this group of bans
        $str .= '</ul><br>';
    }

    // get IP bans of target user's IP
    $ip_bans = db_op('bans_select_by_ip', array($ip));
    $ip_ban_count = (int) count($ip_bans);
    $ip_link = urlify("https://pr2hub.com/mod/ip_info.php?ip=$ip", $ip);
    $ip_lang = 'IP' . (!$mod->trial_mod ? " ($ip_link)" : '');
    $str .= "This $ip_lang has been banned $ip_ban_count times.<br><br>";

    // make account bans list
    if ($ip_ban_count !== 0) {
        $str .= '<ul>';
        foreach ($ip_bans as $ban) {
            $str .= '<li>';
            $ban_id = (int) $ban->ban_id;
            $date = date("M j, Y g:i A", $ban->time);
            $mod_name = htmlspecialchars($ban->mod_name, ENT_QUOTES);
            $banned_name = htmlspecialchars($ban->banned_name, ENT_QUOTES);
            $banned_ip = htmlspecialchars(urlencode($ban->banned_ip), ENT_QUOTES);
            $duration = format_duration($ban->expire_time - $ban->time);
            $reason = htmlspecialchars($ban->reason, ENT_QUOTES);
            $lifted = (bool) $ban->lifted;
            $acc_ban = (bool) $ban->account_ban;
            $ip_ban = (bool) $ban->ip_ban;
            $scope = $ban->scope === 'g' ? '' : ' socially';

            // var init
            $nameip_str = '';
            $lifted_str = '';
            $reason = is_empty($reason) ? 'No reason was given.' : $reason;

            // make name/ip str
            $nameip_str = $acc_ban ? $nameip_str . $banned_name : $nameip_str;
            $nameip_str = $ip_ban && !$mod->trial_mod ? $nameip_str . ' [' . $banned_ip . ']' : $nameip_str;
            $nameip_str = is_empty($nameip_str) && $mod->trial_mod ? '<i>an IP</i>' : trim($nameip_str);

            // check if lifted
            if ($lifted) {
                $lifted_datetime = date('M j, Y \a\t g:i A', $ban->lifted_time);
                $lifted_by = htmlspecialchars($ban->lifted_by, ENT_QUOTES);
                $lifted_reason = htmlspecialchars($ban->lifted_reason, ENT_QUOTES);
                $lifted_str = "<b>^ LIFTED</b> on $lifted_datetime by $lifted_by. Reason: $lifted_reason";
            }

            // craft ban string
            $date_url = urlify("https://pr2hub.com/bans/show_record.php?ban_id=$ban_id", $date);
            $ban_str = "$date_url: $mod_name$scope banned $nameip_str for $duration. Reason: $reason";

            // add to the output string
            $str = $lifted ? $str . $ban_str . '<br>' . $lifted_str : $str . $ban_str;

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


// checks the status of a private server
function privateServerCheckStatus()
{
    global $is_ps, $server_expire_time;

    if ($is_ps && $server_expire_time <= time()) {
        db_op('servers_deactivate_expired');
        shutdown_server(null, true, 'This private server has expired. Thanks for playing!');
    }
}


// perform an operation on the db via a query fn (try reconnecting and retrying on failure)
function db_op($fn, $data = array())
{
    global $pdo, $reconnect_attempted;

    try {
        // sanity: does the fn exist?
        if (!function_exists($fn)) {
            throw new Exception("Function \"$fn\" does not exist.");
        }

        // build params and call fn
        $params = array($pdo);
        foreach ($data as $var) {
            array_push($params, $var);
        }
        $result = call_user_func_array($fn, $params);

        // got here? means it did what it was supposed to do
        $reconnect_attempted = false;
        return $result;
    } catch (Exception $e) {
        $error = $e->getMessage();
        output("DB_OP: Query \"$fn\" failed. Error: $error");
        if (!$reconnect_attempted) {
            $reconnect_attempted = true;
            output('DB_OP: Renewing database connection...');
            $pdo = null;
            $pdo = pdo_connect();
            output('DB_OP: New connection succeeded!');
            return db_op($fn, $data);
        } else {
            throw new Exception($error . " Params: " . json_encode($params));
        }
    }
}


// close socket to new connections and unbind port
// DO NOT CALL WITHOUT SHUTDOWN_SERVER OR RESTART_SERVER
function kill_socket()
{
    global $server;

    output("Closing socket and unbinding port...");
    if (!$server) {
        output("No port bound; no server socket to close.");
    } else {
        $server->__destruct();
        output("Socket closed.");
    }
}


// graceful shutdown
function shutdown_server($socket = null, $die = true, $msg = 'The server is restarting, hold on a sec...')
{
    global $player_array, $socket;

    // kill socket
    kill_socket();

    // disconnect everyone
    output('Disconnecting all players...');
    foreach ($player_array as $player) {
        $player->write("message`$msg");
        $player->remove();
    }

    // tell the world
    output('All players disconnected. Shutting down...');
    output('The shutdown was successful.');
    if (!is_null($socket)) {
        $socket->write('The shutdown was successful.');
    }

    // socketDaemon shutdown
    if ($die === true) {
        die();
    }
}


// graceful restart
function restart_server()
{
    global $server_id;

    // kill socket
    kill_socket();

    // disconnect everyone
    shutdown_server(null, false);

    // start new instance of server
    $server_id = (int) $server_id;
    echo shell_exec('php ' . COMMON_DIR . "/manage_socket/restart_server.php $server_id");
    die(output("The restart was successful."));
}


// not so graceful shutdown
function __crashHandler($force = false)
{
    // this function gets called every time the script ends so we want to make sure it's a crash
    $error = error_get_last();
    if ($error['type'] !== E_ERROR && !$force) {
        return;
    }

    global $server_id;

    // handle crash
    output("--- SERVER IS CRASHING ---");
    output("Saving data...");
    shutdown_server(null, false, 'The server is restarting (due to an error), hold on a sec...');
    output("Data successfully saved.");
}
