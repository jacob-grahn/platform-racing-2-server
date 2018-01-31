<?php

function issue_tournament( $str ) {
	
	// if nothing was passed to the function, disable tournament mode
	if( !isset($str) || $str == '' ) {
		pr2_server::$tournament = false;
	}
	else {
		$arr = explode( ' ', $str ); // add an array item on every space
		array_shift($arr); // don't include /t in the array

		// did the user say /t on? if so, let's ignore 'on' and handle the rest of the data as normal (if any)
		if( isset($arr[0]) && $arr[0] == 'on' ) {
			// take 'on' out of the array
			array_shift($arr);

			// if they didn't give any values after "on", start the default tournament
			if ( !isset($arr[0]) || trim($str) == '/t on' ) {
				$arr[0] = "None";
				$arr[1] = "65";
				$arr[2] = "65";
				$arr[3] = "65";
			}
		}
		
		// did the user say /t off? if so, let's turn off the tournament
		if( !isset($arr[0]) || $arr[0] == 'off' || $arr[0] == '' ) {
			pr2_server::$tournament = false;
		}
		// if not /t off
		else {
			if (isset($arr[0]) && isset($arr[1]) && isset($arr[2]) && isset($arr[3]) && !isset($arr[4])) {
				pr2_server::$tournament = true;
				
				// make array values easier to work with
				$hat = $arr[0];
				$speed = (int) $arr[1];
				$accel = (int) $arr[2];
				$jump = (int) $arr[3];
			}
			else {
				throw new Exception("It looks like you left out a stat value or the hat name, or added too many arguments.");
			}
		}
		
		// if tournament mode was determined to be on, let's set the values
		if( pr2_server::$tournament ) {
			if( isset($hat) ) {
				if( Hats::str_to_id($hat) <= 14 ) {
					pr2_server::$tournament_hat = limit( Hats::str_to_id($hat), 1, 14 );
				}
				else {
					throw new Exception("The hat you entered isn't a valid hat name.");
				}
			}
			if( isset($speed) ) {
				if ($speed >= 0 && $speed <= 100) {
					pr2_server::$tournament_speed = limit($speed, 0, 100);
				}
				else {
					throw new Exception("Stat values can only be between 0 and 100.");
				}
			}
			if( isset($accel) ) {
				if ($accel >= 0 && $accel <= 100) {
					pr2_server::$tournament_acceleration = limit($accel, 0, 100);
				}
				else {
					throw new Exception("Stat values can only be between 0 and 100.");
				}
			}
			if( isset($jump) ) {
				if ($jump >= 0 && $jump <= 100) {
					pr2_server::$tournament_jumping = limit($jump, 0, 100);
				}
				else {
					throw new Exception("Stat values can only be between 0 and 100.");
				}
			}
		}
	}
}

?>
