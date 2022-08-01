<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/api_fns.php';
require_once QUERIES_DIR . '/part_awards.php';
require_once QUERIES_DIR . '/prize_actions.php';
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
            if (strpos($reply, 'Error: ') === 0) {
                throw new Exception(substr($reply, 7));
            }
            $r->message = $reply;
        }
    } else {
        if (!award_part($pdo, $user->user_id, $type, $part_id)) {
            throw new Exception('This player already has this part.');
        }
        $r->message = 'They were nice! The part was awarded.';
    }

    // record the prize in the prize log
    $full_part_name = ($part->epic ? 'Epic ' : '') . to_part_name($part->type, $part->id) . ' ' . ucfirst($part->type);
    $msg = "Prize awarded via API to $user->name from $ip "
        ."{to_user_id: $user->user_id, "
        .'user_online: ' . check_value($user->server_id != 0, true) . ', '
        ."part_type: $part->type, "
        ."part_id: $part->id, "
        .'is_epic: ' . check_value($part->epic, true) . ', '
        ."part_name: $full_part_name}"
    prize_action_insert($pdo, $user->user_id, $msg, 'api', false, $ip);

    $r->success = true;
} catch (Exception $e) {
    $r->error = $e->getMessage();
} finally {
    die(json_encode($r));
}
