<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/contests/part_vars.php';
require_once QUERIES_DIR . '/contests/contest_select.php';
require_once QUERIES_DIR . '/contest_prizes/contest_prize_select.php';
require_once QUERIES_DIR . '/contest_prizes/contest_prizes_select_by_contest.php';
require_once QUERIES_DIR . '/contest_winners/throttle_awards.php';
require_once QUERIES_DIR . '/contest_winners/contest_winner_insert.php';
require_once QUERIES_DIR . '/messages/message_insert.php';

$ip = get_ip();
$contest_id = (int) find('contest_id', 0);
$action = find('action', 'form');

try {
    // rate limiting
    rate_limit('award-contest-prize-'.$ip, 60, 10);
    rate_limit('award-contest-prize-'.$ip, 5, 2);

    // sanity check: is a valid contest ID specified?
    if (is_empty($contest_id, false)) {
        throw new Exception("Invalid contest ID specified.");
    }

    // connect
    $pdo = pdo_connect();

    // determine user id
    $user_id = (int) token_login($pdo, true);
    $is_staff = is_staff($pdo, $user_id);
    $is_mod = $is_staff->mod;
    $is_admin = $is_staff->admin;

    // get contest info
    $contest = contest_select($pdo, $contest_id, !$is_admin, true);

    // sanity check: does the contest exist?
    if (empty($contest) || $contest === false) {
        throw new Exception("Could not find a contest with that ID.");
    }

    // define some variables
    $contest_id = $contest->contest_id;
    $host_id = (int) $contest->user_id;

    // sanity check: is this user the contest owner, admin, or mod?
    if ($is_admin === false && $is_mod === false && $user_id !== $host_id) {
        $html_contest_name = htmlspecialchars($contest->contest_name);
        throw new Exception("You don't own $html_contest_name.");
    }

    // if not an admin/mod, throttle awards
    if ($user_id === $host_id && $is_admin === false && $is_mod === false) {
        $recent_awards = (int) throttle_awards($pdo, $contest_id, $user_id);
        $max_awards = (int) $contest->max_awards;
        if ($recent_awards >= $max_awards) {
            throw new Exception(
                "You've reached your maximum amount of awards for this week.<br>".
                "If you need to award more, please contact a member of the PR2 Staff Team.".
                "<br><br>".
                "<a href='contests.php'><- All Contests</a>"
            );
        }
    }
} catch (Exception $e) {
    output_header("Error", $is_mod, $is_admin);
    echo 'Error: ' . $e->getMessage();
    output_footer();
    die();
}

