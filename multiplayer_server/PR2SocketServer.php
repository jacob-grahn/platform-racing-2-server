<?php

namespace pr2\multi;

class PR2SocketServer extends \chabot\SocketServer
{
    public static $tournament = false;
    public static $no_prizes = false;
    public static $tournament_hat = 1;
    public static $tournament_speed = 65;
    public static $tournament_acceleration = 65;
    public static $tournament_jumping = 65;

    public static $prizer_id = 0;

    // once every 2 seconds
    public function onTimer()
    {
        TemporaryItems::removeExpired();
        ServerBans::removeExpired();
        Mutes::removeExpired();
        LoiterDetector::check();
        \socialBansRemoveExpired();
        \privateServerCheckStatus();
    }
}
