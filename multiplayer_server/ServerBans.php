<?php

namespace pr2\multi;

class ServerBans
{

    private static $arr = array();


    public static function add($user_name, $duration = 1800)
    {
        $ban = new \stdClass();
        $ban->user_name = $user_name;
        $ban->expire_time = time() + $duration;
        self::$arr[] = $ban;
    }


    public static function remove($user_name)
    {
        $len = count(self::$arr);
        for ($i=0; $i<$len; $i++) {
            $ban = self::$arr[$i];
            if (strtolower(trim($ban->user_name)) == strtolower(trim($user_name))) {
                array_splice(self::$arr, $i, 1);
                return true;
            }
            continue;
        }
        return false;
    }


    public static function isBanned($user_name)
    {
        $match = false;
        foreach (self::$arr as $ban) {
            if (strtolower(trim($ban->user_name)) == strtolower(trim($user_name))) {
                $match = true;
                break;
            }
        }
        return $match;
    }


    public static function removeExpired()
    {
        $time = time();
        $len = count(self::$arr);
        for ($i=0; $i<$len; $i++) {
            $ban = self::$arr[ $i ];
            if ($ban->expire_time < $time) {
                array_splice(self::$arr, $i, 1);
                $len--;
                $i--;
            }
        }
    }
}
