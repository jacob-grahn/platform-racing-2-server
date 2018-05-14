<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/changing_emails/changing_email_select.php';
require_once QUERIES_DIR . '/changing_emails/changing_email_complete.php';
require_once QUERIES_DIR . '/users/user_update_email.php';

$code = $_GET['code'];
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
    $user_id = $row->user_id;
    $old_email = $row->old_email;
    $new_email = $row->new_email;
    $change_id = $row->change_id;

    // push the change through
    changing_email_complete($pdo, $change_id, $ip);
    user_update_email($pdo, $user_id, $old_email, $new_email);
    
    // make some variables
    $safe_old_email = htmlspecialchars($old_email);
    $safe_new_email = htmlspecialchars($new_email);

    // tell it to the world
    output_header('Confirm Email Change');
    echo "Great success! Your email address has been changed from $safe_old_email to $safe_new_email.";
} catch (Exception $e) {
    output_header('Confirm Email Change');
    echo $e->getMessage();
} finally {
    output_footer();
    die();
}
