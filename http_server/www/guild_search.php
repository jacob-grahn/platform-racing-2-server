<?php

require_once('../fns/output_fns.php');

error_reporting(0);

if (isset($_GET['name'])) {
	$param = http_build_query(array(
		'name' => $_GET['name'],
		'getMembers' => 'yes',
		'rand' => rand(),
	));
}
elseif (isset($_GET['id'])) {
	$param = http_build_query(array(
		'id' => $_GET['id'],
		'getMembers' => 'yes',
		'rand' => rand(),
	));
}

$server = file_get_contents("https://pr2hub.com/guild_info.php?".$param);

$json = json_decode($server, true);

$guildinfo = $json['guild'];

$memberinfo = $json['members'];

$count = count($memberinfo);

$membercount = ($count - 1);

$guildcreated = date_create_from_format("Y-m-d H:i:s", $guildinfo['creation_date']);

$t = time();

$shortdatetime = "d/M/Y \a\\t g:i A";

$fulldatetime = "g:i:s A \o\\n l, F jS, Y";

$img_validate = substr($guildinfo['emblem'], -2);
if ($img_validate == ".j") {
	$image = $guildinfo['emblem'] . "pg";
}
else {
	$image = $guildinfo['emblem'];
}

$succhtml = "<br><br>";

$emptyhtml = "</label>";

$errhtml = "<br>";

output_header( 'Guild Search' );

echo "<center>";


echo "      <img src='guild_search.png'>
            <br><br>
			<form method='get'>
		    <label for='nametxt'>Guild Name: </label>
		    <input type='text' id='nametxt' name='name' class='primary full textbox' title='Enter the name of the guild.' required='true'>
			<input type='submit' value='Submit' class='button' accesskey='s' tabindex='1'>
			</form>
			<br>
			or
            <br>
            <br>
			<form method='get'>
		    <label for='idtxt'>Guild ID: </label> 
		    <input type='text' id='idtxt' name='id' class='primary full textbox' title='Enter the ID of the guild.' required='true'>
			<input type='submit' value='Submit' class='button' accesskey='s' tabindex='1'>
			</form>
		</span>
<br>";

if(isset($_GET['name']) xor isset($_GET['id'])) {

if(isset($guildinfo['guild_id'])) {

	echo "<br><table><tr><td class=\"image\">";
	
	echo "<img src=\"https://pr2hub.com/emblems/" . $image . "\">";
	
	echo "</td><td rowspan=\"2\" class=\"right\">";

	echo "Name: " . htmlspecialchars($guildinfo['guild_name']);

	echo "<br>ID: " . $guildinfo['guild_id'];	

	echo "<br>Created: " . $guildcreated->format($shortdatetime);

	echo "<br>Members: " . $guildinfo['member_count'];

	echo "<br>GP Earned (Today): " . $guildinfo['gp_today'];

	echo "<br>GP Earned (Total): " . $guildinfo['gp_total'];

	echo "</td></tr></table>";
	
	if (!empty($guildinfo['note'])) {
		echo "<br>Prose: <i>";
		echo htmlspecialchars($guildinfo['note']);
		echo "</i>";
	}

	if($guildinfo['member_count'] >= "1"){
		
		echo "<br><br><table><tr><th><b>Members</b></th><th><b>GP Today</b></th><th><b>GP Total</b></th>";

		foreach (range(0, $membercount) as $number) {

			echo "<tr>";
			
			echo "<td class=\"members\">";
			
			if($memberinfo[$number]['user_id'] == $guildinfo['owner_id']) {
				echo "<img src=\"crown.png\" height=\"12\"> ";
			}

			echo "<u>" . "<a href=\"http://pr2hub.com/player_search.php/?name=" . $memberinfo[$number]['name'] . "\" target=\"_blank" . "\">";
	
			echo "<font color=\"#";

			if($memberinfo[$number]['power'] == "1") {
				echo "047b7b"; 
			}
			elseif($memberinfo[$number]['power'] == "2") {
				echo "1c369f";
			}
			elseif($memberinfo[$number]['power'] == "3") {
				echo "870a6f";
			}

			echo "\">";

			echo "<span title=\"User ID Number: " . $memberinfo[$number]['user_id'] . "\">";

			echo $memberinfo[$number]['name'];

			echo "</span>" . "</font>" . "</a>" . "</u>" . "</td>";
			
			echo "<td class=\"gp\">";
			if(isset($memberinfo[$number]['gp_today'])) {
				echo $memberinfo[$number]['gp_today'];
			}
			else {
				echo "0";
			}
			echo "</td>";
			
			echo "<td>";
			if(isset($memberinfo[$number]['gp_total'])) {
				echo $memberinfo[$number]['gp_total'];
			}
			else {
				echo "0";
			}
			echo "</td>";
			
			echo "</tr>";
		}
		
	echo "</table>";
		
	}
	
	else {
		echo "<br><br>This guild contains no members.";
	}
	
	echo $succhtml;
}

elseif (isset($json['error'])) {
	echo "Error: ";
	print $json['error'];
	echo $errhtml;
}

else {
	echo "An unknown error occurred, try refreshing the page.";
	echo $errhtml;
}
}
elseif (isset($_GET['name']) and isset($_GET['id'])) {
	echo "You cannot enter both a name and an ID.";
	echo $errhtml;
}
else {
	echo $emptyhtml;
}

echo "</center>";

output_footer();

?>
