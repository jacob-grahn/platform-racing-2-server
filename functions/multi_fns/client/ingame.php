<?php

//--- loose hat ----------------------------------------------------------------------------
function client_loose_hat($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->looseHat($player, $data);
    }
}



//--- pick up a lost hat ----------------------------------------------------------------------------
function client_get_hat($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->getHat($player, $data);
    }
}



//--- set pos ------------------------------------------------------------------
function client_p($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->setPos($player, $data);
    }
}



//--- set exact pos ----------------------------------------------------------------
function client_exact_pos($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->setExactPos($player, $data);
    }
}



//--- squash another player ---------------------------------------------------------------
function client_squash($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->squash($player, $data);
    }
}



//--- set variable ----------------------------------------------------------------
function client_set_var($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->setVar($player, $data);
    }
}



//--- add an effect -------------------------------------------------------------
function client_add_effect($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->sendToRoom('addEffect`'.$data, $player->user_id);
    }
}



//---- use a lightning item -------------------------------------------------------
function client_zap($socket)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->sendToRoom('zap`', $player->user_id);
    }
}


//--- hit a block ---------------------------------------------------------------
function client_hit($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->sendToRoom('hit'.$data, $player->user_id);
    }
}



//--- touch a block ---------------------------------------------------------------
function client_activate($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->sendToRoom('activate`'.$data.'`', $player->user_id);
    }
}



//--- bump a heart block ---------------------------------
function client_heart($socket)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->lives++;
        $player->game_room->sendToRoom('heart'.$player->temp_id.'`', $player->user_id);
    }
}



//--- finish drawing ------------------------------------------------------------
function client_finish_drawing($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->finishDrawing($player, $data);
    }
}



//--- finish race ------------------------------------------------------------
function client_finish_race($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->remoteFinishRace($player, $data);
    }
}



//--- quit race -----------------------------------------------------------------
function client_quit_race($socket)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->quitRace($player);
    }
}


//--- grab egg -----------------------------------------------------------------
function client_grab_egg($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->grabEgg($player, $data);
    }
}



//-- record single finish in objective mode -----------------------------------------------
function client_objective_reached($socket, $data)
{
    $player = $socket->getPlayer();
    if (isset($player->game_room)) {
        $player->game_room->objectiveReached($player, $data);
    }
}
