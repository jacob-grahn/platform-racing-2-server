<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/api_fns.php';
require_once QUERIES_DIR . '/messages.php';

$ip = get_ip();
$pass = default_post('pass', '');

$user_id = (int) default_post('pr2_id', 0);
$ver_code = default_post('ver_code', '');

$d_id = (int) default_post('d_id', 0);
$d_name = default_post('d_name', '');
$d_discrim = default_post('d_discrim', '');

$r = new stdClass();
$r->success = false;

try {
    // authorized?
    rate_limit('api-send-discord-verification-'.$ip, 5, 1);
    validate_api_request($ip, $pass);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // data missing?
    if (is_empty($user_id) || is_empty($ver_code) || empty($d_id) || is_empty($d_name) || empty($d_discrim)) {
        throw new Exception('Some data is missing.');
    }

    // connect
    $pdo = pdo_connect();

    // verify
    $user = user_select($pdo, $user_id);

    // discord account details
    $discord_full = "[b]$d_name#$d_discrim [/b]\n[i][small](ID: $d_id)[/small][/i]";

    // PM body
    $message = "Howdy [user]$user->name[/user],\n\n"
        ."The following Discord user has requested to link your PR2 account to their Discord account:\n\n"
        ."$discord_full\n\n"
        ."If you initiated this action, please [discordverif=$ver_code]click here[/discordverif] "
        .'to complete the process of linking your Discord and PR2 accounts. [i]This link will expire in one hour.[/i] '
        .'If you didn\'t request this message, you can simply ignore it.'
        ."\n\nAll the best,\nFred";
    $message = message_parse_tags($pdo, $message);
    message_insert($pdo, $user_id, 4291976, $message, '0');

    $r->success = true;
} catch (Exception $e) {
    $r->error = $e->getMessage();
} finally {
    die(json_encode($r));
}
