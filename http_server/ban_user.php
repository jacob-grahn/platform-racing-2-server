<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/bans.php';
require_once QUERIES_DIR . '/mod_actions.php';

$ban_name = default_post('banned_name');
$duration = (int) default_post('duration', 60);
$reason = default_post('reason', '');
$log = default_post('record', '');
$using_mod_site = default_post('using_mod_site', 'no');
$redirect = default_post('redirect', 'no');
$type = default_post('type', 'both');
$force_ip = default_post('force_ip', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // rate limiting
    rate_limit('ban-'.$ip, 5, 1);

    // connect
    $pdo = pdo_connect();

    // sanity check: was a name passed to the script?
    if (is_empty($ban_name)) {
        throw new Exception('Invalid name provided');
    }

    // check for permission
    $mod = check_moderator($pdo);

    // get variables from the mod variable
    $mod_uid = (int) $mod->user_id;
    $mod_name = $mod->name;
    $mod_power = $mod->power;

    // limit ban length
    $duration = $duration > $mod->max_ban ? $mod->max_ban : $duration;
    $time = (int) time();
    $ends = $time + $duration;

    // throttle bans using PDO
    $throttle = throttle_bans($pdo, $mod_uid);
    $recent_ban_count = $throttle->recent_ban_count;
    if ($recent_ban_count > $mod->bans_per_hour) {
        throw new Exception("You have reached the cap of $mod->bans_per_hour bans per hour.");
    }

    // get the banned user's info using PDO
    $target = user_select_by_name($pdo, $ban_name);
    if ($target === false) {
        throw new Exception("The user you're trying to ban doesn't exist.");
    }

    // make some variables
    $ban_ip = !is_empty($force_ip) && filter_var($force_ip, FILTER_VALIDATE_IP) ? $force_ip : $target->ip;
    $banned_power = $target->power;
    $ban_uid = (int) $target->user_id;

    // throw out non-banned info, set ban types
    $is_ip = 0;
    $is_acc = 0;
    switch ($type) {
        case 'both':
            $is_ip = 1;
            $is_acc = 1;
            break;
        case 'account':
            $is_ip = 0;
            $is_acc = 1;
            break;
        case 'ip':
            $is_ip = 1;
            $is_acc = 0;
            break;
        default:
            throw new Exception("Invalid ban type specified.");
        break;
    }

    // permission check
    if ($banned_power >= 2 || $mod_power < 2) {
        throw new Exception("You lack the power to ban $ban_name.");
    }

    // don't ban guest accounts, just the ip
    if ($banned_power == 0) {
        $ban_uid = 0;
        $ban_name = '';
    }

    // add the ban using pdo
    $result = ban_user($pdo, $ban_ip, $ban_uid, $mod_uid, $ends, $reason, $log, $ban_name, $mod_name, $is_ip, $is_acc);
    if ($result === false) {
        throw new Exception('Could not record ban.');
    }

    // remove login token
    tokens_delete_by_user($pdo, $ban_uid);

    // make things pretty
    $disp_duration = format_duration($duration);
    $disp_reason = is_empty($reason) ? 'no reason given' : "reason: $reason";

    // make account/ip ban detection pretty courtesy of data_fns.php
    $is_account_ban = check_value($is_acc, 1);
    $is_ip_ban = check_value($is_ip, 1);

    // make expire time pretty
    $disp_expire_time = date('Y-m-d H:i:s', $ends);

    // record the ban in the action log
    $msg = "$mod_name banned $ban_name from $ip "
        ."{duration: $disp_duration, "
        ."account_ban: $is_account_ban, "
        ."ip_ban: $is_ip_ban, "
        ."expire_time: $disp_expire_time, "
        ."$disp_reason}";
    mod_action_insert($pdo, $mod_uid, $msg, 0, $ip);

    if ($using_mod_site === 'yes' && $redirect === 'yes') {
        $url_ip = urlencode($force_ip);
        header("Location: /mod/player_info.php?user_id=$ban_uid&force_ip=$url_ip");
        die();
    } else {
        $disp_name = htmlspecialchars($ban_name, ENT_QUOTES);
        $guest_msg = "Guest [$ban_ip] has been banned for $duration seconds.";
        $user_msg = "$disp_name has been banned for $duration seconds.";
        $ret->success = true;
        $ret->message = $ban_uid === 0 ? $guest_msg : $user_msg;
    }
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
