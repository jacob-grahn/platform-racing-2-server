<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$archive = find('archive', 0);
$start = find('start', 0);
$count = find('count', 25);

$safe_archive = addslashes($archive);
$safe_start = addslashes($start);
$safe_count = addslashes($count);

$next_form_id = 1;


try {

	//connect
	$db = new DB();

	//make sure you're a moderator
	$mod = check_moderator($db, false);

	// output header
	output_header('Reported Messages', true);

	//navigation
	output_pagination($start, $count);
	echo('<p>---</p>');


	//get the messages
	$result = $db->query("SELECT messages_reported.*, u1.name as from_name, u2.name as to_name
									FROM messages_reported, users u1, users u2
									WHERE to_user_id = u2.user_id
									AND from_user_id = u1.user_id
									ORDER by reported_time desc
									LIMIT $safe_start, $safe_count");
	if(!$result){
		throw new Exception('Could not retireve messages');
	}


	//output the messages
	while($row = $result->fetch_object()) {
		$formatted_time = date('M j, Y g:i A', $row->sent_time);
		$from_name = str_replace(' ', '&nbsp;', htmlspecialchars($row->from_name));
		$to_name = str_replace(' ', '&nbsp;', htmlspecialchars($row->to_name));
		$from_user_id = $row->from_user_id;
		$to_user_id = $row->to_user_id;
		$from_ip = $row->from_ip;
		$reporter_ip = $row->reporter_ip;
		$archived = $row->archived;
		$message_id = $row->message_id;
		$html_safe_message = htmlspecialchars( filter_swears( $row->message ) );
		$html_safe_message = str_replace("\r", '<br>', $html_safe_message);

		if($archived) {
			$class = 'archived';
		}
		else {
			$class = 'not-archived';
		}

		echo("	<br/>
				<div class='$class'>
				<p><a href='player_info.php?user_id=$from_user_id&force_ip=$from_ip'>$from_name</a> sent this message to <a href='player_info.php?user_id=$to_user_id&force_ip=$reporter_ip'>$to_name</a> on $formatted_time<p>
				<p>$html_safe_message</p> ");

		if(!$archived) {
			$form_id = 'f'.$next_form_id;
			$button_id = 'b'.$next_form_id;
			$div_id = 'd'.$next_form_id++;
			echo("<div id='$div_id'>
					<input id='$button_id' type='submit' value='Archive' />
					<br/>
					-- or --
					<br/>
					<form id='$form_id' action='../ban_user.php' method='get'>
						<input type='hidden' value='yes' name='using_mod_site'  />
						<input type='hidden' value='$from_name' name='banned_name' />
						<input type='hidden' value='$from_ip' name='force_ip' />
						<input type='submit' name='reason' value='Flaming' />
						<input type='submit' name='reason' value='Vulgar Language' />
						<input type='submit' name='reason' value='Password Scamming' />
						<input type='submit' name='reason' value='Spam' />
						<select name='duration'>
							<option value='60'>1 Minute</option>
							<option value='3600' selected='selected'>1 Hour</option>
							<option value='86400'>1 Day</option>
							<option value='648000'>1 Week</option>
							<option value='2592000'>1 Month</option>
							<option value='31104000'>1 Year</option>
							<option value='99999999'>Forever</option>
						</select>
					</form>
				</div>
				<script>
					$('#$form_id').ajaxForm(function() {
						$('#$div_id').remove();
						$.get('archive_message.php', {message_id: $message_id});
					});
					$('#$button_id').click(function() {
						$('#$div_id').remove();
						$.get('archive_message.php', {message_id: $message_id});
					});
				</script>
			");
		}

		echo("
				</div>
				<p>&nbsp;</p>");
	}


	echo('<p>---</p>');
	output_pagination($start, $count);

	output_footer();
}

catch(Exception $e){
	echo 'Error: '.$e->getMessage();
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
