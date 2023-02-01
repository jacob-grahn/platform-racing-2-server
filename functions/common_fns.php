<?php


// returns the active ban of both game/social if the specified user/ip is banned
function query_if_banned($pdo, $user_id, $ip)
{
    if (!function_exists('ban_select')) {
        require_once QUERIES_DIR . '/bans.php';
    }
    $bans = !empty($user_id) && !empty($ip) ? bans_select_active($pdo, $user_id, $ip) : false; // both
    $bans = !$bans && !empty($user_id) ? bans_select_active_by_user_id($pdo, $user_id) : $bans; // user_id
    $bans = !$bans && !empty($ip) ? bans_select_active_by_ip($pdo, $ip) : $bans; // ip if user_id isn't found
    return $bans;
}


// throw an exception or returns the most recent/severe ban (game first) if the user is banned
function check_if_banned($pdo, $user_id, $ip, $scope = 'b', $throw_exception = true)
{
    if ($scope === 'n') {
        return;
    }

    $bans = query_if_banned($pdo, $user_id, $ip);
    if ($bans !== false) {
        foreach ($bans as $ban) {
            if ($ban !== false && ($scope === $ban->scope || $scope === 'b')) { // g will supercede s if scope is b
                if ($throw_exception) {
                    $output = make_banned_notice($ban);
                    throw new Exception($output);
                }
                return $ban;
            }
        }
    }
    return false;
}


