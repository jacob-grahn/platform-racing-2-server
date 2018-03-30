<?php

require_once __DIR__ . '/random_str.php';


//--- checks if a login is valid -----------------------------------------------------------
require_once __DIR__ . '/../queries/users/user_apply_temp_pass.php';

// user selection queries
require_once __DIR__ . '/../queries/users/user_select.php'; // select user (no hashes) by id
require_once __DIR__ . '/../queries/users/user_select_by_name.php'; // select user (no hashes) by name
require_once __DIR__ . '/../queries/users/user_select_full_by_name.php'; // select full user (with hashes) by name
require_once __DIR__ . '/../queries/users/name_to_id.php'; // name -> id
require_once __DIR__ . '/../queries/users/id_to_name.php'; // id -> name

function pass_login($pdo, $name, $password)
{

    // get their ip
    $ip = get_ip();

    // error check
    if (empty($name) || !is_string($password) || $password == '') {
        throw new Exception('You must enter a name and a password.');
    }
    if (strlen($name) < 2) {
        throw new Exception('Your name must be at least 2 characters long.');
    }
    if (strlen($name) > 20) {
        throw new Exception('Your name can not be more than 20 characters long.');
    }

    // load the user row
    $user = user_select_full_by_name($pdo, $name);

    // check the password
    if (!password_verify(sha1($password), $user->pass_hash)) {
        if (password_verify(sha1($password), $user->temp_pass_hash)) {
            user_apply_temp_pass($pdo, $user->user_id);
        } else {
            throw new Exception('That username / password combination was not found.');
        }
    }

    // don't save hashes to memory
    unset($user->pass_hash);
    unset($user->temp_pass_hash);

    // check to see if they're banned
    check_if_banned($pdo, $user->user_id, $ip);

    // done
    return $user;
}



// login using a token
require_once __DIR__ . '/../queries/tokens/token_select.php';

function token_login($pdo, $use_cookie = true, $suppress_error = false)
{

    $rec_token = find_no_cookie('token');
    if (isset($rec_token) && $rec_token != '') {
        $token = $rec_token;
    } elseif ($use_cookie && isset($_COOKIE['token']) && $_COOKIE['token'] != '') {
        $token = $_COOKIE['token'];
    }

    if (!isset($token)) {
        if ($suppress_error == false) {
            throw new Exception('Could not find a valid login token. Please log in again.');
        } else {
            return false;
        }
    }

    $token_row = token_select($pdo, $token);
    $user_id = $token_row->user_id;

    $ip = get_ip();
    check_if_banned($pdo, $user_id, $ip);

    return $user_id;
}

// part/epic upgrade queries
require_once __DIR__ . '/../queries/epic_upgrades/epic_upgrades_select.php';
require_once __DIR__ . '/../queries/epic_upgrades/epic_upgrades_update_field.php';
require_once __DIR__ . '/../queries/pr2/pr2_select.php';
require_once __DIR__ . '/../queries/pr2/pr2_update_part_array.php';
require_once __DIR__ . '/../queries/part_awards/part_awards_insert.php';

// award hats
function award_part($pdo, $user_id, $type, $part_id, $ensure = true)
{
    $ret = false;
    $epicUpgrade = false;

    if (strpos($type, 'e') === 0) {
        $epicUpgrade = true;
    }

    // get existing parts
    try {
        if ($epicUpgrade) {
            $row = epic_upgrades_select($pdo, $user_id);
        } else {
            $row = pr2_select($pdo, $user_id);
        }
        $field = type_to_db_field($type);
        $str_array = $row->{$field};
    } catch (Exception $e) {
        $str_array = '';
    }

    // make a copy so we can check if it changes from the original
    $str_array_new = $str_array;

    if ($ensure) {
        part_awards_insert($pdo, $user_id, $type, $part_id);
    }
    $str_array_new = append_to_str_array($str_array_new, $part_id);

    if ($str_array_new != $str_array) {
        if ($epicUpgrade) {
            epic_upgrades_update_field($pdo, $user_id, $type, $str_array_new);
        } else {
            pr2_update_part_array($pdo, $user_id, $type, $str_array_new);
        }
        $ret = true;
    }

    return $ret;
}


