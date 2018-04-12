<?php

require_once __DIR__ . '/../../../fns/all_fns.php';
require_once __DIR__ . '/../../../fns/output_fns.php';
require_once __DIR__ . '/../../contests/part_vars.php';
require_once __DIR__ . '/../../../queries/contests/contest_select.php';
require_once __DIR__ . '/../../../queries/contest_prizes/contest_prize_select_id.php';
require_once __DIR__ . '/../../../queries/contest_prizes/contest_prize_insert.php';
require_once __DIR__ . '/../../../queries/staff/actions/admin_action_insert.php';

$ip = get_ip();
$contest_id = (int) find('contest_id', 0);
$action = find('action', 'form');

try {
     // rate limiting
     rate_limit('add-contest-prize-'.$ip, 30, 5);
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
        die();
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
    die();
}

// page
function output_form($contest)
{
    // define prize types
    $prize_types = ['hat', 'head', 'body', 'feet', 'eHat', 'eHead', 'eBody', 'eFeet'];
    $options_html = '';
    foreach ($prize_types as $pt) {
        $options_html .= "<option value='$pt'>$pt</option>";
    }
    
    echo '<form action="add_prize.php" method="post">';
    
    echo 'Add Contest Prize for <b>'.htmlspecialchars($contest->contest_name).'</b><br><br>';
    
    $part_type_sel = "<select name='part_type'>
                        <option value='' selected='selected'>Choose a type...</option>
                        $options_html
                    </select>";
    
    echo "Prize Type: $part_type_sel<br>";
    echo "Prize ID: <input type='text' name='part_id' maxlength='2'>";

    echo '<input type="hidden" name="action" value="add"><br>';
    echo '<input type="hidden" name="contest_id" value="'.(int) $contest->contest_id.'">';

    echo '<input type="submit" value="Add Contest Prize">&nbsp;(no confirmation!)';
    echo '</form>';
    
    echo '<br>';
    echo '---';
    echo '<br>';
    echo '<pre>Find what each part ID is <a href="/admin/part_ids.php" target="blank">here</a>.</pre>';
}

// add contest prize function
function add_contest_prize($pdo, $admin, $contest)
{
    global $hat_names_array, $head_names_array, $body_names_array, $feet_names_array;

    // make some nice variables
    $contest_name = $contest->contest_name;
    $contest_id = (int) $contest->contest_id;
    $part_type = find('part_type');
    $part_id = (int) find('part_id');
    $html_contest_name = htmlspecialchars($contest->contest_name);
    
    // validate the prize and get a nice stdClass back
    $prize = validate_prize($part_type, $part_id);
    
    // check if the prize already exists for this contest
    $prize_exists = contest_prize_select_id($pdo, $contest_id, $part_type, $part_id, true);
    if ($prize_exists != false) {
        throw new Exception("<b>$html_contest_name</b> already awards this prize.");
    }
    
    // add contest prize
    $contest_prize_id = (int) contest_prize_insert($pdo, $contest_id, $part_type, $part_id);
    
    // build variable name
    $prize_type = $prize->type;
    $prize_id = (int) $prize->id;
    $is_epic = (bool) $prize->epic;
    
    // make the display name
    $part_name = ${$prize_type."_names_array"}[$prize_id];
    $disp_type = ucfirst($prize_type);
    $full_part_name = "$part_name $disp_type";
    if ($is_epic == true) {
        $full_part_name = "Epic " . $full_part_name;
    }
    
    // log the action in the admin log
    $admin_ip = get_ip();
    $admin_name = $admin->name;
    $admin_id = $admin->user_id;
    admin_action_insert($pdo, $admin_id, "$admin_name added the $full_part_name to contest $contest_name from $admin_ip. {contest_id: $contest_id, contest_name: $contest_name, prize_id: $contest_prize_id, part_type: $part_type, part_id: $part_id}", 0, $admin_ip);

    // output the page
    echo "Great success! <b>$html_contest_name</b> is now able to award the $full_part_name.";
    echo "<br><br>";
    echo "<a href='add_prize.php?contest_id=$contest_id'>&lt;- Add Another Prize</a><br>";
    echo "<a href='/contests/contests.php'>&lt;- All Contests</a>";
    output_footer();
    die();
}
