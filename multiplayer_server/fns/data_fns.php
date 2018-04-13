<?php


//--- tries to pull a variable from the $_GET array. If it is not present, the default is used. ---------------
function get($str, $default)
{
    $val = $_GET[$str];
    if (!isset($val)) {
        $val = $default;
    }
    return $val;
}



//--- looks for a variable in the url and form data. If none are found, retun the default.
function find_variable($string, $default)
{
    $variable = $_POST[$string];
    if (!isset($variable)) {
        $variable = $_GET[$string];
    }
    if (!isset($variable)) {
        $variable = $default;
    }
    return $variable;
}



//--- tests if a value is empty using a variety of functions --------------------------------
function is_empty($str, $incl_zero = true)
{
    /*
    $incl_zero: checks if the user wants to include the string "0" in the empty check.
    If not, empty($str) will make this function return true.
    */

    // if the string length is 0, it's empty
    if (strlen(trim($str)) === 0) {
        return true;
    }
    // if the string isn't set, it's empty
    if (!isset($str)) {
        return true;
    }
    // if the string is empty and not 0, it's empty
    if ($incl_zero) {
        if (empty($str) && $str != '0') {
            return true;
        }
    } // if the string is empty, it's empty
    else {
        if (empty($str)) {
            return true;
        }
    }
    // you're still here? must mean $str isn't empty
    return false;
}



//--- tests to see if a string contains obscene words ---------------------------------------
function is_obscene($str)
{
    $str = strtolower($str);
    $bad_array = array(
        'fuck',
        'shit',
        'nigger',
        'nigga',
        'whore',
        'bitch',
        'slut',
        'cunt',
        'cock',
        'dick',
        'penis',
        'damn',
        'spic'
    );
    $obscene = false;
    foreach ($bad_array as $bad) {
        if (strpos($str, $bad) !== false) {
            $obscene = true;
            break;
        }
    }
    return $obscene;
}


//---
function remove_resource($_target)
{

    //file?
    if (is_file($_target)) {
        if (is_writable($_target)) {
            if (@unlink($_target)) {
                return true;
            }
        }

        return false;
    }

    //dir?
    if (is_dir($_target)) {
        if (is_writeable($_target)) {
            foreach (new DirectoryIterator($_target) as $_res) {
                if ($_res->isDot()) {
                    unset($_res);
                    continue;
                }

                if ($_res->isFile()) {
                    remove_resource($_res->getPathName());
                } elseif ($_res->isDir()) {
                    remove_resource($_res->getRealPath());
                }

                unset($_res);
            }

            if (@rmdir($_target)) {
                return true;
            }
        }

        return false;
    }
}


function format_duration($seconds)
{
    if ($seconds < 60) {
        $time_left = "$seconds second";
        if ($seconds != 1) {
            $time_left .= 's';
        }
    } elseif ($seconds < 60*60) {
        $minutes = round($seconds/60, 0);
        $time_left = "$minutes minute";
        if ($minutes != 1) {
            $time_left .= 's';
        }
    } elseif ($seconds < 60*60*24) {
        $hours = round($seconds/60/60, 0);
        $time_left = "$hours hour";
        if ($hours != 1) {
            $time_left .= 's';
        }
    } elseif ($seconds < 60*60*24*30) {
        $days = round($seconds/60/60/24, 0);
        $time_left = "$days day";
        if ($days != 1) {
            $time_left .= 's';
        }
    } elseif ($seconds < 60*60*24*30*12) {
        $months = round($seconds/60/60/24/30, 0);
        $time_left = "$months month";
        if ($months != 1) {
            $time_left .= 's';
        }
    } else {
        $years = round($seconds/60/60/24/30/12, 0);
        $time_left = "$years year";
        if ($years != 1) {
            $time_left .= 's';
        }
    }
    return $time_left;
}





//put the user directory into more manageable sub directories. ex: 2,461,761 becomes 2,000,000/2,461,000/2,461,761
function get_user_dir($user_id)
{
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
function limit($num, $min, $max)
{
    if ($num < $min) {
        $num = $min;
    }
    if ($num > $max) {
        $num = $max;
    }
    return( $num );
}
