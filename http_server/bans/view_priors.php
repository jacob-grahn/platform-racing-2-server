<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/player_search_fns.php';
require_once QUERIES_DIR . '/bans.php';

$ip = get_ip();

try {
    // rate limit
    rate_limit("gui-view-priors-" . $ip, 5, 1);
    rate_limit("gui-view-priors-" . $ip, 30, 5);

    // connect
    $pdo = pdo_connect();
    $user_id = (int) token_login($pdo, true, true, 'n');
    $staff = is_staff($pdo, $user_id, false);

    output_header("View Priors", $staff->mod, $staff->admin);
    echo '<center><font face="Gwibble" class="gwibble">-- View Priors --</font></center><br>';

    $user = user_select($pdo, $user_id);

    // give some more info on the most severe ban (game > social, longest duration) currently in effect if there is one
    $banned = 'No';
    $ban = check_if_banned($pdo, $user->user_id, $user->ip, 'b', false);
    if ($ban !== false) {
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
        $banned = "<a href='show_record.php?ban_id=$ban_id'>Yes</a>. Your $ban_type $scope until $ban_end_date. "
            ."Reason: $reason";
    }

    // count how many times they have been banned
    $account_bans = bans_select_by_user_id($pdo, $user->user_id);
    $account_ban_count = (int) count($account_bans);
    $account_ban_list = create_ban_list($account_bans);
    $acc_lang = $account_ban_count !== 1 ? 'times' : 'time';

    // look for all historical bans given to this ip address
    $ip_bans = bans_select_by_ip($pdo, $user->ip);
    $ip_ban_count = (int) count($ip_bans);
    $ip_ban_list = create_ban_list($ip_bans);
    $ip_lang = $ip_ban_count !== 1 ? 'times' : 'time';

    // output the results
    echo "<p>Currently banned: $banned</p>"
        ."<p>Your account has been banned $account_ban_count $acc_lang.</p> $account_ban_list"
        ."<p>Your IP has been banned $ip_ban_count $ip_lang.</p> $ip_ban_list"
        .'<p>Priors expire one year after the ban\'s expiration date.</p>';
} catch (Exception $e) {
    if (!isset($staff)) {
        output_header('Error');
    }
    $error = $e->getMessage();
    echo "<br /><center><i>Error: $error</i></center>";
} finally {
    output_footer();
}
