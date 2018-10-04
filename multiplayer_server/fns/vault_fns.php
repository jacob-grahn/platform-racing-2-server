<?php

// unlock the super booster
function process_unlock_super_booster($socket, $data)
{
    if ($socket->process == true) {
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

    if ($socket->process == true) {
        list( $slug, $user_id, $guild_id, $user_name ) = explode('`', $data);

        start_perk($slug, $user_id, $guild_id);
        $player = id_to_player($user_id, false);
        $display_name = userify($player, $user_name);

        if ($guild_id != 0) {
            if ($slug == \pr2\multi\Perks::GUILD_FRED) {
                send_to_guild($guild_id, "systemChat`$display_name unlocked Fred mode for your guild!");
            }
            if ($slug == \pr2\multi\Perks::GUILD_GHOST) {
                send_to_guild($guild_id, "systemChat`$display_name unlocked Ghost mode for your guild!");
            }
            if ($slug == \pr2\multi\Perks::GUILD_ARTIFACT) {
                send_to_guild($guild_id, "systemChat`$display_name unlocked Artifact mode for your guild!");
            }
            if ($slug == \pr2\multi\Perks::HAPPY_HOUR) {
                global $chat_room_array;
                if (isset($chat_room_array['main'])) {
                    $main = $chat_room_array['main'];
                    $main->sendChat("systemChat`$display_name just triggered a Happy Hour!", -1);
                    foreach ($player_array as $player) {
                        if (isset($player->chat_room) && $player->chat_room != $main) {
                            $player->write("systemChat`$display_name just triggered a Happy Hour!");
                        }
                    }
                } else {
                    sendToAll_players("systemChat`$display_name just triggered a Happy Hour!");
                }
            }
        }

        $socket->write('{"status":"ok"}');
    }
}


// unlock the king set
function process_unlock_set_king($socket, $data)
{
    if ($socket->process == true) {
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
    if ($socket->process == true) {
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
    if ($socket->process == true) {
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
    if ($socket->process == true) {
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
