<?php

if (!headers_sent()) {
	header("Content-Type: text/html; charset=UTF-8");
}

else {
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>";
}

require_once('../fns/output_fns.php');

output_header( 'Server Status' );

echo "<center>";

echo '<font face="Gwibble" class="gwibble">-- Server Status --</font>';

echo "<br /><br />";

echo "<style>
table, th, td {
    background-color: #f2f2f2;
    padding: 5px;
    border: 1px solid #000000;
    border-collapse: collapse;
}
</style>";

echo "<table>
  <tr>
    <th>Server Name</th>
    <th>Population</th>
    <th>Status</th>
    <th>Happy Hour</th>
    <th>Tournament</th>
  </tr>";

try {

$decode = json_decode(file_get_contents("http://pr2hub.com/files/server_status_2.txt"), true);

foreach ($decode["servers"] as $jarray) {

    $hh = "No";
    $tournament = "No";
    
    if (strval($jarray["happy_hour"]) == "1") {
        $hh = "Yes";
    }
    
    if (strval($jarray["tournament"]) == "1") {
        $tournament = "Yes";
    }

    echo "<tr>";

    echo "<td>" . htmlspecialchars($jarray["server_name"]) . "</td>";

    echo "<td>" . htmlspecialchars($jarray["population"]) . "</td>";

    echo "<td>" . htmlspecialchars($jarray["status"]) . "</td>";

    echo "<td>" . htmlspecialchars($hh) . "</td>";

    echo "<td>" . htmlspecialchars($tournament) . "</td>";

    echo "</tr>";
}

echo "</table>";

echo "</center>";

}

catch (Exception $emsg) {
    echo "<center><span>" . "There was an error getting the PR2 servers: " . htmlspecialchars($emsg->getMessage) . "</span></center>";
}

output_footer();

?>
