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

get_staff("bls1999,Eternal,Jiggmin,1python64,a7x3,Captain of the Inks,cod4fan,Dangevin,Dev54,inuyasharox,Nemo Nation,Stxtics,TRUC");

echo "</center>";

output_footer();

?>

