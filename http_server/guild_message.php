<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/messages.php';

$message = default_post('message', '');
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // check referrer
    require_trusted_ref('message your guild');

    // rate limit
    $rl_msg = 'Please wait at least 15 seconds before trying to message your guild again.';
    rate_limit('guildMessage-attempt-'.$ip, 15, 1, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // confirm login
    $user_id = token_login($pdo, false);

    // confirm that they are in a guild
    $user = user_select($pdo, $user_id);
    $guild_id = $user->guild;
    if ($guild_id <= 0) {
        throw new Exception('You are not in a guild.');
    }
    if ($user->power <= 0) {
        $e = 'Guests can\'t send guild messages. To access this feature, please create your own account.';
        throw new Exception($e);
    }

    // confirm that there's a message
    if (is_empty($message)) {
        throw new Exception('You must enter a valid message.');
    }

    // rate limit
    rate_limit('guildMessage-'.$ip, 300, 1, 'Only one guild message can be sent every five minutes.');
    rate_limit('guildMessage-'.$user_id, 300, 1, 'Only one guild message can be sent every five minutes.');

    // send message to each member
    $members = guild_select_members($pdo, $guild_id);
    foreach ($members as $member) {
        message_insert($pdo, $member->user_id, $user_id, $message, $ip, 1);
    }

    $ret->success = true;
    $ret->message = 'Your message was sent successfully!';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
