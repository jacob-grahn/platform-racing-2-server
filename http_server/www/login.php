<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/Encryptor.php';
require_once __DIR__ . '/../queries/tokens/token_insert.php';
require_once __DIR__ . '/../queries/servers/server_select.php';
require_once __DIR__ . '/../queries/users/user_select_guest.php';
require_once __DIR__ . '/../queries/users/user_select.php';
require_once __DIR__ . '/../queries/users/user_update_status.php';
require_once __DIR__ . '/../queries/users/user_update_ip.php';
require_once __DIR__ . '/../queries/pr2/pr2_select.php';
require_once __DIR__ . '/../queries/epic_upgrades/epic_upgrades_select.php';
require_once __DIR__ . '/../queries/rank_tokens/rank_token_select.php';
require_once __DIR__ . '/../queries/rank_token_rentals/rank_token_rentals_count.php';
require_once __DIR__ . '/../queries/staff/actions/mod_action_insert.php';
require_once __DIR__ . '/../queries/friends/friends_select.php';
require_once __DIR__ . '/../queries/ignored/ignored_select_list.php';
require_once __DIR__ . '/../queries/exp_today/exp_today_select.php';
require_once __DIR__ . '/../queries/guilds/guild_select.php';
require_once __DIR__ . '/../queries/recent_logins/recent_logins_insert.php';
require_once __DIR__ . '/../queries/messages/messages_select_most_recent.php';

$encrypted_login = $_POST['i'];
$version = $_POST['version'];
$in_token = find('token');

$allowed_versions = array('24-dec-2013-v1');
$guest_login = false;
$has_email = false;
$has_ant = false;
$new_account = false;
$rt_available = 0;
$rt_used = 0;
$guild_owner = 0;
$emblem = '';
$guild_name = '';
$friends = array();
$ignored = array();

// get the user's ip info and run it through an ip info api (because installing geoip is not worth the hassle)
try {
    $ip = get_ip();
    $ip_info = json_decode(file_get_contents('https://tools.keycdn.com/geo.json?host=' . $ip));
    $country_code = $ip_info->data->geo->country_code;
} catch (Exception $e) {
    $country_code = '?';
}

