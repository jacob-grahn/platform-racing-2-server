<?php

function announce_tournament($chat)
{
    if (PR2SocketServer::$tournament) {
        $chat->sendToAll(
            'systemChat`Tournament mode is on!<br/>'
            .'Hat: '.Hats::id_to_str(PR2SocketServer::$tournament_hat).'<br/>'
            .'Speed: '.PR2SocketServer::$tournament_speed.'<br/>'
            .'Accel: '.PR2SocketServer::$tournament_acceleration.'<br/>'
            .'Jump: '.PR2SocketServer::$tournament_jumping
        );
    } else {
        $chat->sendToAll('systemChat`Tournament mode is off.');
    }
}

function tournament_status($requester)
{
    if (PR2SocketServer::$tournament) {
        $requester->write(
            'systemChat`Tournament mode is on!<br/>'
            .'Hat: '.Hats::id_to_str(PR2SocketServer::$tournament_hat).'<br/>'
            .'Speed: '.PR2SocketServer::$tournament_speed.'<br/>'
            .'Accel: '.PR2SocketServer::$tournament_acceleration.'<br/>'
            .'Jump: '.PR2SocketServer::$tournament_jumping
        );
    } else {
        $requester->write('systemChat`Tournament mode is off.');
    }
}
