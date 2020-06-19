<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/mod_actions.php';

$start = (int) default_get('start', 0);
$count = (int) default_get('count', 25);
$ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-action-log-'.$ip, 5, 3);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $staff = is_staff($pdo, token_login($pdo), false, true);

    // get actions for this page
    $actions = mod_actions_select($pdo, $start, $count);

    // output header
    output_header('Mod Action Log', $staff->mod, $staff->admin);

    // navigation
    output_pagination($start, $count);
    echo '<p>---</p>';

    // output actions
    foreach ($actions as $action) {
        $msg = htmlspecialchars($action->message, ENT_QUOTES);
        $time = date('M j, Y g:i A', $action->time);
        echo "<p><span class='date'>$time</span> -- $msg</p>";
    }

    echo '<p>---</p>';
    output_pagination($start, $count);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error", $staff->mod, $staff->admin);
    echo "Error: $error";
} finally {
    output_footer();
}
