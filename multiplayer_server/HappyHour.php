<?php

class HappyHour
{

    public static $hh_active_until = 0;
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
        if (pr2_server::$tournament) {
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
        if (self::isActive() != false && $hh_active_until != 0) {
            $timeleft = time() - $hh_active_until;
            return $timeleft;
        } else {
            return false;
        }
    }
}

HappyHour::$random_hour = rand(0, 36);
