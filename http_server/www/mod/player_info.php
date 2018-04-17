<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/users/user_select.php';
require_once __DIR__ . '/../../queries/pr2/pr2_select.php';
require_once __DIR__ . '/../../queries/pr2/pr2_select_true_rank.php';
require_once __DIR__ . '/../../queries/bans/bans_select_by_user_id.php';
require_once __DIR__ . '/../../queries/bans/bans_select_by_ip.php';

$user_id = (int) default_get('user_id', 0);
$force_ip = find_no_cookie('force_ip');
$ip = find_no_cookie('ip');

$mod_ip = get_ip();

try {
    // sanity check: make sure something is fed to the script
    if (is_empty($user_id, false) && is_empty($force_ip) && is_empty($ip)) {
        throw new Exception("Invalid user ID specified.");
    }

    // sanity check: send IP to ip_info.php
    if ((!is_empty($ip) || !is_empty($force_ip)) && is_empty($user_id, false)) {
        $ip = urlencode($ip);
        header("Location: ip_info.php?ip=$ip");
        die();
    }

    // rate limiting
    rate_limit('mod-player-info-'.$mod_ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo, false);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // header
    output_header('Player Info', true);

    // check if they are currently banned
    $banned = 'No';
    $row = query_if_banned($pdo, $user_id, $ip);

    //give some more info on the current ban in effect if there is one
    if ($row !== false) {
        $ban_id = $row->ban_id;
        $reason = htmlspecialchars($row->reason);
        $ban_end_date = date("F j, Y, g:i a", $row->expire_time);
        if ($row->ip_ban == 1 && $row->account_ban == 1 && $row->banned_name == $user_name) {
            $ban_type = 'account and ip are';
        } elseif ($row->ip_ban == 1) {
            $ban_type = 'ip is';
        } elseif ($row->account_ban == 1) {
            $ban_type = 'account is';
        }
        $banned = "<a href='../bans/show_record.php?ban_id=$ban_id'>Yes.</a>"
                 ."This $ban_type banned until $ban_end_date. Reason: $reason";
    }
    
    // get dem infos
    $user = user_select($pdo, $user_id);
    $pr2 = pr2_select($pdo, $user_id, true);

    // make neat variables
    if ($pr2 !== false) {
        $rank = (int) pr2_select_true_rank($pdo, $user_id);
        $hats = count(explode(',', $pr2->hat_array)) - 1;
    }
    $status = $user->status;
    $ip = $user->ip;
    $user_name = $user->name;

    // count how many times they have been banned
    $account_bans = bans_select_by_user_id($pdo, $user_id);
    $account_ban_count = (int) count($account_bans);
    $account_ban_list = create_ban_list($account_bans);
    $acc_lang = 'time';
    if ($account_ban_count !== 1) {
        $acc_lang = 'times';
    }

    // override ip
    $overridden_ip = '';
    if (isset($force_ip) && $force_ip != '') {
        $overridden_ip = $ip;
        $ip = $force_ip;
    }

    // look for all historical bans given to this ip address
    $ip_bans = bans_select_by_ip($pdo, $ip);
    $ip_ban_count = (int) count($ip_bans);
    $ip_ban_list = create_ban_list($ip_bans);
    $ip_lang = 'time';
    if ($ip_ban_count !== 1) {
        $ip_lang = 'times';
    }

    // safety first
    $html_overridden_ip = htmlspecialchars($overridden_ip);
    $html_ip = htmlspecialchars($ip);
    $html_url_ip = htmlspecialchars(urlencode($ip));

    // output the results
    if (!is_empty($user_id)) {
        $html_user_name = htmlspecialchars($user_name);
        echo "<p>Name: <b>$html_user_name</b></p>"
            ."<p>IP: <del>$html_overridden_ip</del> <a href='ip_info.php?ip=$html_url_ip'>$html_ip</a></p>"
            ."<p>Status: $status</p>";
        
        // display pr2 info
        if ($pr2 !== false) {
            echo "<p>Rank: $rank<p>"
                ."<p>Hats: $hats<p>";
        }
        
        // display ban info
        echo "<p>Currently banned: $banned</p>"
            ."<p>Account has been banned $account_ban_count $acc_lang.</p> $account_ban_list"
            ."<p>IP has been banned $ip_ban_count $ip_lang.</p> $ip_ban_list"
            .'<p>---</p>'
            ."<p><a href='ban.php?user_id=$user_id&force_ip=$force_ip'>Ban User</a></p>";
    } else {
        echo "<p>IP: <a href='ip_info.php?ip=$html_url_ip'>$html_ip</a></p>"
        ."<p>Currently banned: $banned</p>"
        ."<p>IP has been banned $ip_ban_count $ip_lang.</p> $ip_ban_list";
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    output_footer();
    die();
}


function create_ban_list($bans)
{
    $str = '<p><ul>';
    foreach ($bans as $row) {
        $ban_date = date("F j, Y, g:i a", $row->time);
        $reason = htmlspecialchars($row->reason);
        $ban_id = $row->ban_id;
        $str .= "<li><a href='../bans/show_record.php?ban_id=$ban_id'>$ban_date:</a> $reason";
    }
    $str .= '</ul></p>';
    return $str;
}
