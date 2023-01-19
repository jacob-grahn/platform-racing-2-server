<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/award_coins_fns.php';
require_once QUERIES_DIR . '/admin_actions.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/vault_coins_orders.php';

$ip = get_ip();
$action = default_post('action', 'form');
$user_id = (int) default_get('user_id', 0);

try {
    // rate limiting
    rate_limit('award-coins-'.$ip, 30, 5);
    rate_limit('award-coins-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, null, true, 3);

    // build page
    if ($action === 'form') {
        output_header('Award Coins', true, true);
        output_form(id_to_name($pdo, $user_id, true));
    } elseif ($action === 'award') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        award_coins($pdo, $admin);
    } else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . $e->getMessage() . '<br><br><a href="javascript:history.back()"><- Go Back</a>';
} finally {
    output_footer();
    die();
}
