<?php

namespace pr2\multi;

class Mutes
{

    private static $arr = array();


    public static function add($user_name, $ip, $duration = 60)
    {
        $mute = new \stdClass();
        $mute->user_name = $user_name;
        $mute->ip = $ip;
        $mute->expire_time = time() + $duration;
        self::$arr[] = $mute;
    }


    public static function remove($user_name)
    {
        $len = count(self::$arr);
        for ($i = 0; $i < $len; $i++) {
            $mute = self::$arr[$i];
            if (strtolower(trim($mute->user_name)) === strtolower(trim($user_name))) {
                array_splice(self::$arr, $i, 1);
                return true;
            }
            continue;
        }
        return false;
    }


    public static function isMuted($user_name, $ip = '')
    {
        $isMuted = false;
        $muted_until = self::getMute($user_name, $ip)->expire_time;
        if ($muted_until > 0 && $muted_until > time()) {
            $isMuted = true;
        }
        return $isMuted;
    }


    public static function remainingTime($user_name, $ip)
    {
        if (self::isMuted($user_name, $ip) === true) {
            return self::getMute($user_name, $ip)->expire_time - time();
        }
    }


    private static function getMute($user_name, $ip)
    {
        $name = null;
        $exp = 0;
        foreach (self::$arr as $mute) {
            if (strtolower(trim($mute->user_name)) === strtolower(trim($user_name)) || trim($mute->ip) === trim($ip)) {
                if ($mute->expire_time - time() > $exp) {
                    $exp = $mute->expire_time;
                    $name = $mute->user_name;
                }
            }
        }

        // returns a blank mute if nothing matched
        $mute = new \stdClass();
        $mute->user_name = $name;
        $mute->expire_time = $exp;
        return $mute;
    }


    public static function getAll()
    {
        return self::$arr;
    }


    public static function removeExpired()
    {
        $time = time();
        $len = count(self::$arr);
        for ($i = 0; $i < $len; $i++) {
            $mute = self::$arr[$i];
            if ($mute->expire_time < $time) {
                array_splice(self::$arr, $i, 1);
                $len--;
                $i--;
            }
        }
    }
}
