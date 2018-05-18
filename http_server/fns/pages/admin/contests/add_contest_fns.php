<?php


// page
function output_form()
{
    echo '<form action="add_contest.php" method="post">';

    echo "Add Contest<br><br>";

    echo 'Contest Name: <input type="text" name="contest_name" maxlength="100">
        (the name of the contest, max: 100 characters)<br>';
    echo 'Description: <input type="text" name="description" maxlength="255">
        (short description of what the contest involves, max: 255 characters)<br>';
    echo 'Contest URL: <input type="text" name="url" maxlength="255">
        (link to contest homepage, max: 255 characters)<br>';
    echo 'Host User ID: <input type="text" name="host_id" maxlength="10">
        (the user ID of the PR2 player that is hosting this contest, max: 10 numbers)<br>';
    echo 'Awarding: <input type="text" name="awarding" maxlength="255">
        (summary of the prizes the contest is awarding, max: 255 characters)<br>';
    echo 'Max Awards: <input type="text" name="max_awards" maxlength="2">
        (max times a contest owner can award prizes per week, suggested: 1-3, min: 1, max: 50)<br>';
    echo 'Active: <input type="checkbox" name="active" checked="checked">
        (contest visible and prizes able to be awarded)<br>';

    echo '<input type="hidden" name="action" value="add">';

    echo '<br>';
    echo '<input type="submit" value="Add Contest">&nbsp;(no confirmation!)';
    echo '</form>';
}

// add contest function
function add_contest($pdo, $admin)
{
    // make some nice variables
    $contest_name = find('contest_name');
    $description = find('description');
    $contest_url = find('url');
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
    $host_name = id_to_name($pdo, $host_id, false);
    if ($host_name == false) {
        throw new Exception('Could not find a user with that ID.');
    }

    // add contest
    $inserted_contest_id = contest_insert(
        $pdo,
        $contest_name,
        $description,
        $contest_url,
        $host_id,
        $awarding,
        $max_awards,
        $is_active
    );
    if ($inserted_contest_id != false) {
        // log the action in the admin log
        $admin_ip = get_ip();
        $admin_name = $admin->name;
        $admin_id = $admin->user_id;
        $is_active = check_value($is_active, 1);
        admin_action_insert(
            $pdo,
            $admin_id,
            "$admin_name added contest $contest_name from $admin_ip. {
                contest_id: $inserted_contest_id,
                contest_name: $contest_name,
                description: $description,
                url: $contest_url,
                host_id: $host_id,
                awarding: $awarding,
                awards_per_week: $max_awards,
                active: $is_active
            }",
            0,
            $admin_ip
        );
    }

    header("Location: add_prize.php?contest_id=$inserted_contest_id");
    die();
}
