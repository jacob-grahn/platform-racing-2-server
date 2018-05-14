<?php


/* vault-related process functions are in vault_fns.php */


// activate a happy hour
function process_activate_happy_hour($socket)
{
    if ($socket->process === true) {
        output('Activating the happiest of hours...');
        if (\pr2\multi\HappyHour::isActive() === false) {
            \pr2\multi\HappyHour::activate();
            output('Happy hour activated!');
            $socket->write('Happy hour activated!');
        } else {
            $time = format_duration(\pr2\multi\HappyHour::timeLeft());
            output("It seems there's already an active Happy Hour. It will expire in $time.");
            $socket->write("It seems there's already an active Happy Hour. It will expire in $time.");
        }
    }
}


// check server status
function process_check_status($socket)
{
    if ($socket->process === true) {
        $socket->write('This server is a-okay!');
    }
}


// clear player's daily exp levels
function process_start_new_day($socket)
{
    if ($socket->process == true) {
        global $player_array;
        foreach ($player_array as $player) {
            $player->start_exp_today = $player->exp_today = 0;
        }
        $socket->write('Another day, another destiny!');
    }
}


// run update cycle
function process_update_cycle($socket, $data)
{
    if ($socket->process == true) {
        $obj = json_decode($data);
        place_artifact($obj->artifact);
        pm_notify($obj->recent_pms);
        apply_bans($obj->recent_bans);

        $rep = new stdClass();
        $rep->plays = drain_plays();
        $rep->gp = \pr2\multi\GuildPoints::drain();
        $rep->population = get_population();
        $rep->status = get_status();
        $rep->happy_hour = \pr2\multi\HappyHour::isActive();

        $socket->write(json_encode($rep));
    }
}


// creates a player from a successful login
function process_register_login($server_socket, $data)
{
    if ($server_socket->process == true) {
        global $login_array, $player_array, $guild_id, $guild_owner;

        $login_obj = json_decode($data);
        $login_id = $login_obj->login->login_id;
        $group = $login_obj->user->power;
        $user_id = $login_obj->user->user_id;

        $socket = @$login_array[$login_id];
        unset($login_array[$login_id]);

        if (isset($socket)) {
            if (!$server_socket->process) {
                $socket->write('message`Login verify failed.');
                $socket->close();
                $socket->onDisconnect();
            } elseif ($guild_id != 0 && $guild_id != $login_obj->user->guild) {
                $socket->write('message`You are not a member of this guild.');
                $socket->close();
                $socket->onDisconnect();
            } elseif (isset($player_array[$user_id])) {
                $existing_player = $player_array[$user_id];
                $existing_player->write('message`You were disconnected because you logged in somewhere else.');
                $existing_player->remove();

                $socket->write('message`Your account was already running on this server. '.
                    'It has been logged out to save your data. Please log in again.');
                $socket->close();
                $socket->onDisconnect();
            } elseif (\pr2\multi\LocalBans::isBanned($login_obj->user->name)) {
                $socket->write('message`You have been kicked from this server for 30 minutes.');
                $socket->close();
                $socket->onDisconnect();
            } else {
                $player = new \pr2\multi\Player($socket, $login_obj);
                $socket->player = $player;
                if ($player->user_id == $guild_owner) {
                    $player->becomeServerOwner();
                } elseif ($player->group <= 0) {
                    $player->becomeGuest();
                }

                $socket->write('loginSuccessful`'.$group);
                $socket->write('setRank`'.$player->active_rank);
                $socket->write('ping`' . time());
            }
        }
    }
}


// message a player on the server
function process_message_player($socket, $data)
{
    global $server_name;
    
    if ($socket->process == true) {
        $obj = json_decode($data);
        $user_id = $obj->user_id;
        $message = $obj->message;

        $player = id_to_player($user_id, false);
        if (isset($player)) {
            $player->write('message`' . $message);
        }
        $socket->write("Message sent to player on $server_name.");
    }
}


// server shutdown
function process_shut_down($socket)
{
    if ($socket->process === true) {
        output('Received shutdown command. Initializing shutdown...');
        $socket->write('The shutdown was successful.');
        shutdown_server($socket);
    }
}
