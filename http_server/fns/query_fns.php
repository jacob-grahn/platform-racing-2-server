<?php

require_once __DIR__ . '/random_str.php';


//--- checks if a login is valid -----------------------------------------------------------
require_once __DIR__ . '/../queries/users/user_select_hash_by_name.php';
require_once __DIR__ . '/../queries/users/user_apply_temp_pass.php';
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
    $user = user_select_hash_by_name($pdo, $name);

    // check the password
    if (!password_verify(sha1($password), $user->pass_hash)) {
        if (password_verify(sha1($password), $user->temp_pass_hash)) {
            user_apply_temp_pass($pdo, $user->user_id);
        } else {
            throw new Exception('Incorrect password');
        }
    }

    // check to see if they're banned
    check_if_banned($pdo, $user->user_id, $ip);

    // respect changes to capitalization
    $user->name = $name;

    // done
    return $user;
}



// login using a token
require_once __DIR__ . '/../queries/tokens/token_select.php';

function token_login($pdo, $use_cookie = true)
{

    $rec_token = find_no_cookie('token');
    if (isset($rec_token) && $rec_token != '') {
        $token = $rec_token;
    } elseif ($use_cookie && isset($_COOKIE['token']) && $_COOKIE['token'] != '') {
        $token = $_COOKIE['token'];
    }

    if (!isset($token)) {
        throw new Exception('No token found. Please log in again.');
    }

    $token_row = token_select($pdo, $token);
    $user_id = $token_row->user_id;

    $ip = get_ip();
    check_if_banned($pdo, $user_id, $ip);

    return $user_id;
}



// lookup user_id with name
function name_to_id($db, $name)
{
    $user_id = $db->grab('user_id', 'user_select_user_id', array($name), 'Could not find a user with that name.');
    return $user_id;
}



// lookup name with user_id
function id_to_name($db, $user_id)
{
    $user_name = $db->grab('name', 'user_select', array($user_id));
    return $user_name;
}



// count the number of bans an account has recieved
function count_bans($db, $value, $ban_type = 'account')
{
    $safe_value = addslashes($value);

    if ($ban_type == 'account') {
        $field = 'banned_user_id';
    } else {
        $field = 'banned_ip';
    }

    $result = $db->query(
        "select count(*) as ban_count
									from bans
									where $field = '$safe_value'"
    );
    if (!$result) {
        throw new Exception('Could not count bans for '.$ban_type);
    }

    $row = $result->fetch_object();
    $ban_count = $row->ban_count;

    return($ban_count);
}



// retrieve all bans of a certain type
function retrieve_bans($db, $value, $ban_type = 'account')
{
    $safe_value = addslashes($value);

    if ($ban_type == 'account') {
        $field = 'banned_user_id';
    } else {
        $field = 'banned_ip';
    }

    $result = $db->query(
        "select *
									from bans
									where $field = '$safe_value'
									and ".$ban_type."_ban = 1
									limit 0, 50"
    );
    if (!$result) {
        throw new Exception('Could not retrieve bans for '.$ban_type);
    }

    return($result);
}


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
    }
    catch (Exception $e) {
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



// save a login token
function save_login_token($db, $user_id, $token)
{
    $db->call('token_insert', array($user_id, $token));
}


// delete a login token
function delete_login_token($db, $token)
{
    $db->call('token_delete', array($token));
}



// throw an exception if the user is banned
function check_if_banned($pdo, $user_id, $ip)
{
    $row = query_if_banned($pdo, $user_id, $ip);

    if ($row !== false) {
        $ban_id = $row->ban_id;
        $banned_ip = $row->banned_ip;
        $banned_user_id = $row->banned_user_id;
        $mod_user_id = $row->mod_user_id;
        $expire_time = $row->expire_time;
        $reason = $row->reason;
        $response = $row->response;

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


require_once __DIR__ . '/../queries/bans/ban_select_by_user_id.php';
require_once __DIR__ . '/../queries/bans/ban_select_by_ip.php';

function query_if_banned($pdo, $user_id, $ip)
{
    $ban = false;
    if (isset($user_id) && $user_id != 0) {
        $ban = ban_select_by_user_id($pdo, $user_id);
    }
    if (!$ban && isset($ip)) {
        $ban = ban_select_by_ip($pdo, $ip);
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






// perform a level search
function search_levels($mode, $search_str, $order, $dir, $page)
{

    if (!isset($mode) || ($mode != 'user' && $mode != 'title')) {
        $mode = 'title';
    }
    if (!isset($order) || ($order != 'rating' && $order != 'date' && $order != 'alphabetical' && $order != 'popularity')) {
        $order = 'date';
    }
    if (!isset($dir) || ($dir != 'asc' && $dir != 'desc')) {
        $dir = 'desc';
    }
    if (!isset($page) || !is_numeric($page) || $page < 1) {
        $page = 1;
    }

    $start = ($page-1) * 6;
    $count = 6;

    $safe_search_str = addslashes($search_str);
    $safe_dir = addslashes($dir);

    $select_str = 'select pr2_levels.level_id, pr2_levels.version, pr2_levels.title, pr2_levels.rating, pr2_levels.play_count, pr2_levels.min_level, pr2_levels.note, pr2_levels.live, pr2_levels.type, users.name, users.power, pr2_levels.pass';
    $from_str = 'from pr2_levels, users';


    if ($order == 'rating') {
        $order_by = 'order by pr2_levels.rating';
    } elseif ($order == 'date') {
        $order_by = 'order by pr2_levels.time';
    } elseif ($order == 'alphabetical') {
        $order_by = 'order by pr2_levels.title';
    } elseif ($order == 'popularity') {
        $order_by = 'order by pr2_levels.play_count';
    } else {
        $order_by = 'order by pr2_levels.time';
    }


    if ($mode == 'user') {
        $query_str = "$select_str
						$from_str
						WHERE users.name = '$safe_search_str'
						AND pr2_levels.user_id = users.user_id
						AND (pr2_levels.live = 1 OR pr2_levels.pass IS NOT NULL)
						$order_by $safe_dir
						limit $start, $count";
    } elseif ($mode == 'title') {
        $query_str = "$select_str
						$from_str
						WHERE MATCH (title) AGAINST ('\"$safe_search_str\"' IN BOOLEAN MODE)
						AND pr2_levels.user_id = users.user_id
						AND live = 1
						$order_by $safe_dir
						limit $start, $count";
    }


    $db = new DB();
    $result = $db->query($query_str, '*search_levels');
    if (!$result) {
        throw new Exception('Could not retrieve levels.');
    }


    $str = format_level_list($db->to_array($result));
    return($str);
}
