<?php

namespace pr2\multi;

class PR2SocketServer extends \chabot\socketServer
{

    public static $last_read_time = 0;
    public static $tournament = false;
    public static $no_prizes = false;
    public static $tournament_hat = 1;
    public static $tournament_speed = 65;
    public static $tournament_acceleration = 65;
    public static $tournament_jumping = 65;


    public function __construct($client_class, $bind_address = 0, $bind_port = 0, $domain = AF_INET, $type = SOCK_STREAM, $protocol = SOL_TCP)
    {
        parent::__construct($client_class, $bind_address, $bind_port, $domain, $type, $protocol);
        PR2SocketServer::$last_read_time = time();
    }


    public function on_timer()
    {
        //once every 10 seconds
        TemporaryItems::removeExpired();
        LocalBans::remove_expired();
    }


    public function on_timer2()
    {
        //once every second
        LoiterDetector::check();
        $this->consider_shutting_down();
    }


    private function consider_shutting_down()
    {
        $elapsed = time() - PR2SocketServer::$last_read_time;
        if ($elapsed > 60*5) {
            shutdown_server();
        }
    }
}
