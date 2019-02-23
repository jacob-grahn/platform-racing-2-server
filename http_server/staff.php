<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';

output_header('PR2 Staff Team');

$ip = get_ip();

try {
    // rate limiting
    rate_limit('gui-staff-list-'.$ip, 5, 2, 'Please wait at least 10 seconds before refreshing the page again.');

    // connect
    $pdo = pdo_connect();

    // get the data
    $staff_list = users_select_staff($pdo);

    echo '<center>
            <font face="Gwibble" class="gwibble">-- PR2 Staff Team --</font>
            <br>
            <br>
            <table>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Register Date</th>
                    <th>Last Login</th>
                </tr>';

    foreach ($staff_list as $row) {
        // make nice variables for our data
        $safe_name = str_replace(' ', '&nbsp;', htmlspecialchars($row->name, ENT_QUOTES));
        $group_color = $group_colors[(int) $row->power];
        $status = $row->status;
        $register_date = date('j/M/Y', $row->register_time);
        $active_date = date_format(date_create($row->active_date), 'j/M/Y');

        // start the row
        echo "<tr>";

        try {
            // check for a name
            if (is_empty($safe_name) && strlen(trim($safe_name)) === 0) {
                throw new Exception('Invalid name.');
            }

            // display the name with the color and link to the player search page
            $link = 'player_search.php?name=' . urlencode($row->name);
            $style = "color: #$group_color; text-decoration: underline;";
            echo "<td><a href='$link' style='$style'>$safe_name</a></td>";

            // display the status
            echo "<td>$status</td>";

            // display the register date
            if ($register_date === "1/Jan/1970") {
                echo "<td>Age of Heroes</td>";
            } elseif (!is_empty($register_date)) {
                echo "<td>$register_date</td>";
            } else {
                throw new Exception('No register date received.');
            }

            // display the active date
            echo "<td>$active_date</td>";
        } catch (Exception $e) {
            $error = $e->getMessage();
            echo "<td>Error: $error</td>";
        }

        // end the row
        echo "</tr>";
    }

    // end the table
    echo '</table>';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "<br><i>Error: $error</i>";
} finally {
    echo '</center>';
    output_footer();
}
