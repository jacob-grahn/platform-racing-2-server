<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/player_search_fns.php';
require_once QUERIES_DIR . '/bans.php';

$user_id = (int) default_get('user_id', 0);
$name = default_get('name', '');
$force_ip = default_get('force_ip');
$ip = default_get('ip');

$mod_ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-player-info-'.$mod_ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $staff = is_staff($pdo, token_login($pdo), false, true);

    // sanity check: send IP to ip_info.php
    if ((!is_empty($ip) || !is_empty($force_ip)) && is_empty($user_id, false) && is_empty($name)) {
        $ip = urlencode($ip);
        header("Location: ip_info.php?ip=$ip");
    }

    // header
    output_header('Player Info', $staff->mod, $staff->admin);

    // determine mode
    $mode = '';
    if (!is_empty($name) && $user_id === 0) {
        $mode = 'name';
        $user = user_select_by_name($pdo, $name, true);
    } elseif ($user_id > 0) {
        $mode = 'ID';
        $user = user_select($pdo, $user_id, true);
    } else {
        // output search without gwibble text
        output_search('', false);
        throw new Exception('');
    }

    // sanity check: does the user exist?
    if ($user === false) {
        output_search('', false);
        throw new Exception("Could not find a user with that $mode.");
    }
    $user_name = $user->name;

    // output search without gwibble text
    output_search($user->name, false);

    // give some more info on the most severe ban (game > social, longest duration) currently in effect if there is one
    $banned = 'No';
    $ban = check_if_banned($pdo, $user->user_id, $user->ip, 'b', false);
    if (!empty($ban)) {
        $ban_id = (int) $ban->ban_id;
        $reason = htmlspecialchars($ban->reason, ENT_QUOTES);
        $ban_end_date = date("F j, Y, g:i a", $ban->expire_time);
        if ((int) $ban->ip_ban === 1 && (int) $ban->account_ban === 1 && $ban->banned_name === $user_name) {
            $ban_type = 'account and IP are';
        } elseif ((int) $ban->ip_ban === 1) {
            $ban_type = 'IP is';
        } elseif ((int) $ban->account_ban === 1) {
            $ban_type = 'account is';
        }
        $scope = $ban->scope === 's' ? 'socially banned' : 'banned';
        $banned = "<a href='/bans/show_record.php?ban_id=$ban_id'>Yes</a>. This $ban_type $scope until $ban_end_date. "
            ."Reason: $reason";
    }

    // get the pr2 information
    $pr2 = pr2_select($pdo, $user->user_id, true);

    // make neat variables
    if ($pr2 !== false) {
        $rank = (int) pr2_select_true_rank($pdo, $user->user_id);
        $hats = count(explode(',', $pr2->hat_array)) - 1;
    }
    $status = $user->status;
    $ip = $user->ip;
    $user_id = (int) $user->user_id;

    // count how many times they have been banned
    $account_bans = bans_select_by_user_id($pdo, $user->user_id);
    $account_ban_count = (int) count($account_bans);
    $account_ban_list = create_ban_list($account_bans);
    $acc_lang = $account_ban_count !== 1 ? 'times' : 'time';

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
    $ip_lang = $ip_ban_count !== 1 ? 'times' : 'time';

    // safety first
    $html_overridden_ip = htmlspecialchars($overridden_ip, ENT_QUOTES);
    $html_ip = htmlspecialchars($ip, ENT_QUOTES);
    $html_url_ip = htmlspecialchars(urlencode($ip), ENT_QUOTES);

    // output the results
    $html_user_name = htmlspecialchars($user_name, ENT_QUOTES);
    echo "<p>User ID: $user_id</p>"
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
        ."<p>IP has been banned $ip_ban_count $ip_lang.</p> $ip_ban_list";

    // display options
    echo '<p>---</p>'
        .'<p>'
        ."<a href='ban.php?user_id=$user_id&force_ip=$force_ip'>Ban User</a><br>"
        ."<a href='purge_tokens.php?user_id=$user_id'>Purge Tokens</a>"
        .'</p>';
} catch (Exception $e) {
    if ($e->getMessage() !== '') {
        $error = $e->getMessage();
        echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    }
} finally {
    output_footer();
}
