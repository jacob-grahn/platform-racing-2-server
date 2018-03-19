<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../www/mod/mod_fns.php';
require_once __DIR__ . '/../../queries/mod_actions/mod_actions_select.php';

$start = find('start', 0);
$count = find('count', 25);

try {
    //connect
    $pdo = pdo_connect();

    //make sure you're an admin
    $admin = check_moderator($pdo, false, 3);

    //get actions for this page
    $actions = admin_actions_select($pdo, $start, $count);

    // output header
    output_header('Admin Action Log', true, true);

    //navigation
    output_pagination($start, $count);
    echo('<p>---</p>');


    //output actions
    foreach ($actions as $row) {
        echo("<p><span class='date'>$row->time</span> -- ".htmlspecialchars($row->message)."</p>");
    }


    echo('<p>---</p>');
    output_pagination($start, $count);
    output_footer();
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: '.$e->getMessage();
    output_footer();
}