try {
    // header
    output_header('Award Prize', $is_mod, $is_admin);

    // get prizes info for this contest
    $prizes = contest_prizes_select_by_contest($pdo, $contest->contest_id);

    // sanity check: does this contest have any prizes set?
    if (empty($prizes) || $prizes === false) {
        throw new Exception("This contest doesn't currently have any prizes.");
    }

    // form
    if ($action === 'form') {
        $html_contest_name = htmlspecialchars($contest->contest_name);
        $max_awards = (int) $contest->max_awards;
        $recent_awards = (int) throttle_awards($pdo, $contest->contest_id, $contest->user_id);
        $lang = ['sets','times'];
        if ($max_awards === 1) {
            $lang = ['set','time'];
        }

        // start page
        echo "Award Prizes for <b>$html_contest_name</b><br><br>";
        if ($is_staff->mod === false && $is_staff->admin === false) {
            echo "You can award a maximum of <b>$max_awards</b> $lang[0] of prizes per week. "
                ."This means that you can click the \"Award Prize(s)\" button $max_awards $lang[1] per week. "
                ."If you have questions about how this works, please ask a member of the PR2 Staff Team for help. "
                ."<br><br>"
                ."So far, you have used <b>$recent_awards</b> of your allotted awards for the week.<br><br>";
        }
        echo "<form method='post'>";

        echo "Select Prizes to Award:<br>";
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
        echo '<br>';

        echo "PR2 Name: <input type='text' name='winner_name' maxlength='25'> (enter the winner's PR2 name here)<br>";
        echo "Comments: <input type='text' name='comment'> "
            ."(this should be used to explain why you're awarding this user these prizes)<br>";
        echo '<input type="hidden" name="action" value="award"><br>';
        echo '<input type="hidden" name="contest_id" value="'.(int) $contest->contest_id.'">';

        echo '<input type="submit" value="Award Prize(s)">&nbsp;(no confirmation!)';
        echo '</form>';

        echo '<br><br>';
        echo "<a href='contests.php'>&lt;- All Contests</a>";
        echo '<br><br>';
        echo '---';
        echo '<br>';
        echo '<pre>Check the boxes of the prizes you wish to award.'
            .'<br>When you\'re done, click "Award Prize(s)".';
        if ($is_staff->mod === false && $is_staff->admin === false) {
            echo '<br><br><b>WARNING: Awarding prizes to players who have not won your contest'
                .'will result in disciplinary action.<br>'
                .'If you have a special case and are unsure of what to do, '
                .'ask a member of the PR2 Staff Team for help.</b>';
        }
        echo '</pre>';
    } // award
    elseif ($action === 'award') {
        // validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }

        // check referrer
        require_trusted_ref('award prizes');

        // make some nice variables
        $winner_name = default_post('winner_name', '');
        $comment = default_post('comment', '');

        // sanity check: are all of the fields filled out?
        if (is_empty($winner_name) || is_empty($comment)) {
            throw new Exception("You must specify a winner and explain why you're awarding the prize(s) to them.");
        }

        // sanity check: does this user exist?
        $winner_id = name_to_id($pdo, $winner_name, true);
        if ($winner_id === false) {
            throw new Exception("Could not find a player with that name.");
        }
        $winner_id = (int) $winner_id;

        // safety first
        $html_winner_name = htmlspecialchars($winner_name);

        // check if the player has the part already
        $prizes_to_award = array();
        $errors = 0;
        foreach ($prizes as $prize) {
            // make some variables
            $prize_id = (int) $prize->prize_id;
            $part_type = $prize->part_type;
            $part_id = $prize->part_id;
            $award_prize = (bool) default_post("prize_$prize_id", null);

            // sanity check: if we're not awarding anything, move on
            if ($award_prize === false || is_null($award_prize)) {
                continue;
            }

            // check if the user has the part
            $has_part = has_part($pdo, $winner_id, $part_type, $part_id);

            // get prize info
            $prize = validate_prize($prize->part_type, $prize->part_id);
            $part_type = $prize->type;
            $part_id = (int) $prize->id;
            $is_epic = (bool) $prize->epic;

            // make the display name
            $part_name = to_part_name($part_type, $part_id);
            $disp_type = ucfirst($part_type);
            $prize_name = "$part_name $disp_type";
            if ($is_epic === true) {
                $prize_name = "Epic " . $prize_name;
            }

            if ($has_part === true) {
                echo "<span style='color: red;'>Error: $html_winner_name already has the $prize_name.</span><br>";
                $errors++;
                continue;
            }

            // award the prizes
            array_push($prizes_to_award, $prize_id);
            echo "<span style='color: green;'>$html_winner_name does not have the $prize_name.</span><br>";
        }

        // if there were any errors, do not proceed
        if ($errors > 0) {
            echo '<br>';
            throw new Exception(
                "One or more checks returned an error. ".
                "The results can be seen above. ".
                "If the user already has one of the parts, go back and uncheck that part. ".
                "If you need help, ask a member of the PR2 Staff Team."
            );
        } else {
            // if no prizes are being awarded, stop
            if (empty($prizes_to_award)) {
                throw new Exception("You must specify prizes to award.");
            }
        }

        // award prizes
        $prizes_awarded_arr = array();
        $errors = 0;
        foreach ($prizes_to_award as $prize_id) {
            $prize = contest_prize_select($pdo, $prize_id);
            $part_type = $prize->part_type;
            $part_id = $prize->part_id;

            $award = award_part($pdo, $winner_id, $part_type, $part_id);
            if ($award === false) {
                throw new Exception(
                    "CRITICAL ERROR: Could not award $prize_name to $winner_name. ".
                    "Please report this error to an admin."
                );
            }

            // get prize info
            $prize = validate_prize($part_type, $part_id);
            $part_type = $prize->type;
            $part_id = (int) $prize->id;
            $is_epic = (bool) $prize->epic;

            // make the display name
            $part_name = to_part_name($part_type, $part_id);
            $disp_type = ucfirst($part_type);
            $prize_name = "$part_name $disp_type";
            if ($is_epic === true) {
                $prize_name = "Epic " . $prize_name;
            }

            array_push($prizes_awarded_arr, $prize_name);
            echo "<span style='color: green; font-weight: bold;'>"
                ."The $prize_name was successfully awarded to $html_winner_name."
                ."</span><br>";
        }

        if ($errors > 0) {
            echo '<br>';
            throw new Exception(
                "One or more prizes could not be awarded. The results can be seen above. ".
                "If the winner already has one or more of the parts selected, ".
                "go back, deselect them, and attempt to award the prizes again. ".
                "If this error persists, contact a member of the PR2 Staff Team."
            );
        }

        // make the array a string
        $prizes_awarded_str = join(",", $prizes_awarded_arr);

        // record winner
        contest_winner_insert($pdo, $contest->contest_id, $winner_id, $ip, $user_id, $prizes_awarded_str, $comment);
        echo "<span style='color: green;'>Recorded $html_winner_name in the list of winners.</span><br>";

        // compose a congratulatory PM
        $pm_prizes_str = join("\n - ", $prizes_awarded_arr);
        $winner_name = id_to_name($pdo, $winner_id);
        $host_name = id_to_name($pdo, $user_id);
        $contest_name = $contest->contest_name;
        $winner_message = "Dear $winner_name,\n\n"
                         ."I'm pleased to inform you that you won $contest_name! "
                         ."You have been awarded with the following prizes:\n\n"
                         ." - $pm_prizes_str\n\n"
                         ."For more information, please visit pr2hub.com/contests. "
                         ."Thanks for playing PR2, and once again, congratulations!\n\n"
                         ." - $host_name";

        // send the congratulatory PM
        message_insert($pdo, $winner_id, $contest->user_id, $winner_message, $ip);
        echo "<span style='color: green;'>Sent $html_winner_name a congratulatory PM.</span><br>";

        // output the page
        echo "<br>All operations completed! The results can be seen above.";
        echo "<br><br>";
        echo "<a href='view_winners.php?contest_id=$contest_id'>&lt;- View Winners</a><br>";
        echo "<a href='contests.php'>&lt;- All Contests</a>";
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
