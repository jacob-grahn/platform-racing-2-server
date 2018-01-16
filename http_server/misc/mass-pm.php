<?php

require_once('../fns/all_fns.php');

$message = 'Happy Motley Monday! From now on, servers experiencing a blissful Happy Hour will be marked with a double exclamation mark (!!) on the login screen. In addition, guild leaders are marked with a crown on their guild page.

Lazy blog post: <a href="http://jiggmin.com/entries/89279-Strange-Markings " target="_blank"><u><font color="#0000FF">Strange Markings </font></u></a>

- Jiggmin';

$db = new DB();

$users = $db->call('users_select_active');

while($user = $users->fetch_object()) {
	output("name: $user->name, user_id: $user->user_id");
	$full_message =
"Hi $user->name,

$message";
	$db->call('message_insert', array($user->user_id, 1, $full_message, '0'));
}

function output($str) {
	echo "- $str \n";
}

?>
