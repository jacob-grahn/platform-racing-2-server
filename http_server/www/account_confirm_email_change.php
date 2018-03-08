<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/output_fns.php';

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
    $db = new DB();

    // look up pending change info by code
    $row = $db->grab_row('changing_email_select', array($code), 'No pending change was found.');

    // get the variables from the pending change
    $user_id = $row->user_id;
    $old_email = $row->old_email;
    $new_email = $row->new_email;
    $change_id = $row->change_id;

    // safety first
    $safe_old_email = htmlspecialchars($old_email);
    $safe_new_email = htmlspecialchars($new_email);

    // push the change through
    $db->call('changing_email_complete', array($change_id, $ip), 'Could not confirm the change.');
    $db->call('user_update_email', array($user_id, $old_email, $new_email), 'Could not update your email.');

    // tell it to the world
    output_header('Confirm Email Change');
    echo "Great success! Your email address has been changed from \"$safe_old_email\" to \"$safe_new_email\".";
    output_footer();
} catch (Exception $e) {
    output_header('Confirm Email Change');
    echo $e->getMessage();
    output_footer();
}
