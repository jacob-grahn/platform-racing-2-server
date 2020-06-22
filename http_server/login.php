<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once HTTP_FNS . '/pages/contests/part_vars.php';
require_once QUERIES_DIR . '/exp_today.php';
require_once QUERIES_DIR . '/favorite_levels.php';
require_once QUERIES_DIR . '/friends.php';
require_once QUERIES_DIR . '/ignored.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/mod_actions.php';
require_once QUERIES_DIR . '/part_awards.php';
require_once QUERIES_DIR . '/rank_tokens.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';
require_once QUERIES_DIR . '/recent_logins.php';
require_once QUERIES_DIR . '/servers.php';

$ip = get_ip();

$encrypted_login = default_post('i', '');
$version = isset($_POST['build']) ? default_post('build', '') : default_post('version', '');
$in_token = find('token');
$guest_login = false;
$token_login = false;
$has_email = false;
$has_ant = false;
$rt_available = 0;
$rt_used = 0;
$guild_owner = 0;
$emblem = '';
$guild_name = '';
$friends = array();
$ignored = array();
$favorite_levels = array();

$ret = new stdClass();
$ret->success = false;

try {
    // sanity check: POST?
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    } // sanity check: was data received?
    if (!isset($encrypted_login)) {
        throw new Exception('Login data not recieved.');
    } // sanity check: is it an allowed version?
    if (array_search($version, $ALLOWED_CLIENT_VERSIONS) === false) {
        throw new Exception('PR2 has recently been updated. Please refresh the page to download the latest version.');
    }

    // correct referrer?
    if (strpos($ip, $BLS_IP_PREFIX) !== 0) {
        require_trusted_ref('log in');
    }

    // rate limiting
    rate_limit('login-'.$ip, 5, 2, 'Please wait at least 5 seconds before trying to log in again.');
    rate_limit('login-'.$ip, 60, 10, 'Only 10 logins per minute per IP are accepted.');

    // decrypt login data
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($LOGIN_KEY);
    $str_login = $encryptor->decrypt($encrypted_login, $LOGIN_IV);
    $login = json_decode($str_login);
    $login->version = isset($login->build) ? $login->build : $login->version; // remove after 156's release
    $login->ip = $ip;
    $user_name = $login->user_name;
    $user_pass = $login->user_pass;
    $version2 = $login->version;
    $server_id = $login->server->server_id;
    $server_port = $login->server->port;
    $server_address = $login->server->address;
    $remember = $login->remember;

    // more sanity checks
    if (array_search($version2, $ALLOWED_CLIENT_VERSIONS) === false) {
        $e = "PR2 has recently been updated. Please refresh the page to download the latest version. $version2";
        throw new Exception($e);
    }
    if ((is_empty($in_token) === true && is_empty($user_name) === true) || strpos($user_name, '`') !== false) {
        throw new Exception('Invalid user name entered.');
    }

    // connect
    $pdo = pdo_connect();

    // get the server they're connecting to
    $server = server_select($pdo, $server_id);

    // guest login
    if (strtolower(trim($login->user_name)) === 'guest') {
        $guest_login = true;
        $user = user_select_guest($pdo);
        check_if_banned($pdo, $user->user_id, $ip); // don't let anyone banned under any scope log into guest accounts
    } // account login
    else {
        // token login
        if (isset($in_token) && $login->user_name === '' && $login->user_pass === '') {
            $token_login = true;
            $token = $in_token;
            $user = user_select($pdo, token_login($pdo, true, false, 'n'));
        } // or password login
        else {
            $user = pass_login($pdo, $user_name, $user_pass, 'n');
        }
        $user_id = (int) $user->user_id;
        unset($user_pass, $login->user_pass); // don't keep raw pass in memory or send to server

        // see if they're trying to log into a guest
        if ((int) $user->power === 0 && $guest_login === false && $token_login === false) {
            $e = 'Direct guest account logins are not allowed. Instead, please click "Play as Guest" on the main menu.';
            throw new Exception($e);
        }
    }
  
    // 160 testing
    if ($user->guild != 205 && $user->power < 2) {
        $link_160 = urlify('https://jiggmin2.com/forums/showthread.php?tid=2613', 'this thread');
        $msg_160 = 'PR2 is currently temporarily shut down to allow for testing '
            ."of a new build being released on or around Monday, June 22 (v160).\n\n"
            ."For more information, please see $link_160. Thanks for your patience!";
        throw new Exception($msg_160);
    }

    // are they banned?
    $bans = query_if_banned($pdo, $user_id, $ip);
    if (!empty($bans)) {
        foreach ($bans as $ban) { // will only iterate twice at most; grouped on scope
            if ($ban->scope === 'g') {
                throw new Exception(make_banned_notice($ban));
            }
            $user->sban_id = $ban->ban_id;
            $user->sban_exp_time = $ban->expire_time;
            $ban_msg = make_banned_notice($ban);
        }
    }

    // generate a login token for future requests
    $token = get_login_token($user->user_id);
    token_insert($pdo, $user->user_id, $token);
    if ($remember === true && $guest_login === false) {
        $token_expire = time() + 2592000; // one month
        setcookie('token', $token, $token_expire, '/', $_SERVER['SERVER_NAME'], false, true);
    } else {
        setcookie('token', '', time() - 3600, '/', $_SERVER['SERVER_NAME'], false, true);
    }

    // check IP validity
    $country_code = '?';
    $valid = check_ip($ip, $user);
    if (!$valid) {
        $aam_link = urlify('https://jiggmin2.com/aam', 'Ask a Mod');
        $msg = 'Please disable your proxy/VPN to connect to PR2. '.
            "If you feel this is a mistake, please use $aam_link to contact a member of the PR2 staff team.";
        throw new Exception($msg);
    }
    ensure_ip_country_from_valid_existing($pdo, $ip); // if possible, ensure country code isn't ?

    // create variables from user data in db
    $user_id = (int) $user->user_id;
    $user_name = $user->name;
    $group = (int) $user->power;

    // sanity check: is the entered name and the one retrieved from the database identical?
    // this won't be triggered unless some real funny business is going on
    if (($token_login === false || !is_empty($login->user_name)) &&
        strtolower($login->user_name) !== strtolower($user_name) &&
        $guest_login === false
    ) {
        throw new Exception('The names don\'t match. If this error persists, contact a member of the PR2 Staff Team.');
    }

    // sanity check: is it a valid name?
    if ($token_login === false) {
        if (strlen(trim($login->user_name)) < 2) {
            throw new Exception('Your name must be at least 2 characters long.');
        }
        if (strlen(trim($login->user_name)) > 20) {
            throw new Exception('Your name cannot be more than 20 characters long.');
        }
    }

    // sanity check: if a guild server, is the user in the guild?
    $ps_staff = $group === 3 || ($group === 2 && (int) $server->guild_id === 205);
    $is_fred = $user_id === FRED;
    if ((int) $server->guild_id !== 0 && (int) $user->guild !== (int) $server->guild_id && !$ps_staff && !$is_fred) {
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
    $rank_tokens = rank_token_select($pdo, $user_id);
    if (!empty($rank_tokens)) {
        $rt_available = $group > 0 ? $rank_tokens->available_tokens : 0;
        $rt_used = $group > 0 ? $rank_tokens->used_tokens : 0;
    }

    // check if they're renting rank tokens
    $rt_rented = rank_token_rentals_count($pdo, $user->user_id, $user->guild);

    // sanity check: do they have more than 8 permanent rank tokens?
    if ($rt_available > 8) {
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
    if (((int) $stats->rank + $rt_used >= 100) && $user_id !== FRED) {
        throw new Exception('Your rank is too high. Please choose a different account.');
    }

    // record moderator login
    if ($group > 1 || in_array($user_id, $special_ids)) {
        mod_action_insert($pdo, $user_id, "$user_name logged into $server->server_name from $ip", 'login', $ip);
    }

    // part arrays
    $hat_array = explode(',', $stats->hat_array);
    $head_array = explode(',', $stats->head_array);
    $body_array = explode(',', $stats->body_array);
    $feet_array = explode(',', $stats->feet_array);

    // check if parts need to be awarded
    $pending_awards = part_awards_select_by_user($pdo, $user_id);
    $stats = award_special_parts($stats, $group, $pending_awards);

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

    // select their favorites
    $fav_levels_result = favorite_levels_select_ids($pdo, $user_id);
    foreach ($fav_levels_result as $level) {
        $favorite_levels[] = (int) $level->level_id;
    }

    // get their EXP gained today
    $exp_today_id = exp_today_select($pdo, 'id-'.$user_id);
    $exp_today_ip = exp_today_select($pdo, 'ip-'.$ip);
    $exp_today = max($exp_today_id, $exp_today_ip);

    // check if they have an email set
    $has_email = !is_empty($user->email) && strlen($user->email) > 0 ? true : false; // email set?
    $has_ant = array_search(20, $head_array) !== false ? true : false; // kong account login perk

    // determine if in a guild and if the guild owner
    if ((int) $user->guild !== 0) {
        $guild = guild_select($pdo, $user->guild);
        if ((int) $guild->owner_id === $user_id) {
            $guild_owner = 1;
        }
        $emblem = $guild->emblem;
        $guild_name = $user->guild_name = $guild->guild_name;
    }

    // get their most recent PM id
    $last_recv_id = messages_select_most_recent($pdo, $user_id);

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
    $send->rt_used = $rt_used;
    $send->rt_available = $rt_available;
    $send->exp_today = $exp_today;
    $send->status = "Playing on $server->server_name";
    $send->server = $server;
    $send->epic_upgrades = $epic_upgrades;

    $str = "register_login`" . json_encode($send);
    $result = talk_to_server($server_address, $server_port, $server->salt, $str, true, false);

    // update user information if the login was successful
    $result = json_decode($result);
    if ($result->success) {
        user_update_status($pdo, $user_id, $send->status, $server_id); // status
        user_update_ip($pdo, $user_id, $ip); // last IP address
        recent_logins_insert($pdo, $user_id, $ip, $country_code); // record recent login
    }

    // tell the world
    $ret->success = true;
    $ret->message = isset($ban_msg) ? $ban_msg : null;
    $ret->userId = $user_id;
    $ret->token = $token;
    $ret->email = $has_email;
    $ret->ant = $has_ant;
    $ret->time = time();
    $ret->lastRead = $user->read_message_id;
    $ret->lastRecv = $last_recv_id;
    $ret->guild = $user->guild;
    $ret->guildOwner = $guild_owner;
    $ret->guildName = $guild_name;
    $ret->emblem = $emblem;
    $ret->favoriteLevels = $favorite_levels;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
