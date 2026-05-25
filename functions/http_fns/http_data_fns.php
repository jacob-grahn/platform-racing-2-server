<?php
use Mailgun\Mailgun;

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
    return isset($val) ? $val : $default;
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
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
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

    return check_local();
}


// checks for trusted ref, throws an exception if not
function require_trusted_ref($action = 'perform this action', $mod = false)
{
    if (!is_trusted_ref()) {
        if ($mod === true) {
            $err = "Incorrect Referrer. $action";
        } elseif ($mod === false) {
            $err = 'It looks like you\'re using PR2 from a third-party website. '.
                "For security reasons, you may only $action from an approved site such as pr2hub.com.";
        }
        throw new Exception(trim($err));
    }
}


// send an email to a user
function send_email($to, $subject, $body)
{
    global $MAILGUN_API_KEY;

    // Instantiate the client.
    $mg = Mailgun::create($MAILGUN_API_KEY);

    // Compose and send your message.
    $mg->messages()->send(
        'mg.platformracing.com',
        [
            'from' => 'Platform Racing <postmaster@mg.platformracing.com>',
            'to' => $to,
            'subject' => $subject,
            'text' => $body
        ]
    );
}


/**
 * Quick function to use cURL instead of file_get_contents.
 *
 * @param string url The URL to query.
 *
 * @return object
 * @return string
 */
function http_get_contents($url, $headers = [])
{
    // init and set options
    $ch = curl_init();
    $opts = array(
        CURLOPT_TIMEOUT => 5,
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    );

    // apply options and process request
    curl_setopt_array($ch, $opts);
    return curl_exec($ch);
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
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}


// format the list of levels returned from the db
function format_level_list($levels)
{
    global $LEVEL_LIST_SALT;

    $ret = new stdClass();
    $ret->levels = [];
    foreach ($levels as $level) {
        $level->level_id = (int) $level->level_id;
        $level->user_id = (int) $level->user_id;
        $level->version = (int) $level->version;
        $level->title = $level->title;
        $level->rating = round($level->rating, 2);
        $level->play_count = (int) $level->play_count;
        $level->min_level = (int) $level->min_level;
        $level->note = $level->note;
        $level->user_name = $level->name;
        $level->user_group = get_group_info($level)->str;
        $level->live = (int) $level->live;
        $level->pass = isset($level->pass);
        $level->type = $level->type;
        $level->bad_hats = $level->bad_hats;
        $level->time = (int) $level->time;

        // remove unwanted vars from output
        unset($level->name, $level->power, $level->trial_mod, $level->ca);

        $ret->levels[] = $level;
    }

    $levels_str = json_encode($ret->levels);
    if (!is_empty($levels_str)) {
        $hash = md5($levels_str . $LEVEL_LIST_SALT);
        $ret->hash = $hash;
    }

    return json_encode($ret);
}


/**
 * Format a vault item for easy handling.
 *
 * @param string url The URL to query.
 *
 * @return object
 * @return string
 */
function format_vault_item($item)
{
    unset($item->active, $item->placement);
    $item->price = (int) $item->price;

    // sale
    $sale = new stdClass();
    $sale->active = (bool) (int) $item->sale;
    if ($sale->active) {
        $sale->value = (int) $item->sale_value;
        $sale->expires = (int) $item->sale_expires;
    }
    $item->sale = $sale;
    unset($item->sale_value, $item->sale_expires);

    return $item;
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


// makes a banned notice
function make_banned_notice($ban)
{
    $ban_id = $ban->ban_id;
    $expire_time = $ban->expire_time;
    $reason = htmlspecialchars($ban->reason, ENT_QUOTES);

    // figure out what the best way to say this is
    $time_left = format_duration($expire_time - time());

    // tell it to the world
    $ban_link = urlify("https://pr2hub.com/bans/show_record.php?ban_id=$ban_id", 'here');
    $dispute_link = urlify("https://jiggmin2.com/forums/showthread.php?tid=110", 'dispute it');
    $banned = $ban->scope === 's' ? 'socially banned' : 'banned';
    $output = "This account or IP address has been $banned.\n".
        "Reason: $reason. \n".
        "This ban will expire in $time_left. \n".
        "You can see more details about this ban $ban_link. \n\n".
        "If you feel that this ban is unjust, you can $dispute_link.";
    
    return $output;
}
