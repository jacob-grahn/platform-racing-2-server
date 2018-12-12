<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/staff/actions/admin_actions_select.php';

$start = find('start', 0);
$count = find('count', 25);

try {
    // rate limiting
    rate_limit('admin-log-'.$ip, 60, 10, 'Wait a bit before searching again.');
    rate_limit('admin-log-'.$ip, 5, 2);
    
    // connect
    $pdo = pdo_connect();

    //make sure you're an admin
    is_staff($pdo, token_login($pdo), true, true, 3);

    //get actions for this page
    $actions = admin_actions_select($pdo, $start, $count);

    // output header
    output_header('Admin Action Log', true, true);

    // navigation
    output_pagination($start, $count);
    echo '<p>---</p>';

    // output actions
    foreach ($actions as $row) {
        $msg = htmlspecialchars($row->message, ENT_QUOTES);
        echo "<p><span class='date'>$row->time</span> -- $msg</p>";
    }

    echo '<p>---</p>';
    output_pagination($start, $count);
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
} finally {
    output_footer();
}
