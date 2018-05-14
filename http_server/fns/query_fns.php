<?php

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
function token_login($pdo, $use_cookie = true, $suppress_error = false)
{

    $rec_token = find_no_cookie('token');
    if (isset($rec_token) && !empty($rec_token)) {
        $token = $rec_token;
    } elseif ($use_cookie && isset($_COOKIE['token']) && !empty($_COOKIE['token'])) {
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


// determine if a user is staff
function is_staff($pdo, $user_id)
{
    $is_mod = false;
    $is_admin = false;

    // determine power and if staff
    $power = (int) user_select_power($pdo, $user_id, true);
    if ($power === false || is_empty($power, false)) {
        $power = 0;
    }
    if ($power >= 2) {
        $is_mod = true;
        if ($power == 3) {
            $is_admin = true;
        }
    }

    // tell the world
    $return = new stdClass();
    $return->mod = $is_mod;
    $return->admin = $is_admin;
    return $return;
}


// award hats
function award_part($pdo, $user_id, $type, $part_id)
{
    $is_epic = false;
    $type = strtolower($type);
    $part_types = ['hat','head','body','feet','ehat','ehead','ebody','efeet'];

    // sanity check: is it a valid type?
    if (!in_array($type, $part_types)) {
        throw new Exception("Invalid part type specified.");
    }

    // determine where in the array our value was found
    $type_no = array_search($type, $part_types);
    if ($type_no >= 4) {
        $is_epic = true;
    }

    // get existing parts
    try {
        if ($is_epic === true) {
            $row = epic_upgrades_select($pdo, $user_id);
        } else {
            $row = pr2_select($pdo, $user_id);
        }
        $field = type_to_db_field($type);
        $str_array = $row->{$field};
    } catch (Exception $e) {
        $str_array = '';
    }

    // explode on ,
    $part_array = explode(",", $str_array);
    if (in_array($part_id, $part_array)) {
        return false;
    }

    // insert part award, award part
    part_awards_insert($pdo, $user_id, $type, $part_id);

    // award part
    array_push($part_array, $part_id);
    $new_field_str = join(",", $part_array);

    if ($is_epic === true) {
        epic_upgrades_update_field($pdo, $user_id, $type, $new_field_str);
    } else {
        pr2_update_part_array($pdo, $user_id, $type, $new_field_str);
    }
    return true;
}


// check to see if a user has a part
function has_part($pdo, $user_id, $type, $part_id)
{
    $is_epic = false;
    $type = strtolower($type);
    $part_types = ['hat','head','body','feet','ehat','ehead','ebody','efeet'];

    // sanity check: is it a valid type?
    if (!in_array($type, $part_types)) {
        throw new Exception("Invalid part type specified.");
    }

    // determine where in the array our value was found
    $type_no = array_search($type, $part_types);
    if ($type_no >= 4) {
        $is_epic = true;
    }

    // perform query
    $field = type_to_db_field($type);
    if ($is_epic === true) {
        $data = epic_upgrades_select($pdo, $user_id);
    } else {
        $data = pr2_select($pdo, $user_id);
    }

    // get data and convert to an array
    $parts_str = $data->{$field};
    $parts_arr = explode(",", $parts_str);

    // search for part ID in array
    if (in_array($part_id, $parts_arr)) {
        return true;
    } else {
        return false;
    }
}


function type_to_db_field($type)
{
    $type = strtolower($type);

    if ($type == 'hat') {
        $field = 'hat_array';
    } elseif ($type == 'head') {
        $field = 'head_array';
    } elseif ($type == 'body') {
        $field = 'body_array';
    } elseif ($type == 'feet') {
        $field = 'feet_array';
    } elseif ($type == 'ehat') {
        $field = 'epic_hats';
    } elseif ($type == 'ehead') {
        $field = 'epic_heads';
    } elseif ($type == 'ebody') {
        $field = 'epic_bodies';
    } elseif ($type == 'efeet') {
        $field = 'epic_feet';
    } else {
        throw new Exception('Unknown type');
    }
    return( $field );
}


function append_to_str_array($str_arr, $val) // is this needed?
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
        $reason = htmlspecialchars($row->reason);

        //figure out what the best way to say this is
        $seconds = $expire_time - time();
        $time_left = format_duration($seconds);

        //tell it to the world
        $output = "This account or ip address has been banned.\n"
        ."Reason: $reason \n"
        ."This ban will expire in $time_left. \n"
        ."You can see more details about this ban at pr2hub.com/bans/show_record.php?ban_id=$ban_id. \n\n"
        ."If you feel that this ban is unjust, you can dispute it. Follow the "
        ."instructions outlined at jiggmin2.com/forums/showthread.php?tid=110.";

        throw new Exception($output);
    }
}


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

    $dir = WWW_ROOT . '/files/lists/'.$mode.'/';
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