try {
    // sanity checks
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }
    if (!isset($encrypted_login)) {
        throw new Exception('Login data not recieved.');
    }
    if (array_search($version, $allowed_versions) === false) {
        throw new Exception('Platform Racing 2 has recently been updated. Please refresh your browser to download the latest version.');
    }


    // rate limiting
    rate_limit('login-'.$ip, 5, 2, 'Please wait at least 5 seconds before trying to log in again.');
    rate_limit('login-'.$ip, 60, 10, 'Only 10 logins per minute per IP are accepted.');


    //--- decrypt login data
    $encryptor = new Encryptor();
    $encryptor->set_key($LOGIN_KEY);
    $str_login = $encryptor->decrypt($encrypted_login, $LOGIN_IV);
    $login = json_decode($str_login);

    $user_name = $login->user_name;
    $user_pass = $login->user_pass;
    $version2 = $login->version;
    $server_id = $login->server->server_id;
    $server_port = $login->server->port;
    $server_address = $login->server->address;
    $origination_domain = $login->domain;
    $remember = $login->remember;
    $login_code = $login->login_code;


    //--- more sanity checks
    if (array_search($version2, $allowed_versions) === false) {
        throw new Exception('Platform Racing 2 has recently been updated. Please refresh your browser to download the latest version. [Version check 2] ' . $version2);
    }
    if ($origination_domain == 'local') {
        throw new Exception('Testing mode has been disabled.');
    }
    if ((is_empty($in_token) === true && is_empty($user_name) === true) || strpos($user_name, '`') !== false) {
        throw new Exception('Invalid user name entered.');
    }

    //--- connect
    $pdo = pdo_connect();



    //--- get the server they're connecting to
    $server = server_select($pdo, $server_id);



    //--- guest login
    if (strtolower(trim($login->user_name)) == 'guest') {
        $guest_login = true;
        $guest = user_select_guest($pdo);
        check_if_banned($pdo, $guest->user_id, $ip);
        $user = pass_login($pdo, $guest->name, $GUEST_PASS);
    } //--- account login
    else {
        //token login
        if (isset($in_token) && $login->user_name == '' && $login->user_pass == '') {
            $token = $in_token;
            $user_id = token_login($pdo);
            $user = user_select($pdo, $user_id);
        } //or password login
        else {
            $user = pass_login($pdo, $user_name, $user_pass);
        }
    }


    //--- give them a login token for future requests
    $token = get_login_token($user->user_id);
    token_insert($pdo, $user->user_id, $token);
    if ($remember == 'true' && !$guest_login) {
        $token_expire = time() + (60*60*24*30);
        setcookie('token', $token, $token_expire);
    } else {
        setcookie('token', '', time()-3600);
    }


    // create variables from user data in db
    $user_id = (int) $user->user_id;
    $user_name = $user->name;
    $login->user_name = $user_name; // sanitize user input by taking name from the db to send to the server
    $group = $user->power;


    // name sanity checks
    if (strlen(trim($user_name)) < 2) {
        throw new Exception("Your name must be at least 2 characters long.");
    }
    if (strlen(trim($user_name)) > 20) {
        throw new Exception("Your name cannot be more than 20 characters long.");
    }


    //---
    if ($server->guild_id != 0 && $user->guild != $server->guild_id) {
        throw new Exception('You must be a member of this guild to join this server.');
    }


    // get their info speed, hats, etc
    $stats = pr2_select($pdo, $user_id);
    $epic_upgrades = epic_upgrades_select($pdo, $user_id, true);



    //--- check if they own rank tokens
    $row = rank_token_select($pdo, $user_id);
    if ($row) {
        $rt_available = $row->available_tokens;
        $rt_used = $row->used_tokens;
    }

    $rt_available += rank_token_rentals_count($pdo, $user->user_id, $user->guild);
    if ($rt_available < $rt_used) {
        $rt_used = $rt_available;
    }


    //--- record moderator login
    $server_name = $server->server_name;
    if ($group > 1) {
        mod_action_insert($pdo, $user_id, "$user_name logged into $server_name from $ip", $user_id, $ip);
    }


    $hat_array = explode(',', $stats->hat_array);
    $head_array = explode(',', $stats->head_array);
    $body_array = explode(',', $stats->body_array);
    $feet_array = explode(',', $stats->feet_array);


    //--- santa set
    $date = date('F d');
    if ($date == 'December 24' || $date == 'December 25') {
        if (add_item($hat_array, 7)) {
            $stats->hat = 7;
        }
        if (add_item($head_array, 34)) {
            $stats->head = 34;
        }
        if (add_item($body_array, 34)) {
            $stats->body = 34;
        }
        if (add_item($feet_array, 34)) {
            $stats->feet = 34;
        }
    }

    //--- bunny set
    if ($date == 'April 20' || $date == 'April 21') {
        if (add_item($head_array, 39)) {
            $stats->head = 39;
        }
        if (add_item($body_array, 39)) {
            $stats->body = 39;
        }
        if (add_item($feet_array, 39)) {
            $stats->feet = 39;
        }
    }

    //--- party hat
    if ($date == 'December 31' || $date == 'January 1') {
        if (add_item($hat_array, 8)) {
            $stats->hat = 8;
        }
    }

    //--- heart set
    if ($date == 'February 13' || $date == 'February 14') {
        if (add_item($head_array, 38)) {
            $stats->head = 38;
        }
        if (add_item($body_array, 38)) {
            $stats->body = 38;
        }
        if (add_item($feet_array, 38)) {
            $stats->feet = 38;
        }
    }

    //--- give crown hats to moderators
    if ($group > 1) {
        add_item($hat_array, 6);
    }


    //--- get their friends
    $friends_result = friends_select($pdo, $user_id);
    foreach ($friends_result as $fr) {
        $friends[] = $fr->friend_id;
    }

    //--- get their ignored
    $ignored_result = ignored_select_list($pdo, $user_id);
    foreach ($ignored_result as $ir) {
        $ignored[] = $ir->ignore_id;
    }


    //--- get their rank gained today
    $exp_today_id = exp_today_select($pdo, 'id-'.$user_id);
    $exp_today_ip = exp_today_select($pdo, 'ip-'.$ip);
    $exp_today = max($exp_today_id, $exp_today_ip);



    //--- check if they have an email set
    if (isset($user->email) && strlen($user->email) > 0) {
        $has_email = true;
    }


    //--- check if they have kong's ant body
    if (array_search(20, $head_array) !== false) {
        $has_ant = true;
    }


    //--- see if they are in a guild
    if ($user->guild != 0) {
        $guild = guild_select($pdo, $user->guild);
        if ($guild->owner_id == $user_id) {
            $guild_owner = 1;
        }
        $emblem = $guild->emblem;
        $guild_name = $guild->guild_name;
    }


    //--- get their most recent PM id
    $last_recv_id = messages_select_most_recent($pdo, $user_id);


    //--- update their status
    $status = "Playing on $server->server_name";

    user_update_status($pdo, $user_id, $status, $server_id);
    user_update_ip($pdo, $user_id, $ip);
    recent_logins_insert($pdo, $user_id, $ip, $country_code);


    //---
    $stats->hat_array = join(',', $hat_array);
    $stats->head_array = join(',', $head_array);
    $stats->body_array = join(',', $body_array);
    $stats->feet_array = join(',', $feet_array);


    //--- send this info to the socket server
    $send = new stdClass();
    $send->login = $login;
    $send->user = $user;
    $send->stats = $stats;
    $send->friends = $friends;
    $send->ignored = $ignored;
    $send->new_account = $new_account;
    $send->rt_used = $rt_used;
    $send->rt_available = $rt_available;
    $send->exp_today = $exp_today;
    $send->status = $status;
    $send->server = $server; //can remove this later?
    $send->epic_upgrades = $epic_upgrades;

    $str = "register_login`" . json_encode($send);
    talk_to_server($server_address, $server_port, $server->salt, $str, false);


    //--- tell it to the world
    $reply = new stdClass();
    $reply->status = 'success';
    $reply->token = $token;
    $reply->email = $has_email;
    $reply->ant = $has_ant;
    $reply->time = time();
    $reply->lastRead = $user->read_message_id;
    $reply->lastRecv = $last_recv_id;
    $reply->guild = $user->guild;
    $reply->guildOwner = $guild_owner;
    $reply->guildName = $guild_name;
    $reply->emblem = $emblem;
    $reply->userId = $user_id;

    // allowed domain check
    $ref = check_ref();
    if ($ref !== true) {
        $reply->message = "It looks like you're using PR2 from a third-party website. For security reasons, some game features may be disabled. To access a version of the game with all features available to you, play from an approved site such as pr2hub.com.";
    } // DEBUGGING
    else if ($user->user_id == 5653330) {
        $server->salt = "pepper"; // i prefer pepper with my data
        $send->server = $server; // put the updated val back in the $server var
        $str = "register_login`" . json_encode($send); // make it readable
        $reply->message = $str; // tell me
    }

    echo json_encode($reply);
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
    echo json_encode($reply);
}



function add_item(&$arr, $item)
{
    if (array_search($item, $arr) === false) {
        $arr[] = $item;
        return true;
    } else {
        return false;
    }
}
