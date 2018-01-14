#!/usr/bin/php
<?php

require_once(__DIR__ . '/../fns/all_fns.php');

function actually_save_info($port, $name, $rank, $exp_points, $group, $hat_color, $head_color, $body_color, $feet_color, $hat, $head, $body, $feet, $hat_array, $head_array, $body_array, $feet_array, $speed, $acceleration, $jumping, $status, $lux, $rt_used, $ip, $tot_exp_gained, $e_server_id, $hat_color_2, $head_color_2, $body_color_2, $feet_color_2, $epic_hat_array, $epic_head_array, $epic_body_array, $epic_feet_array) {

	$safe_port = mysqli_real_escape_string($port);
	$safe_user_id = mysqli_real_escape_string($user_id);
	
	$safe_name = mysqli_real_escape_string($name);
	$safe_rank = mysqli_real_escape_string($rank);
	$safe_exp_points = mysqli_real_escape_string($exp_points);
	$safe_group = mysqli_real_escape_string($group);
	
	$safe_hat_color = mysqli_real_escape_string($hat_color);
	$safe_head_color = mysqli_real_escape_string($head_color);
	$safe_body_color = mysqli_real_escape_string($body_color);
	$safe_feet_color = mysqli_real_escape_string($feet_color);
	
	$safe_hat = mysqli_real_escape_string($hat);
	$safe_head = mysqli_real_escape_string($head);
	$safe_body = mysqli_real_escape_string($body);
	$safe_feet = mysqli_real_escape_string($feet);
	
	$safe_hat_array = mysqli_real_escape_string($hat_array);
	$safe_head_array = mysqli_real_escape_string($head_array);
	$safe_body_array = mysqli_real_escape_string($body_array);
	$safe_feet_array = mysqli_real_escape_string($feet_array);
	
	$safe_speed = mysqli_real_escape_string($speed);
	$safe_acceleration = mysqli_real_escape_string($acceleration);
	$safe_jumping = mysqli_real_escape_string($jumping);
	
	$safe_status = mysqli_real_escape_string($status);
	
	$safe_lux = mysqli_real_escape_string($lux); // are you kidding me? is this seriously still a thing?
	$safe_rt_used = mysqli_real_escape_string($rt_used);
	$safe_ip = mysqli_real_escape_string($ip);
	$safe_exp_today = mysqli_real_escape_string($exp_today);
	$safe_server_id = mysqli_real_escape_string($server_id);
	
	$safe_hat_color_2 = mysqli_real_escape_string($hat_color_2);
	$safe_head_color_2 = mysqli_real_escape_string($head_color_2);
	$safe_body_color_2 = mysqli_real_escape_string($body_color_2);
	$safe_feet_color_2 = mysqli_real_escape_string($feet_color_2);
	
	if(isset($epic_hat_array)) {
	    $safe_epic_hat_array = mysqli_real_escape_string($epic_hat_array);
		$safe_epic_head_array = mysqli_real_escape_string($epic_head_array);
		$safe_epic_body_array = mysqli_real_escape_string($epic_body_array);
		$safe_epic_feet_array = mysqli_real_escape_string($epic_feet_array);
	}
	
	if( $status == 'offline' ) {
		$safe_server_id = 0;
	}
	
	$db = new DB();

	$db->call( 'pr2_update', array( $safe_user_id, $safe_rank, $safe_exp_points,
		$safe_hat_color, $safe_head_color, $safe_body_color, $safe_feet_color,
		$safe_hat_color_2, $safe_head_color_2, $safe_body_color_2, $safe_feet_color_2,
		$safe_hat, $safe_head, $safe_body,
		$safe_feet, $safe_hat_array, $safe_head_array, $safe_body_array, $safe_feet_array, $safe_speed, $safe_acceleration, $safe_jumping ) );

	if($lux > 0) {
		$db->call( 'lux_increment', array( $safe_user_id, $safe_lux ) );
	}

	if(isset($epic_hat_array)) {
	    	$db->call( 'epic_upgrades_upsert', array( $safe_user_id, $safe_epic_hat_array, $safe_epic_head_array, $safe_epic_body_array, $safe_epic_feet_array ) );
	}
	$db->call( 'user_update_status', array( $safe_user_id, $safe_status, $safe_server_id ) );
	$db->call( 'rank_token_update', array( $safe_user_id, $safe_rt_used ) );
	$db->call( 'exp_today_add', array( 'id-'.$safe_user_id, $safe_exp_today ) );
	$db->call( 'exp_today_add', array( 'ip-'.$safe_ip, $safe_exp_today ) );
	echo('ran all the queries');
	
	return true;

}

?>
