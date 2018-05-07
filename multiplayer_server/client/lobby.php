<?php


// set right room
function client_set_right_room($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->right_room)) {
        $player->right_room->removePlayer($player);
    }
    if ($data != 'none' && isset($player->game_room)) {
        $player->game_room->removePlayer($player);
    }
    if ($data != 'none' && strpos($data, '`') === false) {
        global ${$data.'_room'};
        if (${$data.'_room'} != null) {
            ${$data.'_room'}->addPlayer($player);
        }
    }
}


// set the chat room
function client_set_chat_room($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->chat_room)) {
        $player->chat_room->removePlayer($player);
    }
    if (($data == 'mod' && $player->group < 2) ||
        ($data == 'admin' && ($player->group < 3 || $player->user_id == 4291976))
    ) {
        $data = 'none';
        $player->write('message`You lack the power to enter this room.');
    }

    if (is_obscene($data)) {
        $data = 'none';
        $player->write('message`Keep the room names clean, pretty please. :)');
    }
    if ($data != 'none') {
        $chat_room = get_chat_room($data);
        $chat_room->addPlayer($player);
    }
}


// set game room
function client_set_game_room($socket)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->removePlayer($player);
    }
}


// join a slot in a course box
function client_fill_slot($socket, $data)
{
    list($course_id, $slot) = explode('`', $data);
    $player = $socket->getPlayer();
    if (isset($player->right_room)) {
        $player->right_room->fillSlot($player, $course_id, $slot);
    }
}


// confirm a slot in a course box
function client_confirm_slot($socket)
{
    $player = $socket->getPlayer();
    $course_box = $player->course_box;
    if (isset($course_box)) {
        $course_box->confirmSlot($player);
    }
}


// clear a slot in a course box
function client_clear_slot($socket)
{
    $player = $socket->getPlayer();
    $course_box = $player->course_box;
    if (isset($course_box)) {
        $course_box->clearSlot($player);
    }
}


// force the players who have not confirmed out so the rest can play
function client_force_start($socket)
{
    $player = $socket->getPlayer();
    $course_box = $player->course_box;
    if (isset($course_box)) {
        $course_box->forceStart();
    }
}


// returns info for the customize page
function client_get_customize_info($socket)
{
    $player = $socket->getPlayer();
    $player->sendCustomizeInfo();
}


// sets info for the character
function client_set_customize_info($socket, $data)
{
    $player = $socket->getPlayer();
    $player->setCustomizeInfo($data);
}


// sends a chat message
function client_chat($socket, $data)
{
    $player = $socket->getPlayer();
    $player->sendChat($data);
}


// get a list of the players that are online
function client_get_online_list($socket)
{
    global $player_array;
    foreach ($player_array as $player) {
        $socket->write('addUser`'.$player->name.'`'.$player->group.'`'.
            $player->active_rank.'`'.(count($player->hat_array)-1));
    }
}


// get a list of the top chat rooms
function client_get_chat_rooms($socket)
{
    global $chat_room_array;
    $temp_array = array_merge($chat_room_array);
    usort($temp_array, 'sort_chat_room_array');

    $str = 'setChatRoomList';
    $count = count($temp_array);
    if ($count > 8) {
        $count = 8;
    }

    for ($i=0; $i<$count; $i++) {
        $chat_room = $temp_array[$i];
        $str .= '`'.$chat_room->chat_room_name.' - '.count($chat_room->player_array).' online';
    }
    
    if ($str === 'setChatRoomList') {
        $str .= '`No one is chatting. :(';
    }

    $socket->write($str);
}


// add a user to your ignored array
function client_ignore_user($socket, $data)
{
    $player = $socket->getPlayer();
    $ignored_player = name_to_player($data);
    if (isset($ignored_player)) {
        array_push($player->ignored_array, $ignored_player->user_id);
    }
}


// remove a user from your ignored array
function client_un_ignore_user($socket, $data)
{
    $player = $socket->getPlayer();
    $ignored_player = name_to_player($data);
    if (isset($player)) {
        $index = @array_search($ignored_player->user_id, $player->ignored_array);
        if ($index !== false) {
            $player->ignored_array[$index] = null;
            unset($player->ignored_array[$index]);
        }
    }
}


// unlock the kong set (ant set)
function client_award_kong_outfit($socket)
{
    $player = $socket->getPlayer();
    $player->awardKongOutfit();
}


// increment used rank tokens
function client_use_rank_token($socket)
{
    $player = $socket->getPlayer();
    $player->useRankToken();
}


// decrement used rank tokens
function client_unuse_rank_token($socket)
{
    $player = $socket->getPlayer();
    $player->unuseRankToken();
}
