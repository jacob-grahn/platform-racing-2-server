<?php

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/output_fns.php';
require_once QUERIES_DIR . '/messages_reported/messages_reported_select.php';

$start = (int) default_get('start', 0);
$count = (int) default_get('count', 25);

$next_form_id = 1;
$mod_ip = get_ip();

try {
    // rate limiting
    rate_limit('mod-reported-messages-'.$mod_ip, 5, 3);

    //connect
    $pdo = pdo_connect();

    //make sure you're a moderator
    $mod = check_moderator($pdo, false);
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header("Error");
    echo "Error: $error";
    output_footer();
    die();
}

try {
    // output header
    output_header('Reported Messages', true);

    // navigation
    output_pagination($start, $count);
    echo('<p>---</p>');

    //get the messages
    $messages = messages_reported_select($pdo, $start, $count);

    //output the messages
    foreach ($messages as $row) {
        $formatted_time = date('M j, Y g:i A', $row->sent_time);
        $from_name = str_replace(' ', '&nbsp;', htmlspecialchars($row->from_name));
        $to_name = str_replace(' ', '&nbsp;', htmlspecialchars($row->to_name));
        $from_user_id = $row->from_user_id;
        $to_user_id = $row->to_user_id;
        $from_ip = $row->from_ip;
        $reporter_ip = $row->reporter_ip;
        $archived = $row->archived;
        $message_id = $row->message_id;
        $html_safe_message = htmlspecialchars(filter_swears($row->message));
        $html_safe_message = str_replace("\r", '<br>', $html_safe_message);

        if ($archived) {
            $class = 'archived';
        } else {
            $class = 'not-archived';
        }

        echo("	<br/>
				<div class='$class'>
				<p>
                    <a href='player_info.php?user_id=$from_user_id&force_ip=$from_ip'>$from_name</a>
                    sent this message to
                    <a href='player_info.php?user_id=$to_user_id&force_ip=$reporter_ip'>$to_name</a>
                    on $formatted_time
                <p>
				<p>$html_safe_message</p> ");

        if (!$archived) {
            $form_id = 'f'.$next_form_id;
            $button_id = 'b'.$next_form_id;
            $div_id = 'd'.$next_form_id++;
            echo("<div id='$div_id'>
					<input id='$button_id' type='submit' value='Archive' />
					<br/>
					-- or --
					<br/>
					<form id='$form_id' action='../ban_user.php' method='post'>
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
							<option value='604800'>1 Week</option>
							<option value='2592000'>1 Month</option>
							<option value='31536000'>1 Year</option>
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
} catch (Exception $e) {
    $error = $e->getMessage();
    output_header('Reported Messages', true);
    echo "Error: $error";
} finally {
    output_footer();
    die();
}
