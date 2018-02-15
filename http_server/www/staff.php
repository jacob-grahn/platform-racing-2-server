<?php

require_once('../fns/all_fns.php');
require_once('../fns/output_fns.php');

$group_colors = ['7e7f7f', '047b7b', '1c369f', '870a6f'];

output_header('PR2 Staff Team');

try {
	$db = new DB();
	$staff_result = $db->query('
		SELECT power, status, name, active_date, register_time, guild
		FROM users
		WHERE power > 1
		ORDER BY active_date DESC
	');

	echo '
	<center>
	<font face="Gwibble" class="gwibble">-- PR2 Staff Team --</font>
	<br>
	<br>
	<table>
	  <tr>
	    <th>Username</th>
	    <th>Status</th>
	    <th>Guild</th>
			<th>Register Date</th>
	    <th>Last Login</th>
	  </tr>';

	while ($row = $staff_result->fetch_object()) {
		// make nice variables for our data
		$safe_name = htmlspecialchars($row->name);
		$group = (int) $row->power;
		$status = $row->status;
		$active_date = $row->active_date;
		$register_date = date('j/M/Y', $row->register_time);
		$guild = $row->guild;
		$group_color = $group_colors[$group];

		// start the row
		echo "<tr>";

		// display the name with the color
		echo "<td><font color='#$group_color'><u>$safe_name</u></font></td>";
		if (empty($safe_name) && strlen(trim($safe_name)) === 0) {
			throw new Exception("Invalid name.");
		}

		// display the status
		echo "<td>$status</td>";

		// display the guild
		echo "<td>$guild</td>";

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
	echo '
	</table>
	</center>
	';
}
catch(Exception $e){
	$reply = new stdClass();
	$reply->error = $e->getMessage();
	echo json_encode( $reply );
}

output_footer();

?>
