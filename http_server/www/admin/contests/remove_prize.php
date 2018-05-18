<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/contests/part_vars.php';
require_once QUERIES_DIR . '/contests/contest_select.php';
require_once QUERIES_DIR . '/contest_prizes/contest_prizes_select_by_contest.php';
require_once QUERIES_DIR . '/contest_prizes/contest_prize_delete.php';
require_once QUERIES_DIR . '/staff/actions/admin_action_insert.php';

$ip = get_ip();
$contest_id = (int) find('contest_id', 0);
$action = find('action', 'form');

try {
    // rate limiting
    rate_limit('remove-contest-prize-'.$ip, 30, 10);
    rate_limit('remove-contest-prize-'.$ip, 5, 2);

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
    output_header('Remove Contest Prize', true, true);

    // get contest info
    $contest = contest_select($pdo, $contest_id, false, true);
    if ($contest == false || empty($contest)) {
        throw new Exception("Could not find a contest with that ID.");
    }

    // get prizes info for this contest
    $prizes = contest_prizes_select_by_contest($pdo, $contest->contest_id);
    if ($prizes == false || empty($prizes)) {
        throw new Exception("This contest doesn't currently have any prizes.");
    }

    // form
    if ($action === 'form') {
        $html_contest_name = htmlspecialchars($contest->contest_name);
        echo "Remove Prizes from <b>$html_contest_name</b>"
            ."<br><br>"
            ."<form method='post'>";

        foreach ($prizes as $prize) {
            $prize_id = (int) $prize->prize_id;

            // build variable name
            $prize = validate_prize($prize->part_type, $prize->part_id);
            $part_type = $prize->type;
            $part_id = (int) $prize->id;
            $is_epic = (bool) $prize->epic;

            // make the display name
            $part_name = to_part_name($part_type, $part_id);
            $disp_type = ucfirst($part_type);
            $prize_name = "$part_name $disp_type";
            if ($is_epic == true) {
                $prize_name = "Epic " . $prize_name;
            }

            echo "<input type='checkbox' name='prize_$prize_id' id='prize_$prize_id'>"
                ."<label for='prize_$prize_id'> $prize_name</label>"
                ."<input type='hidden' name='prize_name_$prize_id' value='$prize_name'><br>";
        }

        echo '<input type="hidden" name="action" value="remove">';
        echo '<input type="hidden" name="contest_id" value="'.(int) $contest->contest_id.'"><br><br>';

        echo '<input type="submit" value="Remove Contest Prize(s)">&nbsp;(no confirmation!)';
        echo '</form>';

        echo '<br>';
        echo '---';
        echo '<br>';
        echo '<pre>Check the boxes of the prizes you wish to remove.<br>'
            .'When you\'re done, click "Remove Contest Prize(s)".</pre>';
    } // add
    elseif ($action === 'remove') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }

        // make some variables
        $contest_id = (int) $contest->contest_id;
        $contest_name = $contest->contest_name;
        $html_contest_name = htmlspecialchars($contest_name);
        $removed = 0; // count the number of prizes removed

        // determine if we're removing these prizes
        foreach ($prizes as $prize) {
            // make some nice variables
            $prize_id = $prize->prize_id;
            $remove_prize = (bool) $_POST["prize_$prize_id"];

            // move on if not removing this prize
            if ($remove_prize === false) {
                continue;
            }

            // some names of things
            $prize_name = htmlspecialchars(default_post("prize_name_$prize_id", ''));

            // do it
            $operation = contest_prize_delete($pdo, $prize_id, true);
            $removed++; // increment counter
            if ($operation !== false) {
                echo "<b>The $prize_name was deleted from $html_contest_name.</b><br>";
            } else {
                echo "<span style='color: red;'>The $prize_name was not deleted from $html_contest_name.</span><br>";
                continue;
            }

            // log the action in the admin log
            $admin_name = $admin->name;
            $admin_id = $admin->user_id;
            admin_action_insert(
                $pdo,
                $admin_id,
                "$admin_name removed the $prize_name from contest $contest_name from $ip. {
                    contest_id: $contest_id,
                    contest_name: $contest_name,
                    prize_id: $prize_id}",
                0,
                $ip
            );
        }

        // if no prizes were selected to be removed, tell the user
        if ($removed === 0) {
            throw new Exception("No prizes were selected to be removed.");
        }

        // output the page
        echo "<br>All operations completed. The results can be seen above.";
        echo "<br><br>";
        echo "<a href='add_prize.php?contest_id=$contest_id'>&lt;- Add Prize</a><br>";
        echo "<a href='/contests/contests.php'>&lt;- All Contests</a>";
    } // unknown handler
    else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
} finally {
    output_footer();
    die();
}
