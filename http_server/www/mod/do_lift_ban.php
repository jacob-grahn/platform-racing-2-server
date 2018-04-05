<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . '/../../queries/bans/ban_lift.php';
require_once __DIR__ . '/../../queries/staff/actions/mod_action_insert.php';

$ban_id = (int) default_post('ban_id', 0);
$reason = default_post('reason', 'They bribed me with skittles!');
$reason = $reason . ' @' . date('M j, Y g:i A'); // add time/date to lift reason
$ip = get_ip();

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // rate limiting
    rate_limit('mod-do-lift-ban-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're a moderator
    $mod = check_moderator($pdo);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // sanity check: are any values blank?
    if (is_empty($ban_id, false) || is_empty($reason)) {
        throw new Exception('Some information is missing.');
    }

    // lift the ban
    $lifted_by = $mod->name;
    ban_lift($pdo, $ban_id, $lifted_by, $reason);

    //record the change
    $user_id = $mod->user_id;
    $name = $mod->name;
    if ($reason != '') {
        $disp_reason = "Reason: " . htmlspecialchars($reason);
    } else {
        $disp_reason = "There was no reason given";
    }
    mod_action_insert($pdo, $user_id, "$name lifted ban $ban_id from $ip. $disp_reason.", 0, $ip);

    //redirect to a page showing the lifted ban
    header("Location: //pr2hub.com/bans/show_record.php?ban_id=$ban_id");
    die();
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Lift Ban', true);
    echo "Error: $error";
    output_footer();
}
