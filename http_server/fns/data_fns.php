<?php

// requests from a flash client will include this header (only used in logout.php)
function is_from_game()
{
    $req_with = default_server("HTTP_X_REQUESTED_WITH", "");
    $ref = default_server("HTTP_REFERER");

    // let people type in the url manually
    if (is_empty($ref)) {
        return true;
    }

    // does the request originate from the flash player?
    return strpos($req_with, "ShockwaveFlash/") === 0;
}

// check if player has an epic color option for a part
function test_epic($color, $arr_str, $part)
{
    $ret = -1;
    if (isset($arr_str) && strlen($arr_str) > 0) {
        $arr = explode(',', $arr_str);
        if (array_search($part, $arr) !== false || array_search('*', $arr) !== false) {
            $ret = $color;
        }
    }
    return $ret;
}


// add part to part array if not already present
function add_item(&$arr, $item)
{
    if (array_search($item, $arr) === false) {
        $arr[] = $item;
        return true;
    } else {
        return false;
    }
}


// tries to find a variable in various request arrays; uses default if not found
function find($str, $default = null, $cookie = true)
{
    if (isset($_COOKIE[$str]) && $cookie === true) {
        $val = $_COOKIE[$str];
        return $val;
    }
    if (isset($_POST[$str])) {
        $val = $_POST[$str];
        return $val;
    }
    if (isset($_GET[$str])) {
        $val = $_GET[$str];
        return $val;
    }
    if (!isset($val)) {
        $val = $default;
        return $val;
    }
}


// placeholder function to appease files still using
function find_no_cookie($str, $default = null)
{
    return find($str, $default, false);
}


// gets a variable from $_GET array, or default if it doesn't exist
function default_get($str, $default = null)
{
    if (isset($_GET[$str])) {
        return $_GET[$str];
    } else {
        return $default;
    }
}


// gets a variable from $_POST array, or default if it doesn't exist
function default_post($str, $default = null)
{
    if (isset($_POST[$str])) {
        return $_POST[$str];
    } else {
        return $default;
    }
}


// gets a variable from $_SERVER array, or default if it doesn't exist
function default_server($str, $default = null)
{
    if (isset($_SERVER[$str])) {
        return $_SERVER[$str];
    } else {
        return $default;
    }
}


// gets any variable, uses default if it doesn't exist
function default_val($val, $default = null)
{
    if (is_empty($val)) {
        return $default;
    } else {
        return $val;
    }
}


// time formatting
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


// get a user's IP address
function get_ip()
{
    return $_SERVER["REMOTE_ADDR"];
}


// check for a string inside another variable, echoing yes/no if found/not
function check_value($value, $check_for, $yes = 'yes', $no = 'no')
{
    if ($value == $check_for) {
        return $yes;
    } else {
        return $no;
    }
}


// checks if a string is empty (includes a variety of checks)
function is_empty($str, $incl_zero = true)
{
    if (strlen(trim($str)) === 0 || !isset($str)) { // if the string length is 0 or it isn't set
        return true;
    }
    if ($incl_zero === true) { // if the string is empty and not 0, it's empty
        if (empty($str) && $str != '0') {
            return true;
        }
    } else {
        if (empty($str)) {
            return true;
        }
    }

    // you're still here? must mean $str isn't empty
    return false;
}


// referrer check
function is_trusted_ref()
{
    $ref = $_SERVER['HTTP_REFERER'];
    return (strpos($ref, 'http://pr2hub.com/') === 0
        || strpos($ref, 'https://pr2hub.com/') === 0
        || strpos($ref, 'http://cdn.jiggmin.com/') === 0
        || strpos($ref, 'http://chat.kongregate.com/') === 0
        || strpos($ref, 'http://external.kongregate-games.com/gamez/') === 0
        || strpos($ref, 'https://jiggmin2.com/games/platform-racing-2') === 0
        || strpos($ref, 'http://game10110.konggames.com/games/Jiggmin/platform-racing-2') === 0
    );
}


// checks for trusted ref, throws an exception if not
function require_trusted_ref($action = 'perform this action', $mod = false)
{
    if (!is_trusted_ref() && $mod === false) {
        throw new Exception(
            "It looks like you're using PR2 from a third-party website. ".
            "For security reasons, you may only $action from an approved site such as pr2hub.com."
        );
    } elseif (!is_trusted_ref() && $mod === true) {
        $err = "Incorrect Referrer. $action";
        throw new Exception(trim($err));
    }
}


