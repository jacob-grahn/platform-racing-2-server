<?php

require_once __DIR__ . '/../../../fns/all_fns.php';
require_once __DIR__ . '/../../../fns/output_fns.php';
require_once __DIR__ . '/../../contests/part_vars.php';
require_once __DIR__ . '/../../../queries/contests/contest_select.php';
require_once __DIR__ . '/../../../queries/contest_prizes/contest_prizes_select_by_contest.php';
require_once __DIR__ . '/../../../queries/contest_prizes/contest_prize_delete.php';

$ip = get_ip();
$contest_id = (int) find('contest_id', 0);
$action = find('action', 'form');

try {
    // rate limiting
    rate_limit('remove-contest-prize-'.$ip, 60, 10);
    rate_limit('remove-contest-prize-'.$ip, 5, 2);
    
    // sanity check: is a valid contest ID specified?
    if (is_empty($contest_id, false)) {
        throw new Exception("Invalid contest ID specified.");
    }

    //connect
    $pdo = pdo_connect();

    //make sure you're an admin
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
        output_form($contest, $prizes);
        output_footer();
        die();
    } // add
    else if ($action === 'remove') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        foreach($prizes as $prize) {
            remove_contest_prize($pdo, $admin, $contest, $prize);
        }
        // output the page
        echo "<br>Great success! All operations completed. The results can be seen above.";
        echo "<br><br>";
        echo "<a href='add_prize.php?contest_id=$contest_id'>&lt;- Add Prize</a><br>";
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
    echo "Remove Prizes from <b>$html_contest_name</b>"
        ."<br><br>"
        ."<form method='post'>";
    
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
        
        echo "<input type='checkbox' name='prize_$prize_id'> Remove $prize_name<br>";
        echo "<input type='hidden' name='prize_name_$prize_id' value='$prize_name'>";
    }
    
    echo '<input type="hidden" name="action" value="remove">';
    echo '<input type="hidden" name="contest_id" value="'.(int) $contest->contest_id.'"><br><br>';
    
    echo '<input type="submit" value="Remove Contest Prize(s)">&nbsp;(no confirmation!)';
    echo '</form>';
    
    echo '<br>';
    echo '---';
    echo '<br>';
    echo '<pre>Check the boxes of the prizes you wish to remove.<br>When you\'re done, click "Remove Contest Prize(s)".';
}

// remove contest prize function, called inside foreach
function remove_contest_prize($pdo, $admin, $contest, $prize)
{
    // make some nice variables
    $contest_name = $contest->contest_name;
    $contest_id = (int) $contest->contest_id;
    $prize_id = (int) $prize->prize_id;
    $remove_prize = (bool) $_POST["prize_$prize_id"];
    
    // move on if not removing this prize
    if ($remove_prize == false) {
        return false;
    }
    
    // some names of things
    $prize_name = htmlspecialchars(default_post("prize_name_$prize_id", ''));
    $html_contest_name = htmlspecialchars($contest_name);
    
    $result = contest_prize_delete($pdo, $prize_id, true);
    if ($result != false) {
        echo "$prize_name was deleted from $html_contest_name.<br>";
    } else {
        echo "$prize_name could not be deleted from $html_contest_name.<br>";
        return false;
    }
    
    // log the action in the admin log
    $admin_ip = get_ip();
    $admin_name = $admin->name;
    $admin_id = $admin->user_id;
    admin_action_insert($pdo, $admin_id, "$admin_name removed the $prize_name from contest $contest_name from $admin_ip. {contest_id: $contest_id, contest_name: $contest_name, prize_id: $prize_id}", 0, $admin_ip);
    
    // go to the next one
    return true;
}
