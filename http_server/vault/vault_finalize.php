<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once HTTP_FNS . '/pages/vault/kong_order_placed.php';
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

    // get user info
    $user_id = token_login($pdo);
    $user = user_select($pdo, $user_id);
    

    // sanity check: are they a guest?
    if ($user->power <= 0) {
        throw new Exception('Guests can\'t buy things. How about creating your own account?');
    }

    // get the list of items they own
    $items = get_owned_items($api_key, $kong_user_id);

    // loop through and assign any items to their account
    $results = array();
    foreach ($items as $item) {
        $item_id = $item->id;
        $slug = $item->identifier;
        $remaining_uses = $item->remaining_uses;
        if ($remaining_uses >= 1) {
            $reply = unlock_item($pdo, $user_id, $user->guild, $server_id, $slug, $user->name, $kong_user_id);
            $results[] = use_item($api_key, $game_auth_token, $kong_user_id, $item_id);
        }
    }

    // reply
    $ret = new stdClass();
    $ret->results = $results;
    if (isset($reply)) {
        $ret->message = $reply;
    }
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
