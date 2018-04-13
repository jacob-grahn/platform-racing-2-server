<?php

namespace pr2\multi;

class HappyHour
{

    private static $hh_active_until = 0;
    public static $random_hour = 0;

    public static function activate($duration = 3600)
    {
        $time = time();

        if (self::$hh_active_until < $time) {
            self::$hh_active_until = $time;
        }

        self::$hh_active_until += $duration;
    }

    public static function isActive()
    {
        if (PR2SocketServer::$tournament) {
            return false;
        } elseif (self::$hh_active_until >= time()) {
            return true;
        } else {
            $current_hour = (int) date('G');
            return $current_hour === self::$random_hour;
        }
    }

    public static function deactivate()
    {
        $time = time();

        if (self::$hh_active_until > $time) {
            self::$hh_active_until = 0;
        }
    }

    public static function timeLeft()
    {
        if (self::isActive()) {
            $timeleft = self::$hh_active_until - time();
            return $timeleft;
        } else {
            return false;
        }
    }
}
