<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/admin/contests/add_prize_fns.php';
require_once QUERIES_DIR . '/admin_actions.php';
require_once QUERIES_DIR . '/contests.php';
require_once QUERIES_DIR . '/contest_prizes.php';

$ip = get_ip();
$contest_id = (int) find_no_cookie('contest_id', 0);
$action = default_post('action', 'form');

try {
    // rate limiting
    rate_limit('add-contest-prize-'.$ip, 30, 10);
    rate_limit('add-contest-prize-'.$ip, 5, 2);

    // sanity check: is a valid contest ID specified?
    if (is_empty($contest_id, false)) {
        throw new Exception('Invalid contest ID specified.');
    }

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, null, true, 3);

    // get contest info
    $contest = contest_select($pdo, $contest_id, false, true);
    if ($contest === false || empty($contest)) {
        throw new Exception('Could not find a contest with that ID.');
    }

    // build page
    if ($action === 'form') {
        output_header('Add Contest Prize', true, true);
        output_form($contest);
        output_footer();
    } elseif ($action === 'add') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        add_contest_prize($pdo, $admin, $contest);
    } else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Error');
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    output_footer();
}
