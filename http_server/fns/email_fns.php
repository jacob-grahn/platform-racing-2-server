<?php

require_once('Mail.php');

function send_email( $from, $to, $subject, $body ) {
	global $EMAIL_HOST, $EMAIL_USER, $EMAIL_PASS;

	$recipients = $to;

	$headers = array();
	$headers['From']    = $from;
	$headers['To']      = $to;
	$headers['Subject'] = $subject;

	// Define SMTP Parameters
	$params['host'] = $EMAIL_HOST;
	$params['port'] = '465';
	$params['auth'] = 'PLAIN';
	$params['username'] = $EMAIL_USER;
	$params['password'] = $EMAIL_PASS;

	// Create the mail object using the Mail::factory method
	$mail_object =& Mail::factory('smtp', $params);

	// Send the message
	$mail_object->send($recipients, $headers, $body);
}

?>
