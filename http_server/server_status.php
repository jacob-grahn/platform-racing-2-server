<?php

require_once HTTP_FNS . '/http_data_fns.php';
require_once HTTP_FNS . '/output_fns.php';

output_header('Server Status');

try {
    $data = json_decode(file_get_contents(WWW_ROOT . "/files/server_status_2.txt"));

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
        $yes = "<b>Yes</b>";
        $no = "No";

        // make some variables
        $happy_hour = check_value($server->happy_hour, 1, $yes, $no);
        $tournament = check_value($server->tournament, 1, $yes, $no);
        $server_name = htmlspecialchars($server->server_name, ENT_QUOTES);
        $guild_id = (int) $server->guild_id;
        $pop = (int) $server->population;
        $status = $server->status;

        // start row
        echo "<tr>";

        // echo the server name (in bold if a guild-only server)
        echo $guild_id === 0 ? "<td>$server_name</td>" : "<td><b>$server_name</b></td>";

        // if open, echo the population
        echo strtolower($status) === 'open' || $pop > 0 ? "<td>$pop online</td>" : "<td><b>down</b></td>";

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
    $error = $e->getMessage();
    echo "Error: $error";
} finally {
    echo "</center>";
    output_footer();
}
