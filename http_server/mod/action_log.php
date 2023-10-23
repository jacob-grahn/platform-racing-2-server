<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/mod_actions.php';
require_once QUERIES_DIR . '/prize_actions.php';

$mode = strtolower(default_get('mode', 'mod'));
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

    // check mode
    $mode = !in_array($mode, ['mod', 'prize']) ? 'mod' : $mode;

    // output header
    $disp_mode = ucfirst($mode);
    output_header("$disp_mode Action Log", $staff->mod, $staff->admin);

    // don't let trial mods use this
    if ($staff->trial) {
        throw new Exception('You lack the power to access this resource.');
    }

    // get actions for this page
    $fn = "{$mode}_actions_select";
    $actions = $fn($pdo, $start, $count);
    
    // navigation
    output_pagination($start, $count, "&mode=$mode");
    echo '<p>---</p>';

    // output actions
    foreach ($actions as $action) {
        $msg = htmlspecialchars($action->message, ENT_QUOTES);
        $time = date('M j, Y g:i A', $action->time);
        echo "<p><span class='date'>$time</span> -- $msg</p>";
    }

    echo '<p>---</p>';
    output_pagination($start, $count, "&mode=$mode");
} catch (Exception $e) {
    output_error_page($e->getMessage(), @$staff);
} finally {
    output_footer();
}
