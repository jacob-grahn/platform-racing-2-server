<?php

function is_selected($prize_type, $option_value)
{
    $prize_type = strtolower($prize_type);
    $option_value = strtolower($option_value);

    if ($option_value == $prize_type) {
        return "selected='selected'";
    } else {
        return '';
    }
}

function prize_check($type, $id, $err_prefix)
{
    $type_array = array("hat", "head", "body", "feet", "eHat", "eHead", "eBody", "eFeet");

    // safety first
    $safe_type = htmlspecialchars($type);
    $safe_id = htmlspecialchars($id);

    // check for a valid prize type
    if (!in_array($type, $type_array)) {
        throw new Exception("$err_prefix ($safe_type is an invalid prize type).");
    }

    // check for a valid hat id
    if ($type == "hat" || $type == "eHat") {
        if ($id < 2 || $id > 14) {
            throw new Exception("$err_prefix (invalid hat ID ($safe_id) specified).");
        } else {
            return true;
        }
    }

    // check for a valid head id
    if ($type == "head" || $type == "eHead") {
        if ($id < 1 || $id > 39) {
            throw new Exception("$err_prefix (invalid head ID ($safe_id) specified).");
        } else {
            return true;
        }
    }

    // check for a valid body id
    if ($type == "body" || $type == "eBody") {
        if ($id < 1 || $id > 39 || $id === 33) {
            throw new Exception("$err_prefix (invalid body ID ($safe_id) specified).");
        } else {
            return true;
        }
    }

    // check for a valid feet id
    if ($type == "feet" || $type == "eFeet") {
        if ($id < 1 || $id > 39 || ($id >= 31 && $id <= 33)) {
            throw new Exception("$err_prefix (invalid feet ID ($safe_id) specified).");
        } else {
            return true;
        }
    }

    // this should never happen
    throw new Exception("$err_prefix (an unknown error occurred).");
}

function output_form($pdo, $message, $campaign_id)
{
    $campaign = campaign_select_by_id($pdo, $campaign_id);

    output_header('Set Campaign', true, true);

    // if there's a message, display it to the user
    if ($message != '') {
        echo "<p><b>$message</b></p>";
    }

    echo '<form name="input" action="set_campaign.php" method="post">';

    echo "Set Custom Campaign <br>---<br>";

    foreach ($campaign as $row) {
        // get level/prize information
        $num = $row->level_num;
        $level_id = $row->level_id;
        $prize_type = $row->prize_type;
        $prize_id = $row->prize_id;

        // define prize types
        $prize_types = ['hat', 'head', 'body', 'feet', 'eHat', 'eHead', 'eBody', 'eFeet'];

        // check which type the current prize is, then select it in the dropdown
        $option_html = '';
        foreach ($prize_types as $pt) {
            $selected = is_selected($prize_type, $pt);
            $option_html .= "<option value='$pt' $selected>$pt</option>";
        }

        $prize_html = "<select name='prize_type_$num'>
						<option value=''>Choose a type...</option>
						$option_html
					</select>&nbsp;<input type='text' size='' name='prize_id_$num' value='$prize_id'>";

        echo "Level $num: <input type='text' size='' name='level_id_$num' value='$level_id'> | Prize: $prize_html<br>";
    }

    echo '<input type="hidden" name="action" value="update">';

    echo '<br/>';
    echo '<input type="submit" value="Submit">&nbsp;(no confirmation!)';
    echo '</form>';

    echo '<br>';
    echo '---';
    echo '<br>';
    echo '<pre>To set the custom campaign, gather the levels you want to set.<br>'
        .'Then, find the level IDs of those levels.<br>'
        .'Finally, use the level IDs to update the campaign in the form above.<br>'
        .'<br>'
        .'You can find a list of prizes and their corresponding IDs '
        .'<a href="part_ids.php" target="_blank">here</a>.</pre>';

    output_footer();
}

function update($pdo, $admin, $campaign_id)
{

    foreach (range(1, 9) as $id) {
        // get individual level/prize details
        $level_id = (int) find("level_id_$id");
        $prize_type = find("prize_type_$id");
        $prize_id = (int) find("prize_id_$id");

        try {
            level_select($pdo, $level_id); // will throw error if level does not exist
            prize_check($prize_type, $prize_id, "The prize for level $id is invalid");
            campaign_update($pdo, $campaign_id, $id, $level_id, $prize_type, $prize_id);
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            output_form($pdo, $message, $campaign_id);
        }
    }

    // push the changes to the servers
    generate_level_list($pdo, 'campaign');

    //admin log
    $admin_name = $admin->name;
    $admin_id = $admin->user_id;
    $ip = get_ip();
    admin_action_insert($pdo, $admin_id, "$admin_name set a new custom campaign from $ip.", 0, $ip);

    // did the script get here? great! redirect back to the script with a success message
    $message = "Great success! The new campaign has been set and will take effect shortly.";
    header("Location: set_campaign.php?message=" . urlencode($message));
    die();
}
