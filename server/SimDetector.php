<?php

class SimDetector {
	
	private static $arr = array();
	private static $last_clear = 0;
	
	
	public static function is_simming($user_id, $level_id, $exp) {
		
		$simming = false;
		
		if((time() - self::$last_clear) > 43200) {
			self::clear();
		}
		
		if($exp > 100) {
			if(!isset(self::$arr[$user_id])) {
				self::$arr[$user_id] = array();
			}
			if(!isset(self::$arr[$user_id][$level_id])) {
				self::$arr[$user_id][$level_id] = array();
			}
			if(!isset(self::$arr[$user_id][$level_id][$exp])) {
				self::$arr[$user_id][$level_id][$exp] = 1;
			}
			else {
				self::$arr[$user_id][$level_id][$exp]++;
			}
			
			$repeat = self::$arr[$user_id][$level_id][$exp];
			
			if($repeat >= 3) {
				$simming = true;
			}
		}
		
		//echo "user_id: $user_id, level_id: $level_id, exp: $exp, simming: $simming\n";
		
		return $simming;
	}
	
	
	public static function clear() {
		self::$arr = array();
		self::$last_clear = time();
	}
}

?>