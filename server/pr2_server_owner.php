<?php


function issue_tournament( $str ) {
	
	if( !isset($str) || $str == '' ) {
		pr2_server::$tournament = false;
	}
	else {
		$arr = explode( ' ', $str );
		array_shift($arr);
		$len = count( $arr );
		
		if( !isset($arr[0]) || $arr[0] == 'off' || $arr[0] == '' ) {
			pr2_server::$tournament = false;
		}
		else {
			pr2_server::$tournament = true;
		}
		
		if( isset($arr[0]) && $arr[0] == 'on' ) {
			array_shift($arr);
		}
		
		if( pr2_server::$tournament ) {
			if( isset($arr[0]) )
				pr2_server::$tournament_hat = limit( Hats::str_to_id($arr[0]), 1, 14 );
			if( isset($arr[1]) )
				pr2_server::$tournament_speed = limit( (int)$arr[1], 0, 100 );
			if( isset($arr[2]) )
				pr2_server::$tournament_acceleration = limit( (int)$arr[2], 0, 100 );
			if( isset($arr[3]) )
				pr2_server::$tournament_jumping = limit( (int)$arr[3], 0, 100 );
		}
	}
}

?>
