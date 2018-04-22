<?php

require_once __DIR__ . '/../fns/to_hash.php';
require_once __DIR__ . '/../queries/users/user_insert.php';
require_once __DIR__ . '/../queries/pr2/pr2_insert.php';
require_once __DIR__ . '/../queries/messages/message_send_welcome.php';

function do_register_user($pdo, $name, $password, $ip, $time, $email)
{
    // user insert
    $pass_hash = to_hash($password);
    unset($password); // don't keep pass in memory
    user_insert($pdo, $name, $pass_hash, $ip, $time, $email);
    unset($pass_hash); // don't keep hash in memory

    // pr2 insert
    $user_id = name_to_id($pdo, $name);
    pr2_insert($pdo, $user_id);

    // welcome them
    message_send_welcome($pdo, $name, $user_id);
}
