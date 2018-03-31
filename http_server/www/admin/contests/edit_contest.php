<?php

require_once __DIR__ . '/../../../fns/all_fns.php';
require_once __DIR__ . '/../../../fns/output_fns.php';
require_once __DIR__ . '/../../../queries/contests/contest_select.php';
require_once __DIR__ . '/../../../queries/contests/contest_update.php';
require_once __DIR__ . '/../../../queries/staff/actions/admin_action_insert.php';

$action = find('action', 'form');
$contest_id = $_GET['contest_id'];

try {
    // rate limiting
    rate_limit('edit-contest-'.$ip, 60, 10);
    rate_limit('edit-contest-'.$ip, 5, 2);

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
    output_header('Edit Contest', true, true);
    
    // select contest
    $contest = contest_select($pdo, $contest_id);
    if (empty($contest)) {
        throw new Exception("Could not find a contest with that ID.");
    }

    // form
    if ($action === 'form') {
        output_form($pdo, $contest);
        output_footer();
        die();
    } // add
    else if ($action === 'edit') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        edit_contest($pdo, $contest, $admin);
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
function output_form($pdo, $contest)
{
    $active_checked = check_value($contest->active, 1, 'checked="checked"', '');

    echo '<form action="edit_contest.php" method="post">';

    echo "Edit Contest<br><br>";

    echo "Contest Name: <input type='text' name='contest_name' maxlength='100' value='".htmlspecialchars($contest->contest_name)."'> (the name of the contest, max: 100 characters)<br>";
    echo "Description: <input type='text' name='description' maxlength='255' value='".htmlspecialchars($contest->description)."'> (short description of what the contest involves, max: 255 characters)<br>";
    echo "Contest URL: <input type='text' name='url' maxlength='255' value='".htmlspecialchars(urlencode($contest->url))."'> (link to contest homepage, max: 255 characters)<br>";
    echo "Host User ID: <input type='text' name='host_id' maxlength='10' value='".(int) $contest->user_id."'> (the user ID of the PR2 player that is hosting this contest, max: 10 numbers)<br>";
    echo "Awarding: <input type='text' name='awarding' maxlength='255' value='".htmlspecialchars($contest->awarding)."'> (summary of the prizes the contest is awarding, max: 255 characters)<br>";
    echo "Max Awards (per week): <input type='text' name='max_awards' maxlength='2' value='".(int) $contest->max_prizes."'> (max times a contest owner can award prizes per week, suggested: 1-3, min: 1, max: 50)<br>";
    echo "Active: <input type='checkbox' name='active' $active_checked> (contest visible and prizes able to be awarded)<br>";

    echo '<input type="hidden" name="action" value="edit">';
    echo "<input type='hidden' name='contest_id' value='".(int) $contest->contest_id."'>";

    echo '<br>';
    echo '<input type="submit" value="Edit Contest">&nbsp;(no confirmation!)';
    echo '</form>';

}

// edit contest function
function edit_contest($pdo, $contest, $admin)
{
    // make some variables
    $contest_name = find('contest_name');
    $description = find('description');
    $contest_url = find('url');
    $host_id = (int) find('host_id');
    $awarding = find('awarding');
    $max_awards = (int) find('max_awards');
    $is_active = (int) (bool) find('active');
    
    if ($contest_name == $contest->contest_name &&
        $description == $contest->description &&
        $contest_url == $contest->url &&
        $host_id == $contest->user_id &&
        $awarding == $contest->awarding &&
        $max_awards == $contest->max_prizes &&
        $active == $contest->active) {
        throw new Exception('No edits to be made.');
    }
    
    // make sure the host exists
    if ($host_id != $contest->user_id) {
        $host_name = id_to_name($pdo, $host_id, false);
        if ($host_name == false) {
            throw new Exception('Could not find a user with that ID.');
        }
    }

    // specify the range of max awards that can be set
    if ($max_awards > 50) {
        throw new Exception('Too many awards per week.');
    } else if ($max_awards <= 0) {
        throw new Exception('Too few awards per week.');
    }
    
    // edit contest
    contest_update($pdo, $contest_id, $contest_name, $description, $contest_url, $host_id, $awarding, $max_awards, $is_active);
    
    // log the action in the admin log
    $admin_ip = get_ip();
    $admin_name = $admin->name;
    $admin_id = $admin->user_id;
    $is_active = check_value($is_active, 1);
    admin_action_insert($pdo, $admin_id, "$admin_name edited contest $contest_name (ID #$contest_id) from $admin_ip. {contest_id: $contest_id, contest_name: $contest_name, description: $description, url: $contest_url, host_id: $host_id, awarding: $awarding, awards_per_week: $max_awards, active: $is_active}", 0, $admin_ip);

    header("Location: /contests/contests.php");
    die();
}
