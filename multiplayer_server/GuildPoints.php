<?php

class GuildPoints {
	
	private static $arr = array();
	
	
	public static function submit( $user_id, $level_id, $gp ) {
		$obj = self::id_to_obj( $user_id );
		$obj->gp += $gp;
		if( isset( $obj->levels[ $level_id ] ) ) {
			$obj->levels[ $level_id ] += $gp;
		}
		else {
			$obj->levels[ $level_id ] = $gp;
		}
	}
	
	
	public static function get_previous_gp( $user_id, $level_id ) {
		$obj = self::id_to_obj( $user_id );
		$prev_gp = 0;
		if( isset( $obj->levels[ $level_id ] ) ) {
			$prev_gp = $obj->levels[ $level_id ];
		}
		return $prev_gp;
	}
	
	
	public static function drain() {
		$cup = array();
		foreach( self::$arr as $obj ) {
			if( $obj->gp > 0 ) {
				$cup[ $obj->user_id ] = $obj->gp;
				$obj->gp = 0;
			}
		}
		return $cup;
	}	
	
	
	public static function clear() {
		self::$arr = array();
	}
	
	
	private static function id_to_obj( $user_id ) {
		if( !isset( self::$arr[ $user_id ] ) ) {
			$obj = new stdClass();
			$obj->levels = array();
			$obj->gp = 0;
			$obj->user_id = $user_id;
			self::$arr[ $user_id ] = $obj;
		}
		else {
			$obj = self::$arr[ $user_id ];
		}
		return $obj;
	}
}

?>