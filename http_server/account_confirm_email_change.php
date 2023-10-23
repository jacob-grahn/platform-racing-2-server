<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/changing_emails.php';

$code = default_get('code', '');
$ip = get_ip();

try {
    // sanity check: check for the code
    if (is_empty($code)) {
        throw new Exception('No code found.');
    }

    // rate limiting
    rate_limit('account-confirm-email-change-'.$ip, 5, 1);

    // connect
    $pdo = pdo_connect();

    // look up pending change info by code
    $row = changing_email_select($pdo, $code);

    // get the variables from the pending change
    $user_id = (int) $row->user_id;
    $old_email = $row->old_email;
    $new_email = $row->new_email;
    $change_id = (int) $row->change_id;

    // push the change through
    changing_email_complete($pdo, $change_id, $ip);
    user_update_email($pdo, $user_id, $old_email, $new_email);

    // make some variables
    $safe_old_email = htmlspecialchars($old_email, ENT_QUOTES);
    $safe_new_email = htmlspecialchars($new_email, ENT_QUOTES);

    // tell it to the world
    output_header('Confirm Email Change');
    echo "Great success! Your email address has been changed from $safe_old_email to $safe_new_email.";
} catch (Exception $e) {
    output_error_page($e->getMessage(), null, 'Confirm Email Change');
} finally {
    output_footer();
}
