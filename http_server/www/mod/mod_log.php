<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/staff/actions/mod_actions_select.php';

$start = (int) default_get('start', 0);
$count = (int) default_get('count', 25);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-action-log-'.$ip, 5, 3);

    //connect
    $pdo = pdo_connect();

    //make sure you're a moderator
    $mod = check_moderator($pdo, false);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // get actions for this page
    $actions = mod_actions_select($pdo, $start, $count);

    // output header
    output_header('Mod Action Log', true);

    //navigation
    output_pagination($start, $count);
    echo('<p>---</p>');

    //output actions
    foreach ($actions as $row) {
        echo("<p><span class='date'>$row->time</span> -- ".htmlspecialchars($row->message)."</p>");
    }

    echo('<p>---</p>');
    output_pagination($start, $count);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Mod Action Log', true);
    echo "Error: $error";
} finally {
    output_footer();
    die();
}
