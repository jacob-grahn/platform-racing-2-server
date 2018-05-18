<?php

require_once HTTP_FNS . '/data_fns.php';
require_once HTTP_FNS . '/output_fns.php';

output_header('Server Status');

try {
    $data = json_decode(file_get_contents("http://pr2hub.com/files/server_status_2.txt"));

    if (array_key_exists('error', $data)) {
        throw new Exception($data->error);
    }

    // make a heading
    echo '<center>'
        .'<font face="Gwibble" class="gwibble">-- Server Status --</font><br>'
        .'<br>';

    // make a table and the headers
    echo "<table>
      <tr>
        <th>Server Name</th>
        <th>Status</th>
        <th>Happy Hour</th>
        <th>Tournament</th>
      </tr>";

    foreach ($data->servers as $server) {
        // echo this when it's yes/no
        $yes = "<strong>Yes</strong>";
        $no = "No";

        // make some variables
        $happy_hour = check_value($server->happy_hour, 1, $yes, $no);
        $tournament = check_value($server->tournament, 1, $yes, $no);
        $server_name = htmlspecialchars($server->server_name);
        $guild_id = (int) $server->guild_id;
        $population = (int) $server->population;
        $status = $server->status;

        // start row
        echo "<tr>";

        // echo the server name (in bold if a guild-only server)
        if ($guild_id === 0) {
            echo "<td>$server_name</td>";
        } else {
            echo "<td><strong>$server_name</strong></td>";
        }

        // if open, echo the population
        if (strtolower($status) == 'open' || $population > 0) {
            echo "<td>$population online</td>";
        } else {
            echo "<td><strong>down</strong></td>";
        }

        // echo status of a happy hour
        echo "<td>$happy_hour</td>";

        // echo status of a tournament
        echo "<td>$tournament</td>";

        // end row
        echo "</tr>";
    }

    // end table
    echo "</table>";
} catch (Exception $e) {
    $safe_message = htmlspecialchars($e->getMessage());
    echo "Error: $safe_message";
} finally {
    echo "</center>";
    output_footer();
    die();
}