// tests to see if a string contains obscene words
function is_obscene($str)
{
    $str = strtolower($str);
    $bad_array = [
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
    ];
    $obsene = false;
    foreach ($bad_array as $bad) {
        if (strpos($str, $bad) !== false) {
            $obsene = true;
            break;
        }
    }
    return $obsene;
}


// checks if an email address is valid
function valid_email($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}


// returns your account if you are a moderator
function check_moderator($pdo, $check_ref = true, $min_power = 2)
{
    if ($check_ref === true) {
        require_trusted_ref('', true);
    }

    $user_id = token_login($pdo);
    $user = user_select_mod($pdo, $user_id, true);

    if (!$user || $user->power < $min_power) {
        throw new Exception('You lack the power to access this resource.');
    }

    return $user;
}


// returns true if you are logged in as a moderator, false if you are not
function is_moderator($pdo, $check_ref = true)
{
    $is_mod = false;
    try {
        check_moderator($pdo, $check_ref);
        $is_mod = true;
    } catch (Exception $e) {
        // TODO: remove this later
    }

    return $is_mod;
}


// returns true if you are logged in as an admin, false if you are not
function is_admin($pdo, $check_ref = true)
{
    $is_admin = false;
    try {
        check_moderator($pdo, $check_ref, 3);
        $is_admin = true;
    } catch (Exception $e) {
        // TODO: remove this later
    }

    return $is_admin;
}


// format the list of levels returned from the db
function format_level_list($levels)
{
    global $LEVEL_LIST_SALT;

    $num = 0;
    $str = '';
    foreach ($levels as $row) {
        $level_id = $row->level_id;
        $version = $row->version;
        $title = urlencode(htmlspecialchars($row->title));
        $rating = round($row->rating, 2);
        $play_count = $row->play_count;
        $min_level = $row->min_level;
        $note = urlencode(htmlspecialchars($row->note));
        $user_name = urlencode(htmlspecialchars($row->name));
        $group = $row->power;
        $live = $row->live;
        $pass = isset($row->pass);
        $type = $row->type;

        if ($num > 0) {
            $str .= "&";
        }
        $str .= "levelID$num=$level_id"
        ."&version$num=$version"
        ."&title$num=$title"
        ."&rating$num=$rating"
        ."&playCount$num=$play_count"
        ."&minLevel$num=$min_level"
        ."&note$num=$note"
        ."&userName$num=$user_name"
        ."&group$num=$group"
        ."&live$num=$live"
        ."&pass$num=$pass"
        ."&type$num=$type";
        $num++;
    }

    if ($str != '') {
        $hash = md5($str . $LEVEL_LIST_SALT);
        $str .= '&hash='.$hash;
    }

    return $str;
}


// replace naughty words with slightly less naughty ones
function filter_swears($str)
{
    $damnArray = array("dang", "dingy-goo", "condemnation");
    $fuckArray = array("fooey", "fingilly", "funk-master", "freak monster", "jiminy cricket");
    $shitArray = array("shoot", "shewet");
    $niggerArray = array("someone cooler than me", "ladies magnet", "cooler race");
    $bitchArray = array("cooler gender", "female dog");

    $str = str_replace('damn', $damnArray[array_rand($damnArray)], $str);
    $str = str_replace('fuck', $fuckArray[array_rand($fuckArray)], $str);
    $str = str_replace('nigger', $niggerArray[array_rand($niggerArray)], $str);
    $str = str_replace('nigga', $niggerArray[array_rand($niggerArray)], $str);
    $str = str_replace('spic ', $niggerArray[array_rand($niggerArray)], $str);
    $str = str_replace('shit', $shitArray[array_rand($shitArray)], $str);
    $str = str_replace('bitch', $bitchArray[array_rand($bitchArray)], $str);
    $str = str_replace('cunt', $bitchArray[array_rand($bitchArray)], $str);

    return( $str );
}


// slow down a bit, yo.
function rate_limit($key, $interval, $max, $error = 'Slow down a bit, yo.')
{
    $unit = round(time() / $interval);
    $key .= '-'.$unit;
    $count = 0;

    if (apcu_exists($key)) {
        $count = apcu_fetch($key);
        if ($count >= $max) {
            throw new Exception($error);
        }
    }

    $count++;
    apcu_store($key, $count, $interval);

    return($count);
}