function type_to_db_field($type)
{
    if ($type == 'hat') {
        $field = 'hat_array';
    } elseif ($type == 'head') {
        $field = 'head_array';
    } elseif ($type == 'body') {
        $field = 'body_array';
    } elseif ($type == 'feet') {
        $field = 'feet_array';
    } elseif ($type == 'eHat') {
        $field = 'epic_hats';
    } elseif ($type == 'eHead') {
        $field = 'epic_heads';
    } elseif ($type == 'eBody') {
        $field = 'epic_bodies';
    } elseif ($type == 'eFeet') {
        $field = 'epic_feet';
    } else {
        throw new Exception('Unknown type');
    }
    return( $field );
}


function append_to_str_array($str_arr, $val)
{
    if (!isset($str_arr) || $str_arr == ',') {
        $str_arr = '';
    }
    if ($str_arr === '') {
        $ret = $val.'';
    } else {
        $arr = explode(',', $str_arr);
        $index = array_search($val, $arr);
        if ($index === false) {
            $arr[] = $val;
        }
        $ret = join(',', $arr);
    }
    return $ret;
}



// generates a login token
function get_login_token($user_id)
{
    $token = $user_id . '-' . random_str(30);
    return $token;
}



// throw an exception if the user is banned
function check_if_banned($pdo, $user_id, $ip)
{
    $row = query_if_banned($pdo, $user_id, $ip);

    if ($row !== false) {
        $ban_id = $row->ban_id;
        $expire_time = $row->expire_time;
        $reason = $row->reason;

        //figure out what the best way to say this is
        $seconds = $expire_time - time();
        $time_left = format_duration($seconds);

        //tell it to the world
        $output = "This account or ip address has been banned.\n"
        ."Reason: $reason \n"
        ."This ban will expire in $time_left. \n"
        ."You can see more details about this ban at pr2hub.com/bans/show_record.php?ban_id=$ban_id. \n\n"
        ."If you feel that this ban is unjust, you can dispute it. Follow the instructions outlined at jiggmin2.com/forums/showthread.php?tid=110.";

        throw new Exception($output);
    }
}


require_once __DIR__ . '/../queries/bans/ban_select_active_by_user_id.php';
require_once __DIR__ . '/../queries/bans/ban_select_active_by_ip.php';

function query_if_banned($pdo, $user_id, $ip)
{
    $ban = false;
    if (isset($user_id) && $user_id != 0) {
        $ban = ban_select_active_by_user_id($pdo, $user_id);
    }
    if (!$ban && isset($ip)) {
        $ban = ban_select_active_by_ip($pdo, $ip);
    }
    return $ban;
}


require_once __DIR__ . '/../queries/levels/levels_select_campaign.php';
require_once __DIR__ . '/../queries/levels/levels_select_best.php';
require_once __DIR__ . '/../queries/levels/levels_select_best_today.php';
require_once __DIR__ . '/../queries/levels/levels_select_newest.php';

// write a level list to the filesystem
function generate_level_list($pdo, $mode)
{
    if ($mode == 'campaign') {
        $levels = levels_select_campaign($pdo);
    } elseif ($mode == 'best') {
        $levels = levels_select_best($pdo);
    } elseif ($mode == 'best_today') {
        $levels = levels_select_best_today($pdo);
    } elseif ($mode == 'newest') {
        $levels = levels_select_newest($pdo);
    }

    $dir = __DIR__ . '/../www/files/lists/'.$mode.'/';
    @mkdir($dir, 0777, true);

    for ($j=0; $j<9; $j++) {
        $str = format_level_list(array_slice($levels, $j * 9, 9));
        $filename = $dir .($j+1);
        $handle = @fopen($filename, 'w');
        if ($handle) {
            fwrite($handle, $str);
            fclose($handle);
        } else {
            throw new Exception('could not write level list to '.$filename);
        }
    }
}
