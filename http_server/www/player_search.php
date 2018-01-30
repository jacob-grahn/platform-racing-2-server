<?php

require_once('../fns/output_fns.php');

output_header( 'Player Search' );

$name = $_GET['name'];

// get the file and decode it
$decode = json_decode(file_get_contents("https://pr2hub.com/get_player_info_2.php?name=" . $_GET['name']));

// pretty things
echo "<center><img src='/img/player_search.png' /><br><br>";

echo '<form method="get">
Username: <input type="text" name="name">
<input type="submit" value="Search">
</form>';

if(isset($name) && !empty($name) && strlen(trim($name)) !== 0) {

	$user_id = $decode->userId;
	$error = $decode->error;

	if(isset($decode->userId)) {
		
		// define some variables to make it easier for us
		$group = (int) $decode->group;
		$safe_name = htmlspecialchars($decode->name);
		$status = $decode->status;
		$guild_id = (int) $decode->guildId;
		$safe_guild_name = htmlspecialchars($decode->guildName);
		$rank = (int) $decode->rank;
		$hats = (int) $decode->hats;
		$join_date = $decode->registerDate;
		$login_date = $decode->loginDate;
		
		
		// make guild id 0 say none
		if($guild_id === 0) {
			$safe_guild_name = "none";
		}

		// make join date say age of heroes if 1/Jan/1970
		if($join_date == "1/Jan/1970") {
			$join_date = "Age of Heroes";
		}
	
		switch($group) {
			case 0:
				$group_name = "Guest";
				$group_color = "#7E7F7F";
				break;
			case 1:
				$group_name = "Member";
				$group_color = "#047B7B";
				break;
			case 2:
				$group_name = "Moderator";
				$group_color = "#1C369F";
				break;
			case 3:
				$group_name = "Admin";
				$group_color = "#870A6F";
				break;
			default:
				$group_name = "Unknown";
				$group_color = "#000000";
				break;
		}
	
		// player name with group color
		echo "<br>-- <u><font color='$group_color'><strong>$safe_name</strong></font></u> --<br><br>";

		// Playing on ?/offline
		echo "<i>$status</i><br><br>";

		// group name
		echo "Group: $group_name<br>";

		// guild name
		echo "Guild: $safe_guild_name<br>";

		// rank
		echo "Rank: $rank<br>";

		// hats
		echo "Hats: $hats<br>";

		// join date
		echo "Joined: $join_date<br>";
		
		// last login date
		echo "Active: $login_date<br>";

	}

	else if(isset($error)) {
		echo "<br /><i>Error: $error</i><br />";
	}

}

echo "</center>";

output_footer();

?>
