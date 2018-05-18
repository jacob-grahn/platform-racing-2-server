<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once HTTP_FNS . '/pr2/register_user_fns.php';
require_once QUERIES_DIR . '/tokens/token_insert.php';
require_once QUERIES_DIR . '/servers/server_select.php';
require_once QUERIES_DIR . '/users/user_select_guest.php';
require_once QUERIES_DIR . '/users/user_select.php';
require_once QUERIES_DIR . '/users/user_update_status.php';
require_once QUERIES_DIR . '/users/user_update_ip.php';
require_once QUERIES_DIR . '/pr2/pr2_select.php';
require_once QUERIES_DIR . '/epic_upgrades/epic_upgrades_select.php';
require_once QUERIES_DIR . '/rank_tokens/rank_token_select.php';
require_once QUERIES_DIR . '/rank_token_rentals/rank_token_rentals_count.php';
require_once QUERIES_DIR . '/staff/actions/mod_action_insert.php';
require_once QUERIES_DIR . '/friends/friends_select.php';
require_once QUERIES_DIR . '/ignored/ignored_select_list.php';
require_once QUERIES_DIR . '/exp_today/exp_today_select.php';
require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/recent_logins/recent_logins_insert.php';
require_once QUERIES_DIR . '/messages/messages_select_most_recent.php';

// initialize our variables
$encrypted_login = $_POST['i'];
$version = $_POST['version'];
$in_token = find('token');
$allowed_versions = array('24-dec-2013-v1');
$guest_login = false;
$token_login = false;
$has_email = false; // is this needed?
$has_ant = false;
$new_account = false; // is this needed?
$rt_available = 0;
$rt_used = 0;
$guild_owner = 0;
$emblem = '';
$guild_name = '';
$friends = array();
$ignored = array();

// get the user's IP and run it through an IP info API
$ip = get_ip();
$ip_info = json_decode(file_get_contents('https://tools.keycdn.com/geo.json?host=' . $ip));
if ($ip_info !== false && !empty($ip_info)) {
    $country_code = $ip_info->data->geo->country_code;
} else {
    $country_code = "?"; // deal with third party failure
}

