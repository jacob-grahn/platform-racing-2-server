<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/edit_contest_fns.php';
require_once QUERIES_DIR . '/contests/contest_select.php';
require_once QUERIES_DIR . '/contests/contest_update.php';
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php';

$ip = get_ip();
$action = find_no_cookie('action', 'form');
$contest_id = (int) find_no_cookie('contest_id', 0);

try {
    // rate limiting
    rate_limit('edit-contest-'.$ip, 30, 5);
    rate_limit('edit-contest-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, true, 3);
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . $e->getMessage();
    output_footer();
    die();
}

try {
    // select contest
    $contest = contest_select($pdo, $contest_id, false, true);
    if (empty($contest) || $contest === false) {
        throw new Exception("Could not find a contest with that ID.");
    }

    // form
    if ($action === 'form') {
        output_header('Edit Contest', true, true);
        output_form($contest);
        output_footer();
    } // add
    elseif ($action === 'edit') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        edit_contest($pdo, $contest, $admin);
    } // unknown handler
    else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    output_header('Edit Contest', true, true);
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    output_footer();
} finally {
    die();
}
