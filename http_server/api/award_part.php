<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/servers.php';

$to_name = @$_GET['user_name'];
$type = @$_GET['type'];
$part_id = @$_GET['part_id'];
$pass = @$_GET['pass'];
$ip = get_ip();

$r = new stdClass();
$r->success = false;

try {
    // authorized?
    rate_limit('api-award-part-'.$ip, 5, 1);
    if ($pass !== $PR2HUB_API_KEY) {
        throw new Exception('Wrong password.');
    }

    // is bls?
    $user_id = token_login($pdo);
    if ($user_id !== 3483035) {
        throw new Exception('Hey... you\'re not bls!');
    }

    // award
    $user = user_select_by_name($pdo, $to_name);
    if ($user->server_id != 0) {
        $server = server_select($pdo, $server_id);
        $data = new stdClass();
        $data->user_id = (int) $user->user_id;
        $data->type = $type;
        $data->part_id = (int) $part_id;
        $data = json_encode($data);
        $reply = talk_to_server(
            $server->address,
            $server->port,
            $server->salt,
            'gain_part`' . $data,
            true,
            false
        );
        if ($reply !== false) {
            echo $reply;
        }
    }
}