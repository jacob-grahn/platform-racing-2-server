<?php


// returns the active ban of both game/social if the specified user/ip is banned
function query_if_banned($pdo, $user_id, $ip)
{
    if (!function_exists('ban_select')) {
        require_once QUERIES_DIR . '/bans.php';
    }
    $bans = isset($user_id) && $user_id != 0 ? bans_select_active_by_user_id($pdo, $user_id) : false; // user_id
    $bans = !$bans && isset($ip) ? bans_select_active_by_ip($pdo, $ip) : $bans; // ip if user_id isn't found
    return $bans;
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


// get exp required for ranking
function exp_required_for_ranking($rank)
{
    // exp constant (ranks 1-130)
    $exp_req = [
        0,
        1,
        38,
        47,
        59,
        73,
        92,
        114,
        143,
        179,
        224,
        279,
        349,
        437,
        546,
        682,
        853,
        1066,
        1332,
        1665,
        2082,
        2602,
        3253,
        4066,
        5082,
        6353,
        7941,
        9926,
        12408,
        15510,
        19387,
        24234,
        30292,
        37865,
        47332,
        59165,
        73956,
        92445,
        115556,
        144445,
        180556,
        225695,
        282119,
        352648,
        440810,
        551013,
        688766,
        860958,
        1076197,
        1345247,
        1681558,
        2101948,
        2627435,
        3284293,
        4105367,
        5131708,
        6414635,
        8018294,
        10022868,
        12528585,
        15660731,
        19575913,
        24469892,
        30587365,
        38234206,
        47792757,
        59740947,
        74676183,
        93345229,
        116681536,
        145851921,
        182314901,
        227893626,
        284867032,
        356083790,
        445104738,
        556380923,
        695476153,
        869345192,
        1086681489,
        1358351862,
        1697939827,
        2122424784,
        2653030980,
        3316288725,
        4145360906,
        5181701133,
        6477126416,
        8096408020,
        10120510026,
        12650637532,
        15813296915,
        19766621144,
        24708276429,
        30885345537,
        38606681921,
        48258352401,
        60322940502,
        75403675627,
        94254594534,
        117818243167,
        147272803959,
        184091004949,
        230113756186,
        287642195232,
        359552744040,
        449440930050,
        561801162563,
        702251453204,
        877814316505,
        1097267895631,
        1371584869539,
        1714481086923,
        2143101358654,
        2678876698318,
        3348595872897,
        4185744841122,
        5232181051402,
        6540226314253,
        8175282892816,
        10219103616020,
        12773879520025,
        15967349400031,
        19959186750038,
        24948983437548,
        31186229296935,
        38982786621168,
        48728483276461,
        60910604095576,
        76138255119470,
        95172818899337
    ];

    if (!is_numeric($rank) || $rank < 0 || $rank >= count($exp_req)) {
        $rank = count($exp_req) - 1;
    }

    return $exp_req[$rank];
}
