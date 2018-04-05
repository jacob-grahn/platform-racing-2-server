<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../fns/output_fns.php';
require_once __DIR__ . 'part_vars.php';
require_once __DIR__ . '/../../queries/contests/contest_select.php';
require_once __DIR__ . '/../../queries/contest_prizes/contest_prizes_select_by_contest.php';
require_once __DIR__ . '/../../queries/contest_winners/throttle_awards.php';
require_once __DIR__ . '/../../queries/contest_winners/contest_winner_insert.php';
require_once __DIR__ . '/../../queries/messages/message_insert.php';

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
    $user_id = token_login($pdo, true);
    $is_staff = is_staff($pdo, $user_id);
    $is_mod = $is_staff->mod;
    $is_admin = $is_staff->admin;
    
    // get contest info
    $contest = contest_select($pdo, $contest_id, !$is_admin, true);
    
    // sanity check: does the contest exist?
    if (empty($contest) || $contest == false) {
        throw new Exception("Could not find a contest with that ID.");
    }
    
    // sanity check: is this user the contest owner, admin, or mod?
    if ($is_admin == false && $is_mod == false && $user_id != $contest->user_id) {
        $html_contest_name = htmlspecialchars($contest->contest_name);
        throw new Exception("You don't own $html_contest_name.");
    }
    
    // if not an admin, throttle awards
    if ($user_id == $contest->user_id) {
        $recent_awards = throttle_awards($pdo, (int) $contest->contest_id, $user_id);
        $max_awards = (int) $contest->max_awards;
        if ($recent_awards >= $max_awards) {
            throw new Exception("You've reached your maximum amount of awards for this week. If you need to award more, please contact a member of the PR2 Staff Team.");
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
    if (empty($prizes) || $prizes == false) {
        throw new Exception("This contest doesn't currently have any prizes.");
    }
    
    // form
    if ($action === 'form') {
        output_form($contest, $prizes);
        output_footer();
        die();
    } // award
    else if ($action === 'award') {
        // validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        
        // check referrer
        $ref = check_ref();
        if ($ref !== true) {
            $ref = htmlspecialchars($ref);
            throw new Exception("Incorrect referrer. The referrer is: $ref");
        }
        
        // make some nice variables
        $winner_name = default_post('winner_name', '');
        $comment = default_post('comment', '');
        
        // sanity check: are all of the fields filled out?
        if (is_empty($winner_name) || is_empty($comment)) {
            throw new Exception("You must specify a winner and explain why you're awarding the prize(s) to them.");
        }
        
        // sanity check: does this user exist?
        $winner_id = name_to_id($pdo, $winner_name, true);
        if ($winner_id == false) {
            throw new Exception("Could not find a player with that name.");
        }
        $winner_id = (int) $winner_id;
        
        // award the prizes and get the prizes that were awarded
        $prizes_awarded_arr = array();
        foreach($prizes as $prize) {
            $awarded_prize = award_contest_prize($pdo, $contest, $prize, $winner_name);
            
            // make readable prize
            if ($awarded_prize != false) {
                $prize = validate_prize($prize->part_type, $prize->part_id);
                $prize_type = $prize->type;
                $prize_id = (int) $prize->id;
                $is_epic = (bool) $prize->epic;
        
                // make the display name
                $part_name = ${$prize_type."_names_array"}[$prize_id];
                $disp_type = ucfirst($prize_type);
                $prize_name = "$part_name $disp_type";
                if ($is_epic == true) {
                    $prize_name = "Epic " . $prize_name;
                }
                
                // push to the array
                array_push($prizes_awarded_arr, $prize_name);
            }
        }
        
        // sanity check: were any prizes awarded?
        if (!empty($prizes_awarded_arr)) {
            $prizes_awarded_str = join(",", $prizes_awarded_arr);
        } else {
            throw new Exception("You must specify prizes to award.");
        }
        
        // record winner
        $winner_insert = contest_winner_insert($pdo, $contest->contest_id, $winner_id, $ip, $user_id, $prizes_awarded_str, $comment);
        
        // compose a congratulatory PM
        $pm_prizes_str = join("\n - ", $prizes_awarded_arr);
        $winner_name = id_to_name($pdo, $winner_id);
        $host_name = id_to_name($pdo, $user_id);
        $contest_name = $contest->contest_name;
        $winner_message = "Dear $winner_name,\n\n"
                         ."I'm pleased to inform you that you won $contest_name! "
                         ."You have been awarded with the following prizes:\n\n"
                         ."$pm_prizes_str\n\n"
                         ."For more information, visit pr2hub.com/contests. Thanks for playing PR2, and once again, congratulations!\n\n"
                         ."- $host_name";
        
        // send the congratulatory PM
        message_insert($pdo, $winner_id, $user_id, $winner_message, $ip);
        
        // output the page
        echo "<br>Great success! All operations completed. The results can be seen above.";
        echo "<br><br>";
        echo "<a href='view_winners.php?contest_id=$contest_id'>&lt;- View Winners</a><br>";
        echo "<a href='/contests/contests.php'>&lt;- All Contests</a>";
        output_footer();
        die();
    } // unknown handler
    else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    output_footer();
    die();
}

// page
function output_form($contest, $prizes)
{
    $html_contest_name = htmlspecialchars($contest->contest_name);
    $max_awards = (int) $contest->max_awards;
    echo "Award Prizes for <b>$html_contest_name</b><br><br>"
        ."You can award a maximum of $max_awards sets of prizes per week.<br>"
        ."This means that you can click the \"Award Prize(s)\" button $max_awards times per week.<br>"
        ."If you have questions about how this works, please ask a member of the PR2 Staff Team for help."
        ."<br><br>"
        ."<form method='post'>";
    
    echo "PR2 Name: <input type='text' name='winner_name' maxlength='25'> (enter the winner's PR2 name here)<br><br>";
    echo "Select Prizes to Award:<br>";
    foreach ($prizes as $prize) {
        $prize_id = (int) $prize->prize_id;
    
        // build variable name
        $prize = validate_prize($prize->part_type, $prize->part_id);
        $prize_type = $prize->type;
        $prize_id = (int) $prize->id;
        $is_epic = (bool) $prize->epic;
        
        // make the display name
        $part_name = ${$prize_type."_names_array"}[$prize_id];
        $disp_type = ucfirst($prize_type);
        $prize_name = "$part_name $disp_type";
        if ($is_epic == true) {
            $prize_name = "Epic " . $prize_name;
        }
        
        echo "<input type='checkbox' name='prize_$prize_id'> $prize_name";
        echo "<input type='hidden' name='prize_name_$prize_id' value='$prize_name'><br>";
    }
    echo "Comments: <input type='text' name='comment'> (this should be used to explain why you're awarding this user these prizes)<br>";
    echo '<input type="hidden" name="action" value="award"><br>';
    echo '<input type="hidden" name="contest_id" value="'.(int) $contest->contest_id.'">';

    echo '<input type="submit" value="Award Prize(s)">&nbsp;(no confirmation!)';
    echo '</form>';

    echo '<br>';
    echo '---';
    echo '<br>';
    echo '<pre>Check the boxes of the prizes you wish to award.'
        .'<br>When you\'re done, click "Award Contest Prize(s)".'
        .'<br><br>WARNING: Awarding prizes to players who have not won your contest will result in disciplinary action.<br>'
        .'If you have a special case and are unsure of what to do, consult a member of the PR2 Staff Team for help.';
}

// award contest prize function, called inside foreach
function award_contest_prize($pdo, $contest, $prize, $winner_name)
{   
    // make some variables
    $prize_id = (int) $prize->prize_id;
    $part_type = $prize->part_type;
    $part_id = $prize->part_id;
    $award_prize = (bool) $_POST["prize_$prize_id"];
    
    // sanity check: if we're not awarding anything, move on
    if ($award_prize == false) {
        return false;
    }

    // some names of things
    $prize_name = htmlspecialchars(default_post("prize_name_$prize_id", ''));
    $html_winner_name = htmlspecialchars($winner_name);

    // award the prizes
    $result = award_part($pdo, $prize_id, $part_type, $part_id);
    if ($result != false) {
        echo "$prize_name was successfully awarded to $html_winner_name.<br>";
        return $prize_id;
    } else {
        echo "ERROR: $prize_name could not be awarded to $html_winner_name because they already have the part.<br>";
        return false;
    }
}
