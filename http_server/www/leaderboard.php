<?php

require_once("../fns/all_fns.php");
require_once("../fns/output_fns.php");

$start = (int) default_val($_GET['start'], 0);
$count = (int) default_val($_GET['count'], 100);
$group_colors = ["7e7f7f", "047b7b", "1c369f", "870a6f"];
$ip = get_ip();

try {

	// rate limiting
	rate_limit("leaderboard-" . $ip, 5, 2);

	// connect
	$db = new DB();
	
	// header, also check if mod and output the mod links if so
	$is_mod = is_moderator($db, false);
	output_header('Leaderboard', $is_mod);
	
	// navigation
	output_pagination($start, $count);
	echo('<p>---</p>');
	
	// limit amount of entries to be obtained from the db at a time
	if ($is_mod === true) {
		if (($count - $start) > 1000) {
			$count = 1000;
		}
	}
	else if ($is_mod === false) {
		rate_limit('leaderboard-'.$ip, 60, 10, 'Please wait at least one minute before trying to view the leaderboard again.');
		if (($count - $start) > 100) {
			$count = 100;
		}
	}
	else {
		throw new Exception("Could not determine user staff boolean.");
	}

	$users_result = $db->query("SELECT
						users.name,
						users.power,
						(rank_tokens.used_tokens + pr2.rank) AS active_rank,
						pr2.hat_array AS hats
					FROM users, pr2, rank_tokens
					WHERE users.user_id = pr2.user_id
					AND pr2.user_id = rank_tokens.user_id
					AND rank > 49
					ORDER BY active_rank DESC
					LIMIT $start, $count");
	if (!$users_result) {
		throw new Exception("Could not perform database query.");
	}
	if ($users_result->num_rows <= 0) {
		throw new Exception("No users found.");
	}

	echo '
	<center>
	<font face="Gwibble" class="gwibble">-- Leaderboard --</font>
	<br /><br />
	<table>
		<tr>
			<th>Username</th>
			<th>Rank</th>
			<th>Hats</th>
		</tr>
	';

	while ($user = $users_result->fetch_object()) {

		// name
		$name = $user->name;
		$safe_name = htmlspecialchars($name);
		$safe_name = str_replace(" ", "&nbsp;", $safe_name);
		
		// power
		$group = (int) $user->group;
		$group_color = $group_colors[$group];
		
		// rank
		$rank = $user->active_rank;
		
		// hats
		$hats = $user->hats;
		
		// player details link
		$url_name = urlencode($name);
		$info_link = "player_search.php?name=$url_name";
		
		// echo the row
		echo "<tr>";    

		echo "<td><a href='$info_link' style='color: #$group_color; text-decoration: underline;'>$safe_name</a></td>";
		echo "<td>$rank</td>";
		echo "<td>$hats</td>";

		echo "</tr>";

	}

	echo "</table></center>";
	
	echo('<p>---</p>');
	output_pagination($start, $count);

	output_footer();
}

catch (Exception $e) {
	$error = $e->getMessage();
	$safe_error = htmlspecialchars($error);
	echo "Error: $safe_error";
	output_footer();
}

function output_pagination($start, $count) {
	$next_start_num = $start + $count;
	$last_start_num = $start - $count;
	if($last_start_num < 0) {
		$last_start_num = 0;
	}
	echo('<p>');
	if($start > 0) {
		echo("<a href='?start=$last_start_num&count=$count'><- Last</a> |");
	}
	else {
		echo('<- Last |');
	}
	echo(" <a href='?start=$next_start_num&count=$count'>Next -></a></p>");
}

?>
