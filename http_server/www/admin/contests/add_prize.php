<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/contests/add_prize_fns.php';
require_once HTTP_FNS . '/pages/contests/part_vars.php';
require_once QUERIES_DIR . '/contests/contest_select.php';
require_once QUERIES_DIR . '/contest_prizes/contest_prize_select_id.php';
require_once QUERIES_DIR . '/contest_prizes/contest_prize_insert.php';
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php';

$ip = get_ip();
$contest_id = (int) find('contest_id', 0);
$action = find('action', 'form');

try {
     // rate limiting
     rate_limit('add-contest-prize-'.$ip, 30, 10);
     rate_limit('add-contest-prize-'.$ip, 5, 2);

    // sanity check: is a valid contest ID specified?
    if (is_empty($contest_id, false)) {
        throw new Exception("Invalid contest ID specified.");
    }

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
    // header
    output_header('Add Contest Prize', true, true);

    // get contest info
    $contest = contest_select($pdo, $contest_id, false, true);
    if ($contest == false || empty($contest)) {
        throw new Exception("Could not find a contest with that ID.");
    }

    // form
    if ($action === 'form') {
        output_form($contest);
        output_footer();
    } // add
    elseif ($action === 'add') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        add_contest_prize($pdo, $admin, $contest);
    } // unknown handler
    else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    output_footer();
} finally {
    die();
}
