<?php

// page
function output_form($contest)
{
    // define prize types
    $prize_types = ['hat', 'head', 'body', 'feet', 'eHat', 'eHead', 'eBody', 'eFeet', 'exp'];
    $options_html = '';
    foreach ($prize_types as $pt) {
        $options_html .= "<option value='$pt'>$pt</option>";
    }

    echo '<form action="add_prize.php" method="post">';

    $safe_contest_name = htmlspecialchars($contest->contest_name, ENT_QUOTES);
    echo "Add Contest Prize for <b>$safe_contest_name</b><br><br>";

    $part_type_sel = '<select name="part_type">'
        .'<option value="" selected="selected">Choose a type...</option>'
        .$options_html
        .'</select>';

    echo "Prize Type: $part_type_sel<br>";
    echo "Prize ID: <input type='text' name='part_id'>";

    echo '<input type="hidden" name="action" value="add"><br>';
    echo '<input type="hidden" name="contest_id" value="'.(int) $contest->contest_id.'">';

    echo '<input type="submit" value="Add Contest Prize">&nbsp;(no confirmation!)';
    echo '</form>';

    echo '<br>';
    echo '---';
    echo '<br>';
    echo '<pre>Find what each part ID is <a href="/part_ids.php" target="blank">here</a>.</pre>';
}

// add contest prize function
function add_contest_prize($pdo, $admin, $contest)
{
    // make some nice variables
    $contest_id = (int) $contest->contest_id;
    $contest_name = $contest->contest_name;
    $html_contest_name = htmlspecialchars($contest_name, ENT_QUOTES);
    $part_type = default_post('part_type');
    $part_id = (int) default_post('part_id');

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
    if ($prize_type !== 'exp') {
        $part_name = to_part_name($prize_type, $prize_id);
        $disp_type = ucfirst($prize_type);
        $full_part_name = $is_epic === true ? "Epic $part_name $disp_type" : "$part_name $disp_type";
    } else {
        $exp = number_format($prize_id);
        $full_part_name = "$exp EXP Prize";
    }

    // log the action in the admin log
    $ip = get_ip();
    $msg = "$admin->name added the $full_part_name to contest $contest_name from $ip. {".
        "contest_id: $contest_id, ".
        "contest_name: $contest_name, ".
        "prize_id: $contest_prize_id, ".
        "part_type: $part_type, ".
        "part_id: $part_id}";
    admin_action_insert($pdo, $admin->user_id, $msg, 'contest-prize-add', $ip);

    // output the page
    output_header("Add Contest Prize", true, true);
    echo "Great success! <b>$html_contest_name</b> is now able to award the $full_part_name.";
    echo "<br><br>";
    echo "<a href='add_prize.php?contest_id=$contest_id'>&lt;- Add Another Prize</a><br>";
    echo "<a href='/contests/contests.php'>&lt;- All Contests</a>";
    output_footer();
}
