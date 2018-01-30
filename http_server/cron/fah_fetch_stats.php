<?php

require_once('../fns/all_fns.php');


//--- load the team page for Team Jiggmin -----------------------------------------------------------------
$contents = file_get_contents('http://fah-web.stanford.edu/teamstats/team143016.html');
$contents = preg_replace('/\s+/', ' ', $contents); //replace "_" with " "
$contents = trim($contents);


//--- give up if they're doing a stat update ------------------------------------------------------------
if($contents == false || $contents == '' || strpos($contents, 'Stats update in progress') !== false || strpos($contents, 'The database server is currently serving too many connections.') !== false){
	echo 'updating';
	exit;
}


//--- connect to the db ----------------------------------------------------------------------------------
$db = new DB(fah_connect());


//--- query the list of existing users and their current stats --------------------------------------------------
$result = $db->call( 'stats_select_all', NULL, 'Could not select records. '.$db->get_error() );
$users_history = array();
while( $row = $result->fetch_object() ) {
	$users_history[ strtolower($row->fah_name) ] = $row;
	$row->processed = false;
}


//--- find the tables ----------------------------------------------------------------------------------------
$table_1_index = strpos($contents, '<TABLE>');
$table_2_index = strpos($contents, '<TABLE>', $table_1_index+1);
$table_3_index = strpos($contents, '<TABLE>', $table_2_index+1);
$table_4_index = strpos($contents, '<TABLE>', $table_3_index+1);
$table_5_index = strpos($contents, '<TABLE>', $table_4_index+1);


//--- parse team stats --------------------------------------------------------------------------------
$team_index = strpos($contents, '<TR class=odd>', $table_3_index);

$team_number_index = strpos($contents, '<TD>', $team_index) + 4;
$team_number_end = strpos($contents, '</TD>', $team_number_index);
$team_number = substr($contents, $team_number_index, ($team_number_end - $team_number_index));

$team_name_index = strpos($contents, '<TD>', $team_number_index+1) + 4;
$team_name_end = strpos($contents, '</TD>', $team_name_index);
$team_name = substr($contents, $team_name_index, ($team_name_end - $team_name_index));

$team_score_index = strpos($contents, '<TD>', $team_name_index+1) + 4;
$team_score_end = strpos($contents, '</TD>', $team_score_index);
$team_score = substr($contents, $team_score_index, ($team_score_end - $team_score_index));

$team_wu_index = strpos($contents, '<TD>', $team_score_index+1) + 4;
$team_wu_end = strpos($contents, '</TD>', $team_wu_index);
$team_wu = substr($contents, $team_wu_index, ($team_wu_end - $team_wu_index));

$team_rank_index = strpos($contents, '<BR>', $table_3_index) + 14;
$team_rank_end = strpos($contents, '<BR>', $team_rank_index);
$team_rank = substr($contents, $team_rank_index, ($team_rank_end - $team_rank_index));

$db->call( 'team_update', array( $team_wu, $team_score, $team_rank ) );


//--- parse user stats -------------------------------------------------------------------------------
$users_start_index = strpos($contents, '<BR>Team members:<BR>');
$user_strs = substr($contents, $users_start_index);
$user_array = explode('<TR ', $user_strs);
$user_array = array_splice($user_array, 2);

foreach($user_array as $user_str) {
	$array = explode('<TD>', $user_str);

	$team_rank = $array[2];
	$name = $array[3];
	$points = $array[4];
	$work_units = $array[5];

	$team_rank = substr($team_rank, 0, strlen($team_rank)-5);
	$name = substr($name, 0, strlen($name)-5);
	$points = substr($points, 0, strlen($points)-5);
	$work_units = substr($work_units, 0, strlen($work_units)-5);

	if( strlen( $name ) > 50 ) {
		$name = substr( $name, 0, 50 );
	}

	if( isset( $users_history[ strtolower($name) ] ) ) {
		$history = $users_history[ strtolower($name) ];
		if( ($history->wu != $work_units || $history->points != $points || $history->rank != $team_rank) && !$history->processed ) {
			output( "updating $name: wu: $work_units, points: $points" );
			$db->call( 'stats_save', array( $name, $work_units, $points, $team_rank ) );
		}
		$history->processed = true;
	}
	else {
		output( "creating $name: wu: $work_units, points: $points" );
		$db->call( 'stats_save', array( $name, $work_units, $points, $team_rank ) );
	}
}


//--- handy output function; never leave home without it! --------------------------------------------------
function output( $str ) {
	echo( "* $str \n" );
}

?>
