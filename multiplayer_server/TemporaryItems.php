<?php

class TemporaryItems {
	
	private static $items = array();
	
	
	public static function add( $type, $part_id, $user_id, $guild_id, $duration ) {
		$match = false;
		foreach( self::$items as $item ) {
			if( $item->type == $type && $item->part_id == $part_id && ($item->guild_id == $guild_id || $item->user_id == $part_id) ) {
				$item->expire_time += $duration;
				$match = true;
				break;
			}
		}
		
		if( !$match ) {
			$item = new stdClass();
			$item->type = $type;
			$item->part_id = $part_id;
			$item->user_id = $user_id;
			$item->guild_id = $guild_id;
			$item->expire_time = time() + $duration;
			self::$items[] = $item;
		}
	}
	
	
	public static function get_items( $user_id, $guild_id ) {
		$arr = array();
		foreach( self::$items as $item ) {
			if( $item->user_id == $user_id || ($guild_id != 0 && $item->guild_id == $guild_id) || $item->guild_id == -1 ) {
				$arr[] = $item;
			}
		}
		return $arr;
	}
	
	
	public static function get_parts( $type, $user_id, $guild_id ) {
		$parts = array();
		$items = self::get_items( $user_id, $guild_id );
		foreach( $items as $item ) {
			if( $item->type == $type ) {
				$parts[] = $item->part_id;
			}
		}
		return $parts;
	}
	
	
	public static function remove_expired() {
		$time = time();
		$len = count( self::$items );
		for( $i=0; $i<$len; $i++ ) {
			$item = self::$items[ $i ];			
			if( $item->expire_time < $time ) {
				array_splice( self::$items, $i, 1 );
				$len--;
				$i--;
			}
		}
	}
}
				
?>
