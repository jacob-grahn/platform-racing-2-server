<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/part_awards.php';
require_once QUERIES_DIR . '/purchases.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';
require_once QUERIES_DIR . '/servers.php';

$game_auth_token = find('game_auth_token');
$kong_user_id = find('kong_user_id');
$server_id = find('server_id');
$item_id = -1;
$api_key = $KONG_API_PASS;

try {
    // sanity checks: is data missing?
    if (!isset($game_auth_token)) {
        throw new Exception('Invalid game_auth_token');
    }
    if (!isset($kong_user_id)) {
        throw new Exception('Invalid kong_user_id');
    }
    if (!isset($server_id)) {
        throw new Exception('Invalid server_id');
    }

    // connect
    $pdo = pdo_connect();

    // debugging
    message_insert($pdo, 4505943, 1, "STARTED -- VAULT_FINALIZE", '0');

    // get user info
    $user_id = token_login($pdo);
    $user = user_select($pdo, $user_id);
    if ($user_id !== 4505943) {
        throw new Exception("can't do this :/");
    }

    // sanity check: are they a guest?
    if ($user->power <= 0) {
        throw new Exception('Guests can\'t buy things. How about creating your own account?');
    }

    // debugging
    message_insert($pdo, $user_id, 1, "AUTHENTICATED -- VAULT_FINALIZE", '0');

    // get the list of items they own
    $items = get_owned_items($api_key, $kong_user_id);

    // debugging
    message_insert($pdo, $user_id, 1, "GOT OWNED ITEMS -- VAULT_FINALIZE", '0');

    // loop through and assign any items to their account
    $results = array();
    foreach ($items as $item) {
        $item_id = $item->id;
        $slug = $item->identifier;
        $remaining_uses = $item->remaining_uses;
        if ($remaining_uses >= 1) {
            message_insert($pdo, $user_id, 1, "UNLOCKING ITEM $item_id -- VAULT_FINALIZE", '0');
            $reply = unlock_item($pdo, $user_id, $user->guild, $server_id, $slug, $user->name, $kong_user_id);
            message_insert($pdo, $user_id, 1, "UNLOCKED ITEM $item_id, USING -- VAULT_FINALIZE", '0');
            $results[] = use_item($api_key, $game_auth_token, $kong_user_id, $item_id);
            message_insert($pdo, $user_id, 1, "USED ITEM $item_id -- VAULT_FINALIZE", '0');
        }
    }

    // reply
    $ret = new stdClass();
    $ret->results = $results;
    if (isset($reply)) {
        $ret->message = $reply;
    }
    message_insert($pdo, $user_id, 1, "REPLY: $ret->message -- VAULT_FINALIZE", '0');
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->error = $e->getMessage();
    if ($user_id === 4505943) {
        message_insert($pdo, $user_id, 1, "CAUGHT ERROR: $ret->error -- VAULT_FINALIZE", '0');
    }
} finally {
    die(json_encode($ret));
}
