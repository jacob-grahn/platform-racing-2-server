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
    $ref = $_SERVER['HTTP_REFERER'];
    return (strpos($ref, 'http://pr2hub.com/') === 0
        || strpos($ref, 'https://pr2hub.com/') === 0
        || strpos($ref, 'http://www.pr2hub.com/') === 0
        || strpos($ref, 'https://www.pr2hub.com/') === 0
        || strpos($ref, 'http://cdn.jiggmin.com/') === 0
        || strpos($ref, 'http://chat.kongregate.com/') === 0
        || strpos($ref, 'http://external.kongregate-games.com/gamez/') === 0
        || strpos($ref, 'https://jiggmin2.com/games/platform-racing-2') === 0
        || strpos($ref, 'http://game10110.konggames.com/games/Jiggmin/platform-racing-2') === 0
        || strpos($ref, 'http://naxxol.github.io/') === 0 // advanced LE
    );
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
    global $EMAIL_HOST, $EMAIL_USER, $EMAIL_PASS;

    $recipients = $to;

    $headers = array();
    $headers['From']    = $from;
    $headers['To']      = $to;
    $headers['Subject'] = $subject;

    // Define SMTP Parameters
    $params['host'] = $EMAIL_HOST;
    $params['port'] = '465';
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

// awards prizes to a user for various reasons on login
function award_special_parts($stats, $group, $prizes)
{
    global $hat_array, $head_array, $body_array, $feet_array, $epic_upgrades;

    // get current date for holiday parts check
    $date = date('F j');

    // heart set (valentine)
    if ($date === 'February 13' || $date === 'February 14') {
        $stats->head = add_item($head_array, 38) ? 38 : $stats->head;
        $stats->body = add_item($body_array, 38) ? 38 : $stats->body;
        $stats->feet = add_item($feet_array, 38) ? 38 : $stats->feet;
    }

    // bunny set (easter)
    $easter = date('F j', easter_date(date('Y')));
    if ($date === $easter || date('F j', time() + 86400) === $easter) {
        $stats->head = add_item($head_array, 39) ? 39 : $stats->head;
        $stats->body = add_item($body_array, 39) ? 39 : $stats->body;
        $stats->feet = add_item($feet_array, 39) ? 39 : $stats->feet;
    }

    // santa set (christmas)
    if ($date === 'December 24' || $date === 'December 25') {
        $stats->hat = add_item($hat_array, 7) ? 7 : $stats->hat;
        $stats->head = add_item($head_array, 34) ? 34 : $stats->head;
        $stats->body = add_item($body_array, 34) ? 34 : $stats->body;
        $stats->feet = add_item($feet_array, 34) ? 34 : $stats->feet;
    }

    // party hat (new year)
    if ($date === 'December 31' || $date === 'January 1') {
        $stats->hat = add_item($hat_array, 8) ? 8 : $stats->hat;
    }

    // crown for mods
    $stats->hat = $group >= 2 ? (add_item($hat_array, 6) ? 6 : $stats->hat) : $stats->hat;

    // stop if nothing else to award
    if ($prizes === false) {
        return $stats;
    }

    // contest awards
    foreach ($prizes as $award) {
        $db_field = type_to_db_field($award->type);
        $epic = strpos($award->type, 'e') === 0 ? true : false;
        $base_type = $epic === true ? substr($award->type, 1) : $award->type;
        $part = (int) $award->part;

        $array = $epic === true ? $epic_upgrades->$db_field : ${$db_field};
        $stats->$base_type = add_item($array, $part) ? $part : $stats->$base_type;
    }

    return $stats;
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
        $level_id = $row->level_id;
        $version = $row->version;
        $title = urlencode($row->title);
        $rating = round($row->rating, 2);
        $play_count = $row->play_count;
        $min_level = $row->min_level;
        $note = urlencode($row->note);
        $user_name = urlencode($row->name);
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
