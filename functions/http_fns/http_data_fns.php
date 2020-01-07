<?php


// -- AUTH -- \\

// generates a login token
function get_login_token($user_id)
{
    return $user_id . '-' . random_str(30);
}


// -- SERVER -- \\

// gets a variable from $_GET array, or default if it doesn't exist
function default_get($str, $default = null)
{
    return isset($_GET[$str]) ? $_GET[$str] : $default;
}


// gets a variable from $_POST array, or default if it doesn't exist
function default_post($str, $default = null)
{
    return isset($_POST[$str]) ? $_POST[$str] : $default;
}


// gets any variable, uses default if it doesn't exist
function default_val($val, $default = null)
{
    return is_empty($val) ? $default : $val;
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


// get a user's IP address
function get_ip()
{
    return $_SERVER["REMOTE_ADDR"];
}


// referrer check
function is_trusted_ref()
{
    global $TRUSTED_REFS;

    $ref = (string) default_val(@$_SERVER['HTTP_REFERER'], '');
    foreach ($TRUSTED_REFS as $trusted) {
        if (@strpos($ref, $trusted) === 0 || $ref === $trusted) {
            return true;
        }
    }

    return false;
}


// checks for trusted ref, throws an exception if not
function require_trusted_ref($action = 'perform this action', $mod = false)
{
    if (!is_trusted_ref() && $mod === false) {
        $err = 'It looks like you\'re using PR2 from a third-party website. '.
            "For security reasons, you may only $action from an approved site such as pr2hub.com.";
        throw new Exception($err);
    } elseif (!is_trusted_ref() && $mod === true) {
        $err = "Incorrect Referrer. $action";
        throw new Exception(trim($err));
    }
}


// send an email to a user
function send_email($from, $to, $subject, $body)
{
    global $EMAIL_HOST, $EMAIL_PORT, $EMAIL_USER, $EMAIL_PASS;

    $recipients = $to;

    $headers = array();
    $headers['From']    = $from;
    $headers['To']      = $to;
    $headers['Subject'] = $subject;

    // Define SMTP Parameters
    $params['host'] = $EMAIL_HOST;
    $params['port'] = $EMAIL_PORT;
    $params['auth'] = 'PLAIN';
    $params['username'] = $EMAIL_USER;
    $params['password'] = $EMAIL_PASS;

    // Create the mail object using the Mail::factory method
    $mail_object = Mail::factory('smtp', $params);

    // Send the message
    $mail_object->send($recipients, $headers, $body);
}


// -- DATA HANDLERS -- \\

// sorts by $obj->time
function sort_by_obj_time($a, $b)
{
    if ($a->time === $b->time) {
        return 0;
    }

    return $a->time > $b->time ? -1 : 1;
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


// converts part type to a db field name
function type_to_db_field($type)
{
    $type = strtolower($type);

    if ($type === 'hat') {
        return 'hat_array';
    } elseif ($type === 'head') {
        return 'head_array';
    } elseif ($type === 'body') {
        return 'body_array';
    } elseif ($type === 'feet') {
        return 'feet_array';
    } elseif ($type === 'ehat') {
        return 'epic_hats';
    } elseif ($type === 'ehead') {
        return 'epic_heads';
    } elseif ($type === 'ebody') {
        return 'epic_bodies';
    } elseif ($type === 'efeet') {
        return 'epic_feet';
    } else {
        throw new Exception('Unknown type.');
    }
}


// check for a string inside another variable, echoing yes/no if found/not
function check_value($value, $check_for, $yes = 'yes', $no = 'no')
{
    return $value == $check_for ? $yes : $no;
}


// checks if an email address is valid
function valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
}


// format the list of levels returned from the db
function format_level_list($levels)
{
    global $LEVEL_LIST_SALT;

    $num = 0;
    $str = '';
    foreach ($levels as $row) {
        $level_id = (int) $row->level_id;
        $version = (int) $row->version;
        $title = urlencode($row->title);
        $rating = round($row->rating, 2);
        $play_count = (int) $row->play_count;
        $min_level = (int) $row->min_level;
        $note = urlencode($row->note);
        $user_name = urlencode($row->name);
        $group = (int) $row->power;
        $live = (int) $row->live;
        $pass = isset($row->pass);
        $type = $row->type;
        $time = (int) $row->time;

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
        ."&type$num=$type"
        ."&time$num=$time";
        $num++;
    }

    if (!is_empty($str)) {
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

    return $str;
}
