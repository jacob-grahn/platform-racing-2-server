<?php

class RankupCalculator {
	
	private static $exp_req = array();
	
	
	public static function init() {
		$exp_points = 30;
		for($i=1; $i<100; $i++){
			self::$exp_req[$i] = round($exp_points);
			$exp_points = $exp_points * 1.25;
		}
		self::$exp_req[0] = 0;
		self::$exp_req[1] = 1;
	}
	
	
	public static function get_exp_required($rank) {
		if(!is_numeric($rank) || $rank < 0 || $rank >= count(self::$exp_req)) {
			$rank = count(self::$exp_req) - 1;
		}
		return self::$exp_req[$rank];
	}
}

?>