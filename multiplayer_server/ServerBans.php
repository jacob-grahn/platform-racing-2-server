<?php

namespace pr2\multi;

class ServerBans
{

    private static $arr = array();


    public static function add($user_name, $ip, $duration = 1800)
    {
        $ban = new \stdClass();
        $ban->user_name = $user_name;
        $ban->ip = $ip;
        $ban->expire_time = time() + $duration;
        self::$arr[] = $ban;
    }


    public static function remove($user_name)
    {
        $len = count(self::$arr);
        for ($i = 0; $i < $len; $i++) {
            $ban = self::$arr[$i];
            if (strtolower(trim($ban->user_name)) === strtolower(trim($user_name))) {
                array_splice(self::$arr, $i, 1);
                return true;
            }
            continue;
        }
        return false;
    }


    public static function applyToIP($ip)
    {
        global $player_array, $guild_id, $guild_owner;
        foreach ($player_array as $player) {
            $not_mod = $player->group < 2 || $player->temp_mod === true;
            if (trim($player->ip) === trim($ip) && ($not_mod || $guild_id > 0) && $guild_owner !== $player->user_id) {
                $player->remove();
            }
        }
    }


    public static function isBanned($user_name, $ip = '')
    {
        $match = false;
        foreach (self::$arr as $ban) {
            if (strtolower(trim($ban->user_name)) === strtolower(trim($user_name)) || trim($ban->ip) === trim($ip)) {
                $match = true;
                break;
            }
        }
        return $match;
    }


    public static function remainingTime($user_name, $ip)
    {
        if (self::isBanned($user_name, $ip) === true) {
            return self::getBan($user_name, $ip)->expire_time - time();
        }
    }


    private static function getBan($user_name, $ip)
    {
        $name = null;
        $exp = 0;
        foreach (self::$arr as $ban) {
            if (strtolower(trim($ban->user_name)) === strtolower(trim($user_name)) || trim($ban->ip) === trim($ip)) {
                if ($ban->expire_time - time() > $exp) {
                    $exp = $ban->expire_time;
                    $name = $ban->user_name;
                }
            }
        }

        // returns a blank ban if nothing matched
        $ban = new \stdClass();
        $ban->user_name = $name;
        $ban->expire_time = $exp;
        return $ban;
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
            $ban = self::$arr[$i];
            if ($ban->expire_time < $time) {
                array_splice(self::$arr, $i, 1);
                $len--;
                $i--;
            }
        }
    }
}
