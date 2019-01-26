<?php


// returns the active ban if the specified user or ip is banned
function query_if_banned($pdo, $user_id, $ip)
{
    if (!function_exists('ban_select')) {
        require_once QUERIES_DIR . '/bans.php';
    }
    $ban = isset($user_id) && $user_id != 0 ? ban_select_active_by_user_id($pdo, $user_id) : false; // user_id
    $ban = !$ban && isset($ip) ? ban_select_active_by_ip($pdo, $ip) : $ban; // ip if user_id isn't found
    return $ban;
}


// formats a length of time
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


// builds a client-friendly url
function urlify($link, $disp, $color = '#0000FF', $bt_replace = true)
{
    $link = htmlspecialchars($link, ENT_QUOTES);
    $disp = htmlspecialchars($disp, ENT_QUOTES);

    // replace backticks with html code to prevent errors
    if ($bt_replace === true) {
        $link = str_replace('`', '&#96;', $link);
        $disp = str_replace('`', '&#96;', $disp);
    }

    // return url
    return "<a href='$link' target='_blank'><u><font color='$color'>$disp</font></u></a>";
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


// checks if a string is empty (includes a variety of checks)
function is_empty($str, $incl_zero = true)
{
    if (strlen(trim($str)) === 0 || !isset($str)) { // if the string length is 0 or it isn't set
        return true;
    } elseif ($incl_zero === true && empty($str) && $str != '0') { // if the string is empty and not 0, it's empty
        return true;
    } elseif (empty($str)) {
        return true;
    } else {
        return false;
    }
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

    return $count;
}


// output to command line or log
function output($str)
{
    echo "* $str\n";
}
