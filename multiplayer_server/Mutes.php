<?php

namespace pr2\multi;

class Mutes
{

    private static $arr = array();


    public static function add($user_name, $duration = 60)
    {
        $mute = new \stdClass();
        $mute->user_name = $user_name;
        $mute->expire_time = time() + $duration;
        self::$arr[] = $mute;
    }


    public static function remove($user_name)
    {
        $len = count(self::$arr);
        for ($i=0; $i<$len; $i++) {
            $mute = self::$arr[$i];
            if (strtolower(trim($mute->user_name)) == strtolower(trim($user_name))) {
                array_splice(self::$arr, $i, 1);
                return true;
            }
            continue;
        }
        return false;
    }


    public static function isMuted($user_name)
    {
        $isMuted = false;
        $muted_until = self::getMute($user_name)->expire_time;
        if ($muted_until > 0 && $muted_until > time()) {
            $isMuted = true;
        }
        return $isMuted;
    }


    public static function remainingTime($user_name)
    {
        if (self::isMuted($user_name) === true) {
            return self::getMute($user_name)->expire_time - time();
        }
    }


    private static function getMute($user_name)
    {
        foreach (self::$arr as $mute) {
            if (strtolower(trim($mute->user_name)) == strtolower(trim($user_name))) {
                return $mute;
            }
        }

        // return a blank mute if we got here
        $mute = new \stdClass();
        $mute->user_name = null;
        $mute->expire_time = 0;
        return $mute;
    }


    public static function removeExpired()
    {
        $time = time();
        $len = count(self::$arr);
        for ($i=0; $i<$len; $i++) {
            $mute = self::$arr[ $i ];
            if ($mute->expire_time < $time) {
                array_splice(self::$arr, $i, 1);
                $len--;
                $i--;
            }
        }
    }
}
