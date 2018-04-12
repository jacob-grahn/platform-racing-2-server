<?php

function announce_tournament($chat)
{
    if (\pr2\multi\PR2SocketServer::$tournament) {
        $chat->sendToAll(
            'systemChat`Tournament mode is on!<br/>'
            .'Hat: '.\pr2\multi\Hats::id_to_str(\pr2\multi\PR2SocketServer::$tournament_hat).'<br/>'
            .'Speed: '.\pr2\multi\PR2SocketServer::$tournament_speed.'<br/>'
            .'Accel: '.\pr2\multi\PR2SocketServer::$tournament_acceleration.'<br/>'
            .'Jump: '.\pr2\multi\PR2SocketServer::$tournament_jumping
        );
    } else {
        $chat->sendToAll('systemChat`Tournament mode is off.');
    }
}

function tournament_status($requester)
{
    if (\pr2\multi\PR2SocketServer::$tournament) {
        $requester->write(
            'systemChat`Tournament mode is on!<br/>'
            .'Hat: '.\pr2\multi\Hats::id_to_str(\pr2\multi\PR2SocketServer::$tournament_hat).'<br/>'
            .'Speed: '.\pr2\multi\PR2SocketServer::$tournament_speed.'<br/>'
            .'Accel: '.\pr2\multi\PR2SocketServer::$tournament_acceleration.'<br/>'
            .'Jump: '.\pr2\multi\PR2SocketServer::$tournament_jumping
        );
    } else {
        $requester->write('systemChat`Tournament mode is off.');
    }
}
