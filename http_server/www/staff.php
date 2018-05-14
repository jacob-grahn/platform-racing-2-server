<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/users/users_select_staff.php';

$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

output_header('PR2 Staff Team');

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
        $safe_name = htmlspecialchars($row->name);
        $safe_name = str_replace(' ', '&nbsp;', $safe_name);
        $group = (int) $row->power;
        $group_color = $group_colors[$group];
        $status = $row->status;
        $register_date = date('j/M/Y', $row->register_time);
        $active_date = $row->active_date;
        $active_date = date_create($active_date);
        $active_date = date_format($active_date, 'j/M/Y');

        // start the row
        echo "<tr>";

        // display the name with the color and link to the player search page
        $url_name = urlencode($row->name);
        echo "<td><a href='player_search.php?name=$url_name' style='color: #$group_color; ".
            "text-decoration: underline;'>$safe_name</a></td>";

        if (empty($safe_name) && strlen(trim($safe_name)) === 0) {
            throw new Exception("Invalid name.");
        }

        // display the status
        echo "<td>$status</td>";

        // display the register date
        if ($register_date == "1/Jan/1970") {
            echo "<td>Age of Heroes</td>";
        } elseif (!empty($register_date)) {
            echo "<td>$register_date</td>";
        } else {
            throw new Exception("No register date received.");
        }

        // display the active date
        echo "<td>$active_date</td>";

        // end the row
        echo "</tr>";
    }

    // end the table
    echo '</table>';
} catch (Exception $e) {
    echo "<br><i>Error: " . htmlspecialchars($e->getMessage()) . '</i>';
} finally {
    echo '</center>';
    output_footer();
    die();
}
