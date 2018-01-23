<?php


//--- tries to pull a variable from the $_GET array. If it is not present, the default is used. ---------------
function get($str, $default){
	$val = $_GET[$str];
	if(!isset($val)){
		$val = $default;
	}
	return $val;
}



//--- looks for a variable in the url and form data. If none are found, retun the default.
function find_variable($string, $default){
	$variable = $_POST[$string];
	if(!isset($variable)){
		$variable = $_GET[$string];
	}
	if(!isset($variable)){
		$variable = $default;
	}
	return $variable;
}



//--- tests to see if a string contains obscene words ---------------------------------------
function is_obscene($str){
	$str = strtolower($str);
	$bad_array = array('fuck', 'shit', 'nigger', 'nigga', 'whore', 'bitch', 'slut', 'cunt', 'cock', 'dick', 'penis', 'damn', 'spic');
	$obscene = false;
	foreach($bad_array as $bad){
		if(strpos($str, $bad) !== false){
			$obscene = true;
			break;
		}
	}
	return $obscene;
}


//---
function remove_resource( $_target ) {

    //file?
    if( is_file($_target) ) {
        if( is_writable($_target) ) {
            if( @unlink($_target) ) {
                return true;
            }
        }

        return false;
    }

    //dir?
    if( is_dir($_target) ) {
        if( is_writeable($_target) ) {
            foreach( new DirectoryIterator($_target) as $_res ) {
                if( $_res->isDot() ) {
                    unset($_res);
                    continue;
                }

                if( $_res->isFile() ) {
                    remove_resource( $_res->getPathName() );
                } elseif( $_res->isDir() ) {
                    remove_resource( $_res->getRealPath() );
                }

                unset($_res);
            }

            if( @rmdir($_target) ) {
                return true;
            }
        }

        return false;
    }
}








function check_if_banned($connection, $user_id, $ip){
	//find out if the account or ip is banned
	$result = query_ban_record($connection, "banned_user_id = '$user_id'");
	if($result->num_rows <= 0) {
		$result = query_ban_record($connection, "banned_ip = '$ip'");
	}

	if($result->num_rows > 0){
		$row = $result->fetch_object();

		$ban_id = $row->ban_id;
		$banned_ip = $row->banned_ip;
		$banned_user_id = $row->banned_user_id;
		$mod_user_id = $row->mod_user_id;
		$expire_time = $row->expire_time;
		$reason = $row->reason;
		$response = $row->response;

		//figure out what the best way to say this is
		$seconds = $expire_time - time();
		if($seconds < 60){
			$time_left = "$seconds second(s)";
		}
		else if($seconds < 60*60){
			$minutes = round($seconds/60, 0);
			$time_left = "$minutes minute(s)";
		}
		else if($seconds < 60*60*24){
			$hours = round($seconds/60/60, 0);
			$time_left = "$hours hour(s)";
		}
		else if($seconds < 60*60*24*30){
			$days = round($seconds/60/60/24, 0);
			$time_left = "$days day(s)";
		}
		else if($seconds < 60*60*24*30*12){
			$months = round($seconds/60/60/24/30, 0);
			$time_left = "$months month(s)";
		}
		else{
			$years = round($seconds/60/60/24/30/12, 0);
			$time_left = "$years year(s)";
		}

		//tell it to the world
		$output = "This account or ip address has been banned.\n"
					."Reason: $reason \n"
					."This ban will expire in $time_left. \n"
					.'You can see more details about this ban at pr2hub.com/bans/show_record.php?ban_id='.$ban_id;

		throw new Exception($output);
	}
}





//put the user directory into more manageable sub directories. ex: 2,461,761 becomes 2,000,000/2,461,000/2,461,761
function get_user_dir($user_id) {
	$million_folder = (floor($user_id / 1000000) % 1000) * 1000000;
	$thousand_folder = $million_folder + ((floor($user_id / 1000) % 1000) * 1000);
	$hundred_folder = $user_id;

	$million_folder = number_format($million_folder);
	$thousand_folder = number_format($thousand_folder);
	$hundred_folder = number_format($hundred_folder);

	$dir = $million_folder.'/'.$thousand_folder.'/'.$hundred_folder;

	return $dir;
}




//
function limit( $num, $min, $max ) {
	if( $num < $min ) {
		$num = $min;
	}
	if( $num > $max ) {
		$num = $max;
	}
	return( $num );
}

?>
