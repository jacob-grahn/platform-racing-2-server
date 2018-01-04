#!/usr/bin/php
<?php

require_once('/home/jiggmin/pr2/server/fns/all_fns.php');

$port = $argv[1];
$user_id = $argv[2];

$name = $argv[3];
$rank = $argv[4];
$exp_points = $argv[5];
$group = $argv[6];
	
$hat_color = $argv[7];
$head_color = $argv[8];
$body_color = $argv[9];
$feet_color = $argv[10];
	
$hat = $argv[11];
$head = $argv[12];
$body = $argv[13];
$feet = $argv[14];
	
$hat_array = $argv[15];
$head_array = $argv[16];
$body_array = $argv[17];
$feet_array = $argv[18];

$speed = $argv[19];
$acceleration = $argv[20];
$jumping = $argv[21];

$status = $argv[22];

$lux = $argv[23];
$rt_used = $argv[24];
$ip = $argv[25];
$exp_today = $argv[26];
$server_id = $argv[27];

$hat_color_2 = $argv[28];
$head_color_2 = $argv[29];
$body_color_2 = $argv[30];
$feet_color_2 = $argv[31];

if(isset($argv[32])) {
    $epic_hat_array = $argv[32];
    $epic_head_array = $argv[33];
    $epic_body_array = $argv[34];
    $epic_feet_array = $argv[35];   
}

if( $status == 'offline' ) {
	$server_id = 0;
}

//try{
	$db = new DB();
	
	$db->call( 'pr2_update', array( $user_id, $rank, $exp_points, 
		$hat_color, $head_color, $body_color, $feet_color, 
		$hat_color_2, $head_color_2, $body_color_2, $feet_color_2, 
		$hat, $head, $body,
		$feet, $hat_array, $head_array, $body_array, $feet_array, $speed, $acceleration, $jumping ) );
	
	if($lux > 0) {
		$db->call( 'lux_increment', array( $user_id, $lux ) );
	}
	
	if(isset($epic_hat_array)) {
	    	$db->call( 'epic_upgrades_upsert', array( $user_id, $epic_hat_array, $epic_head_array, $epic_body_array, $epic_feet_array ) );
	}
	$db->call( 'user_update_status', array( $user_id, $status, $server_id ) );
	$db->call( 'rank_token_update', array( $user_id, $rt_used ) );
	$db->call( 'exp_today_add', array( 'id-'.$user_id, $exp_today ) );
	$db->call( 'exp_today_add', array( 'ip-'.$ip, $exp_today ) );
	echo('ran all the queries');
//}

/*catch(Exception $e){
	$message = $e->getMessage();
	echo $message;
	throw $e;
	//call_socket_function($port, "process_message`$message", '75.125.170.90');
	exit;
}*/

?>