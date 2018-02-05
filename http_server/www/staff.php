<?php

require_once('../fns/output_fns.php');

output_header( 'Staff Members' );

echo "<center>";

echo '<font face="Gwibble" class="gwibble">-- Staff Members --</font>';

echo "<br /><br />";

echo "<style>
table, th, td {
    background-color: #f2f2f2;
    padding: 5px;
    text-align: center;
    border: 1px solid #000000;
    border-collapse: collapse;
}
</style>";

echo "<table>
  <tr>
    <th>Username</th>
    <th>Status</th>
    <th>Guild</th>
    <th>Rank</th>
    <th>Hats</th>
    <th>Joined</th>
    <th>Last Login</th>
  </tr>";

function get_staff($playerlist) {

$plist = explode(",", $playerlist);

foreach ($plist as $player) {

$decode = json_decode(file_get_contents("https://pr2hub.com/get_player_info_2.php?name=" . $player));

echo "<tr><td>";

echo '<u><font color="';

if ($decode->group == "0") {
    echo '#7e7f7f">';
}

elseif ($decode->group == "1") {
    echo '#047b7b">';
}

elseif ($decode->group == "2") {
    echo '#1c369f">';
}

elseif ($decode->group == "3") {
    echo '#870a6f">';
}

echo htmlspecialchars($decode->name) . "</u></font></td>";

echo "<td>" . $decode->status . "</td>";

if ($decode->guildId == "0") {
    echo "<td>" . "none" . "</td>";
}
else {
    echo "<td>" . htmlspecialchars($decode->guildName) . "</td>";
}

echo "<td>" . $decode->rank . "</td>";

echo "<td>" . strval($decode->hats) . "</td>";

if ($decode->registerDate == "1/Jan/1970") {
    echo "<td>Age of Heroes</td>";
}
else {
    echo "<td>" . $decode->registerDate . "</td>";
}

echo "<td>" . $decode->loginDate . "</td>";

echo "</tr>";
}

echo "</table>";

}

get_staff("bls1999,Eternal,Jiggmin,1python64,a7x3,Captain of the Inks,cod4fan,Dangevin,Dev54,inuyasharox,Nemo Nation,Stxtics,TRUC");

echo "</center>";

output_footer();

?>
