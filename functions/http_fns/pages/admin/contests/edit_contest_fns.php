<?php

// page
function output_form($contest)
{
    $contest_id = (int) $contest->contest_id;
    $name = htmlspecialchars($contest->contest_name, ENT_QUOTES);
    $desc = htmlspecialchars($contest->description, ENT_QUOTES);
    $url = htmlspecialchars($contest->url, ENT_QUOTES);
    $host_id = (int) $contest->user_id;
    $awarding = htmlspecialchars($contest->awarding, ENT_QUOTES);
    $max_awards = (int) $contest->max_awards;
    $active_checked = check_value($contest->active, 1, 'checked="checked"', '');

    echo '<form method="post">';

    echo 'Edit Contest<br><br>';

    echo 'Contest Name: '
        ."<input type='text' name='contest_name' maxlength='100' value='$name'>"
        .' (the name of the contest, max: 100 characters)<br>';
    echo 'Description: '
        ."<input type='text' name='description' maxlength='255' value='$desc'> "
        .'(short description of what the contest involves, max: 255 characters)<br>';
    echo "Contest URL: <input type='text' name='url' maxlength='255' value='$url'> "
        .'(link to contest homepage, max: 255 characters)<br>';
    echo "Host User ID: <input type='text' name='host_id' maxlength='10' value='$host_id'> "
        .'(the user ID of the PR2 player that is hosting this contest, max: 10 numbers)<br>';
    echo 'Awarding: '
        ."<input type='text' name='awarding' maxlength='255' value='$awarding'> "
        .'(summary of the prizes the contest is awarding, max: 255 characters)<br>';
    echo "Max Awards: <input type='text' name='max_awards' maxlength='2' value='$max_awards'> "
        .'(max times a contest owner can award prizes per week, suggested: 1-3, min: 1, max: 50)<br>';
    echo "Active: <input type='checkbox' name='active' $active_checked> "
        .'(contest visible and prizes able to be awarded)<br>';

    echo '<input type="hidden" name="action" value="edit">';
    echo "<input type='hidden' name='contest_id' value='$contest_id'>";

    echo '<br>';
    echo '<input type="submit" value="Edit Contest">&nbsp;(no confirmation!)';
    echo '</form>';
}

// edit contest function
function edit_contest($pdo, $contest, $admin)
{
    // make some variables
    $contest_id = (int) $contest->contest_id;
    $c_name = default_post('contest_name');
    $desc = default_post('description');
    $c_url = default_post('url');
    $host_id = (int) default_post('host_id');
    $awarding = default_post('awarding');
    $max_awards = (int) default_post('max_awards');
    $is_active = (int) (bool) default_post('active');

    if ($c_name == $contest->contest_name &&
        $desc == $contest->description &&
        $c_url == $contest->url &&
        $host_id == $contest->user_id &&
        $awarding == $contest->awarding &&
        $max_awards == $contest->max_awards &&
        $is_active == $contest->active
    ) {
        throw new Exception('No edits to be made.');
    }

    // host sanity checks (if the host is being changed)
    if ($host_id !== (int) $contest->user_id) {
        // does the new host exist?
        $host_name = id_to_name($pdo, $host_id, false);
        if ($host_name === false) {
            throw new Exception('Could not find a user with that ID.');
        }

        // is the new host a guest?
        if ((int) user_select_power($pdo, $host_id, true) === 0) {
            throw new Exception('Guests can\'t host contests.');
        }
    }

    // specify the range of max awards that can be set
    if ($max_awards > 50) {
        throw new Exception('Too many awards per week.');
    } elseif ($max_awards <= 0) {
        throw new Exception('Too few awards per week.');
    }

    // edit contest
    contest_update($pdo, $contest_id, $c_name, $desc, $c_url, $host_id, $awarding, $max_awards, $is_active);

    // log the action in the admin log
    $ip = get_ip();
    $is_active = check_value($is_active, 1);
    $msg = "$admin->name edited contest $c_name (ID #$contest_id) from $ip. {".
        "contest_id: $contest_id, ".
        "contest_name: $c_name, ".
        "description: $desc, ".
        "url: $c_url, ".
        "host_id: $host_id, ".
        "awarding: $awarding, ".
        "awards_per_week: $max_awards, ".
        "active: $is_active}";
    admin_action_insert($pdo, $admin->user_id, $msg, 0, $ip);

    header("Location: /contests/contests.php");
    die();
}
