<?php

// code

require_once('../fns/output_fns.php');

$staff_array = array(
				"Jiggmin",
				"1python64",
				"bls1999",
				"Eternal",
				"inuyasharox",
				"Captain of the Inks",
				"Dangevin",
				"cod4fan",
				"Dev52",
				"TRUC",
				"Nemo Nation",
				"Stxtics",
				"a7x3"
			);

// start the page

output_header('PR2 Staff Team');

echo "<center>";

echo '<font face="Gwibble" class="gwibble">-- PR2 Staff Team --</font>
		<br /><br />';

try {

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
	  
	foreach ($staff_array as $player) {
	
		// get data
		$decode = json_decode(file_get_contents("https://pr2hub.com/get_player_info_2.php?name=" . urlencode($player)));

		// make sure the data came back without an error
		if (array_key_exists("error", $decode) {
			throw new Exception($decode->error);
		}

		// make nice variables for our data
		$safe_name = htmlspecialchars($decode->name);
		$group = (int) $decode->group;
		$status = $decode->status;
		$rank = (int) $decode->rank;
		$hats = (int) $decode->hats;
		$register_date = $decode->registerDate;
		$active_date = $decode->loginDate;

		// make nice group variables
		switch($group) {
			case 0:
				$group_name = "Guest";
				$group_color = "7e7f7f";
				break;
			case 1:
				$group_name = "Member";
				$group_color = "047b7b";
				break;
			case 2:
				$group_name = "Moderator";
				$group_color = "1c369f";
				break;
			case 3:
				$group_name = "Admin";
				$group_color = "870a6f";
				break;
			default:
				throw new Exception("Error fetching group data.");
		}
		
		// start the row
		echo "<tr>";
		
		// display the name with the color
		echo "<td><font color='#$group_color'><u>$safe_name</u></font></td>";
		if (empty($safe_name) && strlen(trim($safe_name)) === 0) {
			throw new Exception("Invalid name.");
		}
	
		// display the status
		echo "<td>$status</td>";
		if (empty($status)) {
			throw new Exception("Invalid status.");
		}
	
		// display the rank
		echo "<td>$rank</td>";
		if (empty($rank) && $rank !== 0) {
			throw new Exception("Rank not received.");
		}
	
		// display the hats
		echo "<td>$hats</td>";
		if (empty($hats) && $hats !== 0) {
			throw new Exception("Number of hats not received.");
		}
	
		// display the register date
		if ($register_date == "1/Jan/1970") {
			echo "<td>Age of Heroes</td>";
		}
		elseif (!empty($register_date)) {
			echo "<td>$register_date</td>";
		}
		else {
			throw new Exception("No register date received.");
		}
	
		// display the active date
		echo "<td>$active_date</td>";

		// end the row
		echo "</tr>";
	
	}
	
	// end the table
	echo "</table>";
	
}

catch (Exception $e) {
	echo "Error: " . $e->getMessage();
}

echo "</center>";
output_footer();

?>
