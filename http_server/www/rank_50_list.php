<?php

require_once("../fns/all_fns.php");

require_once("../fns/output_fns.php");

output_header("Rank 50+ List");

try {

	$ip = get_ip();

	$user_id = token_login($db, false);

	rate_limit("rank50-list-" . $ip, 7200, 2, "Please wait 2 hours if you want to get all Rank 50+ users again.");

	rate_limit("rank50-list-" . $user_id, 7200, 2, "Please wait 2 hours if you want to get all Rank 50+ users again.");

	$db = new DB();

	$users_result = $db->query("
		SELECT name, power, rank, hats
		FROM users
		WHERE rank >= 50
		ORDER BY rank DESC
	");

	echo '
	<center>
	<font face="Gwibble" class="gwibble">-- Rank 50+ List --</font>
	<br /><br />
	<table>
		<tr>
			<th>Username</th>
			<th>Rank</th>
			<th>Hats</th>
		</tr>
	';

	while ($row = $users_result->fetch_object()) {

		$safe_name = htmlspecialchars($row->name);

		$safe_name = str_replace(" ", "&nbsp;", $safe_name);

		$group_colors = ["7e7f7f", "047b7b", "1c369f", "870a6f"];

		$group = (int) $row->power;

		$group_color = $group_colors[$group];

		$rank = $row->rank;

		$hats = $row->hats;

		echo "<tr>";   

		$url_name = urlencode($row->name);  

		echo "<td><a href='player_search.php?name=$url_name' style='color: #$group_color; text-decoration: underline;'>$safe_name</a></td>";

		echo "<td>$rank</td>";

		echo "<td>$hats</td>";

		echo "</tr>";

	}

	echo "
	</table>
	</center>
	";
}

catch (Exception $e) {
	$safe_message = htmlspecialchars($e->getMessage());
	echo "Error: $safe_message";
}

output_footer();

?>
