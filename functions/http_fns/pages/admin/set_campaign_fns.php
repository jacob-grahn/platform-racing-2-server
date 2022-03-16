<?php


function is_selected($prize_type, $option_value)
{
    $prize_type = strtolower($prize_type);
    $option_value = strtolower($option_value);
    return $option_value === $prize_type ? 'selected="selected"' : '';
}


function prize_check($prize_type, $prize_id, $level_num)
{
    try {
        validate_prize($prize_type, $prize_id, false);
    } catch (Exception $e) {
        throw new Exception("The prize is invalid for level #$level_num. There was an " . lcfirst($e->getMessage()));
    }
    return true;
}


function output_form($pdo, $message, $campaign_id)
{
    $campaign = campaign_select_by_id($pdo, $campaign_id);

    output_header('Set Campaign', true, true);

    // if there's a message, display it to the user
    if ($message !== '') {
        echo "<p><b>$message</b></p>";
    }

    echo '<form name="input" action="set_campaign.php" method="post">';
    echo "Set Custom Campaign <br>---<br>";

    foreach ($campaign as $row) {
        // get level/prize information
        $num = (int) $row->level_num;
        $level_id = (int) $row->level_id;
        $prize_type = $row->prize_type;
        $prize_id = (int) $row->prize_id;

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
}


function update($pdo, $admin, $campaign_id)
{
    $levels = [];

    try {
        foreach (range(1, 9) as $num) {
            // get individual level/prize details
            $level_id = (int) find("level_id_$num");
            $prize_type = find("prize_type_$num");
            $prize_id = (int) find("prize_id_$num");

            if (level_select($pdo, $level_id)->restricted == 1) { // will throw error if level does not exist
                throw new Exception("Level #$num is restricted (ID #$level_id).");
            }
            prize_check($prize_type, $prize_id, $num);

            $level = new stdClass();
            $level->level_id = $level_id;
            $level->prize_type = $prize_type;
            $level->prize_id = $prize_id;
            $levels[] = $level;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        return output_form($pdo, $error, $campaign_id);
    }

    // update the campaign and push the changes to the servers
    campaign_update($pdo, $campaign_id, $levels);
    generate_level_list($pdo, 'campaign');

    // admin log
    $admin_name = $admin->name;
    $admin_id = $admin->user_id;
    $ip = get_ip();
    admin_action_insert($pdo, $admin_id, "$admin_name set a new custom campaign from $ip.", 'campaign-set', $ip);

    // did the script get here? great! redirect back to the script with a success message
    $message = "Great success! The new campaign has been set and will take effect shortly.";
    header("Location: set_campaign.php?message=" . urlencode($message));
}
