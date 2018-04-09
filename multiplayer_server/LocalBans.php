<?php


class LocalBans
{
    
    private static $arr = array();
    
    
    public static function add($user_name, $duration = 1800)
    {
        $ban = new stdClass();
        $ban->user_name = $user_name;
        $ban->expire_time = time() + $duration;
        self::$arr[] = $ban;
    }
    
    
    public static function remove($user_name)
    {
        $len = count(self::$arr);
        for ($i=0; $i<$len; $i++) {
            $ban = self::$arr[$i];
            if ($ban->user_name == $user_name) {
                array_splice(self::$arr, $i, 1);
                return true;
            }
            continue;
        }
        return false;
    }
    
    
    public static function is_banned($user_name)
    {
        $match = false;
        foreach (self::$arr as $ban) {
            if ($ban->user_name == $user_name) {
                $match = true;
                break;
            }
        }
        return( $match );
    }
    
    
    public static function remove_expired()
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
