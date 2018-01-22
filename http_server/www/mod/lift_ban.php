<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$ban_id = find('ban_id');
$safe_ban_id = addslashes($ban_id);

try {

	//connect
	$db = new DB();


	//make sure you're a moderator
	$mod = check_moderator($db);


	// header
	output_header('Lift Ban', true);


	//get the ban
	$result = $db->query("SELECT *
								 	FROM bans
									WHERE ban_id = '$safe_ban_id'
									LIMIT 0, 1");
	if(!$result){
		throw new Exception('Could not lookup ban');
	}
	if($result->num_rows <= 0) {
		throw new Exception('Ban was not found');
	}

	$row = $result->fetch_object();
	$banned_name = $row->banned_name;
	$lifted = $row->lifted;

	if($lifted == '1') {
		throw new Exception('This ban has already been lifted');
	}

	echo "<p>To lift the ban on $banned_name, please enter a reason and hit submit.</p>";

	?>

	<form action="do_lift_ban.php" method="get">
		<input type="hidden" value="<?php echo $ban_id; ?>" name="ban_id"  />
		<input type="text" value="They bribed me with skittles!" name="reason" size="70" />
		<input type="submit" value="Submit" />
	</form>


	<?php

}

catch(Exception $e){
	echo 'Error: '.$e->getMessage();
}

output_footer();
?>
