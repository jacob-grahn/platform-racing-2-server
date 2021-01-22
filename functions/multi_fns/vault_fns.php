<?php


// extend server life
function process_extend_server_life($socket, $data)
{
    if ($socket->process === true) {
        global $server_expire_time, $guild_id;

        list($sent_guild_id, $new_time) = explode('`', $data);
        if ($guild_id === (int) $sent_guild_id && $new_time > $server_expire_time) {
            $server_expire_time = (int) $new_time;
            $socket->write('{"status":"ok"}');
        }
    }
}


// unlock the super booster
function process_unlock_super_booster($socket, $data)
{
    if ($socket->process === true) {
        $user_id = $data;
        $player = id_to_player($user_id, false);
        if (isset($player)) {
            $player->super_booster = true;
        }
        $socket->write('ok`');
    }
}


// unlock a temporary perk
function process_unlock_perk($socket, $data)
{
    global $player_array;

    if ($socket->process === true) {
        list($slug, $user_id, $guild_id, $user_name, $quantity) = explode('`', $data);
        $user_id = (int) $user_id;
        $guild_id = (int) $guild_id;
        start_perk($slug, $user_id, $guild_id, time() + ($quantity * 3600));
        $player = id_to_player($user_id, false);
        $display_name = userify($player, $user_name);

        if ($guild_id !== 0) {
            if (strpos($slug, 'guild_') === 0) {
                $type = ucfirst(explode('_', $slug)[1]);
                $duration = format_duration($quantity * 3600);
                $msg = "$display_name unlocked $type mode for your guild for $duration!";
                send_to_guild($guild_id, "systemChat`$msg");
            } elseif ($slug === 'happy_hour') {
                global $chat_room_array;

                $hh_lang = $quantity > 1 ? "$quantity Happy Hours" : 'a Happy Hour';
                if (isset($chat_room_array['main'])) {
                    $main = $chat_room_array['main'];
                    $main->sendChat("systemChat`$display_name just triggered $hh_lang!");
                    foreach ($player_array as $player) {
                        if (isset($player->chat_room) && $player->chat_room !== $main) {
                            $player->write("systemChat`$display_name just triggered $hh_lang!");
                        }
                    }
                } else {
                    sendToAll_players("systemChat`$display_name just triggered $hh_lang!");
                }
            }
        }

        $socket->write('{"status":"ok"}');
    }
}


// unlock the king set
function process_unlock_set_king($socket, $data)
{
    if ($socket->process === true) {
        $user_id = $data;
        $player = id_to_player($user_id, false);
        if (isset($player)) {
            $player->gainPart('head', 28, true);
            $player->gainPart('body', 26, true);
            $player->gainPart('feet', 24, true);
            $player->gainPart('eHead', 28);
            $player->gainPart('eBody', 26);
            $player->gainPart('eFeet', 24);
            $player->sendCustomizeInfo();
        }
        $socket->write('{"status":"ok"}');
    }
}


// unlock the queen set
function process_unlock_set_queen($socket, $data)
{
    if ($socket->process === true) {
        $user_id = $data;
        $player = id_to_player($user_id, false);
        if (isset($player)) {
            $player->gainPart('head', 29, true);
            $player->gainPart('body', 27, true);
            $player->gainPart('feet', 25, true);
            $player->gainPart('eHead', 29);
            $player->gainPart('eBody', 27);
            $player->gainPart('eFeet', 25);
            $player->sendCustomizeInfo();
        }
        $socket->write('{"status":"ok"}');
    }
}


// unlock the djinn set
function process_unlock_set_djinn($socket, $data)
{
    if ($socket->process === true) {
        $user_id = $data;
        $player = id_to_player($user_id, false);
        if (isset($player)) {
            $player->gainPart('head', 35, true);
            $player->gainPart('body', 35, true);
            $player->gainPart('feet', 35, true);
            $player->gainPart('eHead', 35);
            $player->gainPart('eBody', 35);
            $player->gainPart('eFeet', 35);
            $player->sendCustomizeInfo();
        }
        $socket->write('{"status":"ok"}');
    }
}


// unlock epic everything
function process_unlock_epic_everything($socket, $data)
{
    if ($socket->process === true) {
        $user_id = $data;
        $player = id_to_player($user_id, false);
        if (isset($player)) {
            $player->gainPart('eHat', '*');
            $player->gainPart('eHead', '*');
            $player->gainPart('eBody', '*');
            $player->gainPart('eFeet', '*');
            $player->sendCustomizeInfo();
        }
        $socket->write('{"status":"ok"}');
    }
}


// unlock a rank token rental
function process_unlock_rank_token_rental($socket, $data)
{
    if ($socket->process === true) {
        $data = json_decode($data);

        global $player_array;
        foreach ($player_array as $player) {
            if ($player->user_id === $data->user_id || $player->guild_id === $data->guild_id) {
                $player->activateRankToken($data->quantity);
            }
        }

        $socket->write('{"status":"ok"}');
    }
}