try {
    // sanity check: POST?
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    } // sanity check: was data received?
    if (!isset($encrypted_login)) {
        throw new Exception('Login data not recieved.');
    } // sanity check: is it an allowed version?
    if (array_search($version, $allowed_versions) === false) {
        throw new Exception(
            'Platform Racing 2 has recently been updated. '.
            'Please refresh your browser to download the latest version.'
        );
    } // sanity check: correct referrer?
    //require_trusted_ref('log in');

    // rate limiting
    rate_limit('login-'.$ip, 5, 2, 'Please wait at least 5 seconds before trying to log in again.');
    rate_limit('login-'.$ip, 60, 10, 'Only 10 logins per minute per IP are accepted.');

    // decrypt login data
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($LOGIN_KEY);
    $str_login = $encryptor->decrypt($encrypted_login, $LOGIN_IV);
    $login = json_decode($str_login);
    $user_name = $login->user_name;
    $user_pass = $login->user_pass;
    $version2 = $login->version;
    $server_id = $login->server->server_id;
    $server_port = $login->server->port;
    $server_address = $login->server->address;
    $remember = $login->remember;
    $login_code = $login->login_code;

    // more sanity checks
    if (array_search($version2, $allowed_versions) === false) {
        throw new Exception(
            'Platform Racing 2 has recently been updated. '.
            'Please refresh your browser to download the latest version. '.
            '[Version check 2] '.
            $version2
        );
    }
    if ((is_empty($in_token) === true && is_empty($user_name) === true) || strpos($user_name, '`') !== false) {
        throw new Exception('Invalid user name entered.');
    }

    // connect to the db
    $pdo = pdo_connect();

    // get the server they're connecting to
    $server = server_select($pdo, $server_id);

    // guest login
    if (strtolower(trim($login->user_name)) == 'guest') {
        $guest_login = true;
        $guest = user_select_guest($pdo);
        $user = pass_login($pdo, $guest->name, $GUEST_PASS);
        $login->user_name = $guest->name;
        $login->user_pass = $GUEST_PASS;
    } // account login
    else {
        // token login
        if (isset($in_token) && $login->user_name == '' && $login->user_pass == '') {
            $token_login = true;
            $token = $in_token;
            $user_id = token_login($pdo);
            $user = user_select($pdo, $user_id);
        } // or password login
        else {
            $user = pass_login($pdo, $user_name, $user_pass);
        }
    }

    // generate a login token for future requests
    $token = get_login_token($user->user_id);
    token_insert($pdo, $user->user_id, $token);
    if ($remember == 'true' && !$guest_login) {
        $token_expire = time() + (60*60*24*30);
        setcookie('token', $token, $token_expire, "/", $_SERVER["SERVER_NAME"], false, true);
    } else {
        setcookie('token', '', time()-3600, "/", $_SERVER["SERVER_NAME"], false, true);
    }

    // create variables from user data in db
    $user_id = (int) $user->user_id;
    $user_name = $user->name;
    $group = $user->power;

    // sanity check: is the entered name and the one retrieved from the database identical?
    // this won't be triggered unless some real funny business is going on
    if (($token_login === false || !is_empty($login->user_name)) &&
        strtolower($login->user_name) !== strtolower($user_name)
    ) {
        throw new Exception("The names don't match. If this error persists, contact a member of the PR2 Staff Team.");
    }

    // sanity check: is it a valid name?
    if ($token_login === false) {
        if (strlen(trim($login->user_name)) < 2) {
            throw new Exception("Your name must be at least 2 characters long.");
        }
        if (strlen(trim($login->user_name)) > 20) {
            throw new Exception("Your name cannot be more than 20 characters long.");
        }
    }

    // sanity check: if a guild server, is the user in the guild?
    if ($server->guild_id != 0 && $user->guild != $server->guild_id) {
        throw new Exception('You must be a member of this guild to join this server.');
    }

    // get their pr2 and epic_upgrades info
    $stats = pr2_select($pdo, $user_id, true);
    if ($stats === false) {
        pr2_insert($pdo, $user_id);
        message_send_welcome($pdo, $user_name, $user_id);
    }
    $epic_upgrades = epic_upgrades_select($pdo, $user_id, true);

    // check if they own rank tokens
    $row = rank_token_select($pdo, $user_id);
    if (!empty($row)) {
        $rt_available = $row->available_tokens;
        $rt_used = $row->used_tokens;
    }
    
    // check if they're renting rank tokens
    $rt_rented = rank_token_rentals_count($pdo, $user->user_id, $user->guild);
    
    // sanity check: do they have more than 5 permanent rank tokens?
    if ($rt_available > 5) {
        throw new Exception("Too many rank tokens. Please use a different account.");
    }
    
    // sanity check: are they renting more than 21 guild tokens?
    if ($rt_rented > 21) {
        throw new Exception('Too many guild tokens. Please use a different account.');
    }

    // sanity check: are more tokens used than available?
    $rt_available = $rt_available + $rt_rented;
    if ($rt_available < $rt_used) {
        $rt_used = $rt_available;
    }
    
    // sanity check: is the user's rank 100+?
    $rank = (int) $stats->rank;
    if ($rank + $rt_used >= 100) {
        throw new Exception('Your rank is too high. Please choose a different account.');
    }
    
    // record moderator login
    $server_name = $server->server_name;
    if ($group > 1) {
        mod_action_insert($pdo, $user_id, "$user_name logged into $server_name from $ip", $user_id, $ip);
    }

    // part arrays
    $hat_array = explode(',', $stats->hat_array);
    $head_array = explode(',', $stats->head_array);
    $body_array = explode(',', $stats->body_array);
    $feet_array = explode(',', $stats->feet_array);

    // give special parts based on date
    $date = date('F d');

    // santa set
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

    // bunny set
    if ($date == 'April 7' || $date == 'April 8') {
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

    // party hat
    if ($date == 'December 31' || $date == 'January 1') {
        if (add_item($hat_array, 8)) {
            $stats->hat = 8;
        }
    }

    // heart set
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

    // give crown hats to moderators
    if ($group > 1) {
        add_item($hat_array, 6);
    }

    // select their friends list
    $friends_result = friends_select($pdo, $user_id);
    foreach ($friends_result as $fr) {
        $friends[] = $fr->friend_id;
    }

    // select their ignored list
    $ignored_result = ignored_select_list($pdo, $user_id);
    foreach ($ignored_result as $ir) {
        $ignored[] = $ir->ignore_id;
    }

    // get their EXP gained today
    $exp_today_id = exp_today_select($pdo, 'id-'.$user_id);
    $exp_today_ip = exp_today_select($pdo, 'ip-'.$ip);
    $exp_today = max($exp_today_id, $exp_today_ip);

    // check if they have an email set
    if (isset($user->email) && strlen($user->email) > 0) {
        $has_email = true;
    }

    // check if they have the ant set (kong login perk, checks for ant head)
    if (array_search(20, $head_array) !== false) {
        $has_ant = true;
    }

    // determine if in a guild and if the guild owner
    if ($user->guild != 0) {
        $guild = guild_select($pdo, $user->guild);
        if ($guild->owner_id == $user_id) {
            $guild_owner = 1;
        }
        $emblem = $guild->emblem;
        $guild_name = $guild->guild_name;
    }

    // get their most recent PM id
    $last_recv_id = messages_select_most_recent($pdo, $user_id);

    // update their status
    $status = "Playing on $server->server_name";
    user_update_status($pdo, $user_id, $status, $server_id);

    // update their IP and record the recent login
    user_update_ip($pdo, $user_id, $ip);
    recent_logins_insert($pdo, $user_id, $ip, $country_code);

    // join the part arrays to send to the server
    $stats->hat_array = join(',', $hat_array);
    $stats->head_array = join(',', $head_array);
    $stats->body_array = join(',', $body_array);
    $stats->feet_array = join(',', $feet_array);

    // send this info to the socket server
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
    $send->server = $server;
    $send->epic_upgrades = $epic_upgrades;

    $str = "register_login`" . json_encode($send);
    talk_to_server($server_address, $server_port, $server->salt, $str, false, false);

    // tell the world
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
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
} finally {
    echo json_encode($reply);
    die();
}