// formats a length of time
function format_duration($seconds)
{
    $neg = false;
    if ($seconds < 0) {
        $neg = true;
        $seconds = abs($seconds);
    }
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
    return $time_left . ($neg ? ' ago' : '');
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


/**
 * Makes a client-friendly group string. (Does not check for special user!)
 * 
 * @param object user User data. (Must include group/power. Should include trial_mod, ca. Can include temp_mod.)
 * 
 * @return string Group string (not checked for special user).
 */
function make_group_str($user)
{
    $group = (int) (isset($user->group) ? $user->group : $user->power);
    $group2 = get_second_group($user);
    $group2 = $group2 < 0 ? '' : ",$group2";
    return "$group$group2";
}


/**
 * Gets a user's secondary group value. Does not check for special user!
 * Makes a client-friendly group string. Does not check for special user!
 * 
 * @param object user User data (Must include group/power. Should include trial_mod, ca. Can include temp_mod.)
 * 
 * @return int Secondary group number (not checked for special user).
 */
function get_second_group($user)
{
    $group = (int) (isset($user->group) ? $user->group : $user->power);
    if ($group === 1) {
        return (int) $user->ca;
    } elseif ($group === 2) {
        if (isset($user->temp_mod) && $user->temp_mod) {
            return 0;
        } elseif ((bool) (int) $user->trial_mod) {
            return 1;
        } else {
            return 2;
        }
    }
    return -1;
}


/**
 * Builds group information from a user object.
 * 
 * @param object user User data (Must include group/power. Should include user_id, trial_mod, ca. Can include temp_mod.)
 * 
 * @return int Secondary group number (not checked for special user).
 */
function get_group_info($user)
{
    global $group_names, $group_colors, $special_ids;

    // get data from group string
    @list($group, $suppl) = @explode(',', make_group_str($user));

    // data validation
    $group_default = $group == 2 ? 2 : 0;
    if (is_null($suppl)) {
        $suppl = $group_default;
    }
    $group = $group > count($group_names) - 1 ? 0 : max(0, (int) $group);
    $suppl = $suppl > count($group_names[$group]) - 1 ? $group_default : max(0, (int) $suppl);

    // special
    $special_user = in_array(@$user->user_id, $special_ids);

    // return data
    $ret = new stdClass();
    $ret->num = $group;
    $ret->num2 = !$special_user ? $suppl : '*';
    $ret->name = $group_names[$group][$suppl];
    $ret->color = !$special_user ? $group_colors[$group][$suppl] : '83C141';
    $ret->str = "$group,$ret->num2";
    return $ret;
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
    } elseif ($incl_zero && empty($str) && $str != '0') { // if the string is empty and not 0, it's empty
        return true;
    } elseif (!$incl_zero && empty($str)) {
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


/**
 * Converts a part type and ID to its full corresponding name.
 *
 * @param string type Part type (must not have an "e" for epic in front).
 * @param int id Part ID.
 *
 * @return string
 */
function to_part_name($type, $id)
{
    // hats
    $hat_names_array = [
        2 => 'EXP',
        3 => 'Kong',
        4 => 'Propeller',
        5 => 'Cowboy',
        6 => 'Crown',
        7 => 'Santa',
        8 => 'Party',
        9 => 'Top',
        10 => 'Jump-Start',
        11 => 'Moon',
        12 => 'Thief',
        13 => 'Jigg',
        14 => 'Artifact',
        15 => 'Jellyfish',
        16 => 'Cheese'
    ];

    // heads
    $head_names_array = [
        1 => 'Classic',
        2 => 'Tired',
        3 => 'Smiler',
        4 => 'Flower',
        5 => 'Classic (Female)',
        6 => 'Goof',
        7 => 'Downer',
        8 => 'Balloon',
        9 => 'Worm',
        10 => 'Unicorn',
        11 => 'Bird',
        12 => 'Sun',
        13 => 'Candy',
        14 => 'Invisible',
        15 => 'Football Helmet',
        16 => 'Basketball',
        17 => 'Stick',
        18 => 'Cat',
        19 => 'Elephant',
        20 => 'Ant',
        21 => 'Astronaut',
        22 => 'Alien',
        23 => 'Dino',
        24 => 'Armor',
        25 => 'Fairy',
        26 => 'Gingerbread',
        27 => 'Bubble',
        28 => 'Wise King',
        29 => 'Wise Queen',
        30 => 'Sir',
        31 => 'Very Invisible',
        32 => 'Taco',
        33 => 'Slender',
        34 => 'Santa',
        35 => 'Frost Djinn',
        36 => 'Reindeer',
        37 => 'Crocodile',
        38 => 'Valentine',
        39 => 'Bunny',
        40 => 'Gecko',
        41 => 'Bat',
        42 => 'Sea',
        43 => 'Brew',
        44 => 'Jack-o\'-Lantern',
        45 => 'Star',
        46 => 'Snowman',
        47 => 'Blobfish',
        48 => 'Turkey',
        49 => 'Dog',
        50 => 'Gladiator'
    ];

    // bodies
    $body_names_array = [
        1 => 'Classic',
        2 => 'Strap',
        3 => 'Dress',
        4 => 'Pec',
        5 => 'Gut',
        6 => 'Collar',
        7 => 'Miss PR2',
        8 => 'Belt',
        9 => 'Snake',
        10 => 'Bird',
        11 => 'Invisible',
        12 => 'Bee',
        13 => 'Stick',
        14 => 'Cat',
        15 => 'Car',
        16 => 'Elephant',
        17 => 'Ant',
        18 => 'Astronaut',
        19 => 'Alien',
        20 => 'Galaxy',
        21 => 'Bubble',
        22 => 'Dino',
        23 => 'Armor',
        24 => 'Fairy',
        25 => 'Gingerbread',
        26 => 'Wise King',
        27 => 'Wise Queen',
        28 => 'Sir',
        29 => 'Fred',
        30 => 'Very Invisible',
        31 => 'Taco',
        32 => 'Slender',
        34 => 'Santa',
        35 => 'Frost Djinn',
        36 => 'Reindeer',
        37 => 'Crocodile',
        38 => 'Valentine',
        39 => 'Bunny',
        40 => 'Gecko',
        41 => 'Bat',
        42 => 'Sea',
        43 => 'Brew',
        45 => 'Christmas Tree',
        46 => 'Snowman',
        48 => 'Turkey',
        49 => 'Dog',
        50 => 'Gladiator'
    ];

    // feet
    $feet_names_array = [
        1 => 'Classic',
        2 => 'Heel',
        3 => 'Loafer',
        4 => 'Soccer',
        5 => 'Magnet',
        6 => 'Tiny',
        7 => 'Sandal',
        8 => 'Bare',
        9 => 'Nice',
        10 => 'Bird',
        11 => 'Invisible',
        12 => 'Stick',
        13 => 'Cat',
        14 => 'Car',
        15 => 'Elephant',
        16 => 'Ant',
        17 => 'Astronaut',
        18 => 'Alien',
        19 => 'Galaxy',
        20 => 'Dino',
        21 => 'Armor',
        22 => 'Fairy',
        23 => 'Gingerbread',
        24 => 'Wise King',
        25 => 'Wise Queen',
        26 => 'Sir',
        27 => 'Very Invisible',
        28 => 'Bubble',
        29 => 'Taco',
        30 => 'Slender',
        34 => 'Santa',
        35 => 'Frost Djinn',
        36 => 'Reindeer',
        37 => 'Crocodile',
        38 => 'Valentine',
        39 => 'Bunny',
        40 => 'Gecko',
        41 => 'Bat',
        42 => 'Sea',
        43 => 'Brew',
        45 => 'Present',
        46 => 'Snowman',
        48 => 'Turkey',
        49 => 'Dog',
        50 => 'Gladiator'
    ];

    $lookup = [
        'hat' => $hat_names_array,
        'head' => $head_names_array,
        'body' => $body_names_array,
        'feet' => $feet_names_array
    ];

    return $lookup[$type][$id];
}


/**
 * Validates a prize from a given type and ID.
 *
 * @param string type Part type.
 * @param int id Part ID.
 * @param bool incl_exp Determines if EXP is a valid "part" type.
 *
 * @throws Exception if an invalid part type or ID is specified.
 * @return string
 */
function validate_prize($type, $id, $incl_exp = true)
{
    $type = htmlspecialchars(strtolower($type));
    $id = (int) $id;
    $type_array = ['hat', 'head', 'body', 'feet', 'ehat', 'ehead', 'ebody', 'efeet'];
    $incl_exp = (bool) $incl_exp;

    // check for a valid prize type
    if (!in_array($type, $type_array) && ($incl_exp ? $type !== 'exp' : true)) {
        throw new Exception("Invalid prize type ($type) specified.");
    }

    // preserve epicness
    $is_epic = $type === 'ehat' || $type === 'ehead' || $type === 'ebody' || $type === 'efeet';

    // check for a valid hat id
    if ($type === 'hat' || $type === 'ehat') {
        $type = 'hat';
        if ($id < 2 || $id > 16) {
            throw new Exception("Invalid hat ID ($id) specified.");
        }
    }

    // check for a valid head id
    if ($type === 'head' || $type === 'ehead') {
        $type = 'head';
        if ($id < 1 || $id > 50) {
            throw new Exception("Invalid head ID ($id) specified.");
        }
    }

    // check for a valid body id
    if ($type === 'body' || $type === 'ebody') {
        $type = 'body';
        if ($id < 1 || $id > 50 || $id === 33 || $id === 44 || $id === 47) {
            throw new Exception("Invalid body ID ($id) specified.");
        }
    }

    // check for a valid feet id
    if ($type === 'feet' || $type === 'efeet') {
        $type = 'feet';
        if ($id < 1 || $id > 50 || ($id >= 31 && $id <= 33) || $id === 44 || $id === 47) {
            throw new Exception("Invalid feet ID ($id) specified.");
        }
    }

    // check for a valid amount of exp
    if ($type === 'exp' && $id <= 0) {
        throw new Exception("Invalid amount of EXP ($id) specified.");
    }

    // if we got here, it means no exceptions were caught -- return our data
    $reply = new stdClass();
    $reply->type = $type;
    $reply->id = $id;
    $reply->epic = $is_epic;
    return $reply;
}
