<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/contests/add_contest_fns.php';
require_once QUERIES_DIR . '/contests/contest_insert.php';
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php';

$ip = get_ip();
$action = find('action', 'form');

try {
    // rate limiting
    rate_limit('add-contest-'.$ip, 30, 5);
    rate_limit('add-contest-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, true, 3);

    // add
    if ($action === 'add') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        add_contest($pdo, $admin);
    } // form
    elseif ($action === 'form') {
        output_header('Add Contest', true, true);
        output_form();
        output_footer();
    } // no matching action
    else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . $e->getMessage() . '<br><br><a href="javascript:history.back()"><- Go Back</a>';
    output_footer();
}
