<?php

//--- creates a player if the log in was successful -----------------------
function process_register_login($server_socket, $data)
{
    if ($server_socket->process == true) {
        global $server_id, $ANNIE_ID, $login_array, $player_array, $guild_id, $guild_owner;

        $login_obj = json_decode($data);
        $login_id = $login_obj->login->login_id;
        $group = $login_obj->user->power;
        $user_id = $login_obj->user->user_id;
        
         // annie
        if ($server_id === $ANNIE_ID) {
            // preserve staff/guest
            $login_obj->guest = false;
            $login_obj->is_mod = false;
            $login_obj->is_admin = false;
            if ($group == 0) {
                $login_obj->guest = true;
            }
            if ($group >= 2) {
                $login_obj->is_mod = true;
                if ($group >= 3) {
                    $login_obj->is_admin = true;
                }
            }
            
            $all = '1,2,3,4,5,6,7,8,9,10,'
                    .'11,12,13,14,15,16,17,18,19,'
                    .'20,21,22,23,24,25,26,27,28,29,30,'
                    .'31,32,33,34,35,36,37,38,39,40';
            $login_obj->stats->rank = 2008;
            $login_obj->stats->hat_array = '1,2,3,4,5,6,7,8,9,10,11,12,13,14';
            $login_obj->stats->head_array = $all;
            $login_obj->stats->body_array = $all;
            $login_obj->stats->feet_array = $all;
            $login_obj->epic_upgrades->epic_hats = '*';
            $login_obj->epic_upgrades->epic_heads = '*';
            $login_obj->epic_upgrades->epic_bodies = '*';
            $login_obj->epic_upgrades->epic_feet = '*';
            $login_obj->rt_used = '0';
            $login_obj->rt_available = 0;
        }

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

                $socket->write('message`Your account was already running on this server. '
                                .'It has been logged out to save your data. '
                                .'Please log in again.');
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
                    //$player->becomeGuest();
                }
                
                if ($server_id === $ANNIE_ID) {
                    $group = 3; // annie
                    $player->group = 3;
                    $player->write('message`Happy Anniversary! PR2 turns 10 years old today. '
                                    .'You\'ll have some awesome features on this server only:<br><br>'
                                    .' - You\'re an admin!<br>'
                                    .' - You have all parts and epic upgrades!<br>'
                                    .' - You\'ve been elevated to rank 2008!<br><br>'
                                    .'You won\'t be able to earn any prizes or EXP on this server, but never fear! '
                                    .'Here\'s what the other servers are doing to celebrate:<br><br>'
                                    .' - 10x EXP bonus!<br>'
                                    .' - 2x more likely to find prizes randomly!<br>'
                                    .' - You get an epic party hat to keep!<br><br>'
                                    .'Thanks for making these 10 years so great.<br>'
                                    .'I hope you enjoy!<br><br>'
                                    .' - Jacob<br><br>'
                                    .'P.S. There\'s some other stuff hidden here and there. '
                                    .'Look around and see if you can find it all!');
                } else {
                    $player->write('message`Happy Anniversary! PR2 turns 10 years old today. '
                                    .'You\'ll have some cool features on servers other than Annie:<br><br>'
                                    .' - 10x EXP bonus!<br>'
                                    .' - 2x more likely to find prizes randomly!<br>'
                                    .' - You get an epic party hat to keep!<br><br>'
                                    .'And on Annie, you\'ll get some even <i><b>cooler</b></i> features! '
                                    .'On that server:<br><br>'
                                    .' - You\'re an admin!<br>'
                                    .' - You have all parts and epic upgrades!<br>'
                                    .' - You\'ve been elevated to rank 2008!<br><br>'
                                    .'Thanks for making these 10 years so great.<br>'
                                    .'I hope you enjoy!<br><br>'
                                    .' - Jacob<br><br>'
                                    .'P.S. There\'s some other stuff hidden here and there. '
                                    .'Look around and see if you can find it all!');

                    // give the epic party hat to everyone who logs in
                    $player->gainPart("hat", 8, true);
                    $player->gainPart("eHat", 8, true);
                }
                $socket->write('loginSuccessful`'.$group);
                $socket->write('setRank`'.$player->active_rank);
                $socket->write('ping`' . time());
            }
        }
    }
}
