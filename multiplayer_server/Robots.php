<?php

class Robots {
	
	private static $ips = array();
	
	
	public static function add( $ip, $duration=3600 ) {
		$item = new stdClass();
		$item->$ip = $ip;
		$item->expire_time = time() + $duration;
		self::$ips[$ip] = $item;
	}
	
	
	public static function is_robot( $ip ) {
		$r = isset(self::$ips[$ip]);
		return $r;
	}
	
	
	public static function remove_expired() {
		$time = time();
		foreach(self::$ips as $ip=>$item) {
			if($item->expire_time < $time) {
				unset(self::$ips[$ip]);
			}
		}
	}
}
				
?>
