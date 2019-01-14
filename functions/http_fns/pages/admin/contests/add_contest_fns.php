<?php


// page
function output_form()
{
    echo '<form action="add_contest.php" method="post">';

    echo "Add Contest<br><br>";

    echo 'Contest Name: <input type="text" name="contest_name" maxlength="100"> '
        .'(the name of the contest, max: 100 characters)<br>';
    echo 'Description: <input type="text" name="description" maxlength="255"> '
        .'(short description of what the contest involves, max: 255 characters)<br>';
    echo 'Contest URL: <input type="text" name="url" maxlength="255"> '
        .'(link to contest homepage, max: 255 characters)<br>';
    echo 'Host User ID: <input type="text" name="host_id" maxlength="10"> '
        .'(the user ID of the PR2 player that is hosting this contest, max: 10 numbers)<br>';
    echo 'Awarding: <input type="text" name="awarding" maxlength="255"> '
        .'(summary of the prizes the contest is awarding, max: 255 characters)<br>';
    echo 'Max Awards: <input type="text" name="max_awards" maxlength="2"> '
        .'(max times a contest owner can award prizes per week, suggested: 1-3, min: 1, max: 50)<br>';
    echo 'Active: <input type="checkbox" name="active" checked="checked"> '
        .'(contest visible and prizes able to be awarded)<br>';

    echo '<input type="hidden" name="action" value="add">';

    echo '<br>';
    echo '<input type="submit" value="Add Contest">&nbsp;(no confirmation!)';
    echo '</form>';
}


// add contest function
function add_contest($pdo, $admin)
{
    // make some nice variables
    $c_name = find('contest_name');
    $desc = find('description');
    $c_url = find('url');
    $host_id = (int) find('host_id');
    $awarding = find('awarding');
    $max_awards = (int) find('max_awards');
    $is_active = (int) (bool) find('active');

    if ($max_awards > 50) {
        throw new Exception('Too many awards per week.');
    } elseif ($max_awards <= 0) {
        throw new Exception('Too few awards per week.');
    }

    // make sure the host exists
    if (id_to_name($pdo, $host_id, true) === false) {
        throw new Exception('Could not find a user with that ID.');
    }

    // add contest
    $inserted_contest_id = contest_insert($pdo, $c_name, $desc, $c_url, $host_id, $awarding, $max_awards, $is_active);
    if ($inserted_contest_id != false) {
        // log the action in the admin log
        $ip = get_ip();
        $is_active = check_value($is_active, 1);
        $msg = "$admin->name added contest $c_name from $ip. {" .
            "contest_id: $inserted_contest_id, ".
            "contest_name: $c_name, ".
            "description: $desc, ".
            "url: $c_url, ".
            "host_id: $host_id, ".
            "awarding: $awarding, ".
            "awards_per_week: $max_awards, ".
            "active: $is_active}";
        admin_action_insert($pdo, $admin->user_id, $msg, 0, $ip);
    }

    header("Location: add_prize.php?contest_id=$inserted_contest_id");
    die();
}
