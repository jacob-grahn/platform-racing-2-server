<?php
require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/player_search_fns.php';
require_once QUERIES_DIR . '/bans/bans_select_by_user_id.php';
require_once QUERIES_DIR . '/bans/bans_select_by_ip.php';

$ip = get_ip();

output_header("View Priors");

echo '<center><font face="Gwibble" class="gwibble">-- View Priors --</font></center>'
    .'<br>';

try {
    // rate limit
    rate_limit("gui-view-priors-" . $ip, 5, 1);
    rate_limit("gui-view-priors-" . $ip, 30, 5);
    
    //connect
    $pdo = pdo_connect();
    $user_id = token_login($pdo);
    $user = user_select($pdo, $user_id);
    
    $banned = 'No';
    $row = query_if_banned($pdo, $user->user_id, $user->ip);
    
    // give some more info on the current ban in effect if there is one
    if ($row !== false) {
        $ban_id = $row->ban_id;
        $reason = htmlspecialchars($row->reason);
        $ban_end_date = date("F j, Y, g:i a", $row->expire_time);
        if ($row->ip_ban == 1 && $row->account_ban == 1 && $row->banned_name == $user_name) {
            $ban_type = 'account and IP are';
        } elseif ($row->ip_ban == 1) {
            $ban_type = 'IP is';
        } elseif ($row->account_ban == 1) {
            $ban_type = 'account is';
        }
        $banned = "<a href='show_record.php?ban_id=$ban_id'>Yes</a>. Your $ban_type banned until $ban_end_date. ".
                    "Reason: $reason";
    }
    
    // count how many times they have been banned
    $account_bans = bans_select_by_user_id($pdo, $user->user_id);
    $account_ban_count = (int) count($account_bans);
    $account_ban_list = create_ban_list($account_bans);
    $acc_lang = 'time';
    if ($account_ban_count !== 1) {
        $acc_lang = 'times';
    }

    // look for all historical bans given to this ip address
    $ip_bans = bans_select_by_ip($pdo, $user->ip);
    $ip_ban_count = (int) count($ip_bans);
    $ip_ban_list = create_ban_list($ip_bans);
    $ip_lang = 'time';
    if ($ip_ban_count !== 1) {
        $ip_lang = 'times';
    }

    // output the results
    echo "<p>Currently banned: $banned</p>"
        ."<p>Your account has been banned $account_ban_count $acc_lang.</p> $account_ban_list"
        ."<p>Your IP has been banned $ip_ban_count $ip_lang.</p> $ip_ban_list"
        .'<p>Priors expire one year after the ban\'s expire date.</p>';
} catch (Exception $e) {
    $safe_error = htmlspecialchars($e->getMessage());
    echo "<br /><i>Error: $safe_error</i>";
} finally {
    output_footer();
    die();
}
