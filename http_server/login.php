<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/ip_api_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once QUERIES_DIR . '/exp_today.php';
require_once QUERIES_DIR . '/favorite_levels.php';
require_once QUERIES_DIR . '/follows.php';
require_once QUERIES_DIR . '/friends.php';
require_once QUERIES_DIR . '/ignored.php';
require_once QUERIES_DIR . '/ip_validity.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/mod_actions.php';
require_once QUERIES_DIR . '/part_awards.php';
require_once QUERIES_DIR . '/rank_tokens.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';
require_once QUERIES_DIR . '/recent_logins.php';
require_once QUERIES_DIR . '/servers.php';

$ip = get_ip();

$encrypted_login = default_post('i', '');
$build = default_post('build', '');
$in_token = find('token');
$guest_login = false;
$token_login = false;
$rt_available = 0;
$rt_used = 0;
$guild_owner = 0;
$emblem = '';
$guild_name = '';
$following = array();
$friends = array();
$ignored = array();
$favorite_levels = array();

$ret = new stdClass();
$ret->success = false;

try {
    // sanity: POST?
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // sanity: correct referrer?
    if (strpos($ip, $BLS_IP_PREFIX) !== 0) {
        require_trusted_ref('log in');
    }

    // sanity: proper data received?
    if (!isset($encrypted_login)) {
        throw new Exception('Login data not recieved.');
    }

    // rate limiting
    rate_limit('login-'.$ip, 5, 2, 'Please wait at least 5 seconds before trying to log in again.');
    rate_limit('login-'.$ip, 60, 10, 'Only 10 logins per minute per IP are accepted.');

    // decrypt login data
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($LOGIN_KEY);
    $str_login = $encryptor->decrypt($encrypted_login, $LOGIN_IV);
    $login = json_decode($str_login);
    $login->ip = $ip;
    $user_name = $login->user_name;
    $user_pass = $login->user_pass;
    $build2 = $login->build;
    $server_id = $login->server->server_id;
    $server_port = $login->server->port;
    $server_address = $login->server->address;
    $remember = $login->remember;

    // sanity: correct version?
    if (!in_array($build, $ALLOWED_CLIENT_VERSIONS) || !in_array($build2, $ALLOWED_CLIENT_VERSIONS)) {
        $e = "PR2 has recently been updated. Please reload the game to download the latest version.";
        throw new Exception($e);
    }

    // sanity: valid name?
    if ((is_empty($in_token) && is_empty($user_name)) || strpos($user_name, '`') !== false) {
        throw new Exception('Invalid user name entered.');
    }

    // connect
    $pdo = pdo_connect();

    // get the server they're connecting to
    $server = server_select($pdo, $server_id);
    if ((int) $server->active === 0) { // sanity: trying to log into an inactive server?
        throw new Exception('This server is currently unavailable. Please choose a different one.');
    }

    // guest login
    if (strtolower(trim($login->user_name)) === 'guest' && $user_pass === '') {
        $guest_login = true;
        $user = user_select_guest($pdo);
        check_if_banned($pdo, $user->user_id, $ip); // don't let anyone banned under any scope log into guest accounts
    } else { // account login
        if (isset($in_token) && $user_name === '' && $user_pass === '') {  // token login
            $token_login = true;
            $token = $in_token;
            $user = user_select($pdo, token_login($pdo, true, false, 'n'));
        } else { // or password login
            $user = pass_login($pdo, $user_name, $user_pass, 'n');
        }
        unset($user_pass, $login->user_pass); // don't keep raw pass in memory or send to server

        // see if they're trying to log into a guest
        if ((int) $user->power === 0 && !$guest_login && !$token_login) {
            $e = 'Direct guest account logins are not allowed. Instead, please click "Play as Guest" on the main menu.';
            throw new Exception($e);
        }
    }
    $user_id = (int) $user->user_id;

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

    // check IP validity
    $country_code = '?';
    if (!check_ip_validity($pdo, $ip, $user)) {
        $cam_link = urlify('https://jiggmin2.com/cam', 'Contact a Mod');
        $msg = 'Please disable your proxy/VPN to connect to PR2. '.
            "If you feel this is a mistake, please use $cam_link to contact a member of the PR2 staff team.";
        throw new Exception($msg);
    }
    ensure_ip_country_from_valid_existing($pdo, $ip); // if possible, ensure country code isn't ?

    // create variables from user data in db
    $user_id = (int) $user->user_id;
    $user_name = $user->name;
    $group = (int) $user->power;

    // sanity: login name identical to the one in the db?
    // this won't be triggered unless some real funny business is going on
    $names_match = strtolower($login->user_name) === strtolower($user_name);
    if ((!$token_login || !is_empty($login->user_name)) && !$guest_login && !$names_match) {
        throw new Exception('The names don\'t match. If this error persists, contact a member of the PR2 Staff Team.');
    }

    // sanity: valid name?
    if (!$token_login) {
        if (strlen(trim($login->user_name)) < 2) { // too short?
            throw new Exception('Your name must be at least 2 characters long.');
        } elseif (strlen(trim($login->user_name)) > 20) { // too long?
            throw new Exception('Your name cannot be more than 20 characters long.');
        }
    }

    // generate a login token for future requests
    $token = random_str(32);
    token_insert($pdo, $user->user_id, $token);
    if ($remember && !$guest_login) {
        $token_expire = time() + 2592000; // one month
        setcookie('token', $token, $token_expire, '/', $_SERVER['SERVER_NAME'], false, true);
    } else {
        setcookie('token', '', time() - 3600, '/', $_SERVER['SERVER_NAME'], false, true);
    }

    // sanity check: if a guild server, is the user in the guild?
    $ps_staff = $group === 3 || ($group === 2 && (int) $server->guild_id === 205);
    $is_fred = $user_id === FRED;
    if ((int) $server->guild_id !== 0 && (int) $user->guild !== (int) $server->guild_id && !$ps_staff && !$is_fred) {
        throw new Exception('You must be a member of this guild to join this server.');
    }

    // sanity check: already logged into another server?
    if ($user->server_id > 0 && $user->server_id != $server->server_id) {
        throw new Exception('This account is already logged in on a different server.');
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

    // select their following list
    $following_list = following_select_list($pdo, $user_id);
    foreach ($following_list as $fl) {
        $following[] = $fl->following_id;
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

    // select their favorites
    $fav_levels_result = favorite_levels_select_ids($pdo, $user_id);
    foreach ($fav_levels_result as $level) {
        $favorite_levels[] = (int) $level->level_id;
    }

    // get their EXP gained today
    $exp_today_id = exp_today_select($pdo, 'id-'.$user_id);
    $exp_today_ip = exp_today_select($pdo, 'ip-'.$ip);
    $exp_today = max($exp_today_id, $exp_today_ip);

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

    // returning after a while?
    $user->returning = false;
    if ($user->time < time() - 5184000) {
        $user->returning = true;
        message_send_welcome_back($pdo, $user_name, $user_id);
    }

    // send this info to the socket server
    $send = new stdClass();
    $send->login = $login;
    $send->user = $user;
    $send->stats = $stats;
    $send->following = $following;
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
    $result = json_decode(preg_replace('/[[:cntrl:]]/', '', $result));
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
    $ret->email = !is_empty($user->email) && strlen($user->email) > 0; // email set?
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
    if (strpos($ret->error, 'login token') !== false) {
        $ret->resetToken = true;
    }
} finally {
    die(json_encode($ret));
}
