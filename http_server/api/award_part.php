<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/api_fns.php';
require_once QUERIES_DIR . '/part_awards.php';
require_once QUERIES_DIR . '/servers.php';

$to_name = default_post('user_name', '');
$type = default_post('type', '');
$part_id = (int) default_post('part_id', 0);
$pass = default_post('pass', '');
$ip = get_ip();

$r = new stdClass();
$r->success = false;

try {
    // authorized?
    rate_limit('api-award-part-'.$ip, 5, 1);
    validate_api_request($ip, $pass, true);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // data missing?
    if (is_empty($to_name) || empty($type) || empty($part_id)) {
        throw new Exception('Some data is missing.');
    }

    // validate part
    $part = validate_prize($type, $part_id);

    // connect
    $pdo = pdo_connect();

    // award
    $user = user_select_by_name($pdo, $to_name);
    if ($user->server_id != 0) {
        $server = server_select($pdo, $user->server_id);
        $data = new stdClass();
        $data->user_id = (int) $user->user_id;
        $data->part = $part;
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
            $r->message = $reply;
        }
    } else {
        $has_part = !award_part($pdo, $user->user_id, $type, $part_id);
        $r->message = $has_part ? 'This player already has this part.' : 'Great success! The part was awarded.';
    }
    $r->success = true;
} catch (Exception $e) {
    $r->error = $e->getMessage();
} finally {
    die(json_encode($r));
}
