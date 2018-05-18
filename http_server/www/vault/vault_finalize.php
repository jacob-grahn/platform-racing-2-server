<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once QUERIES_DIR . '/users/user_select.php';

$game_auth_token = find('game_auth_token');
$kong_user_id = find('kong_user_id');
$server_id = find('server_id');
$item_id = -1;
$api_key = $KONG_API_PASS;

try {
    //--- sanity check
    if (!isset($game_auth_token)) {
        throw new Exception('Invalid game_auth_token');
    }
    if (!isset($kong_user_id)) {
        throw new Exception('Invalid kong_user_id');
    }
    if (!isset($server_id)) {
        throw new Exception('Invalid server_id');
    }


    //--- connect
    $pdo = pdo_connect();


    //--- gather infos
    $user_id = token_login($pdo);
    $user = user_select($pdo, $user_id);

    if ($user->power <= 0) {
        throw new Exception('Guests can not buy things...');
    }


    //--- get the list of items they own
    $items = get_owned_items($api_key, $kong_user_id);


    //--- loop through and assign any items to their account
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

    //--- reply
    $r = new stdClass();
    $r->results = $results;
    if (isset($reply)) {
        $r->message = $reply;
    }
    echo json_encode($r);
} catch (Exception $e) {
    $r = new stdClass();
    $r->error = $e->getMessage();
    echo json_encode($r);
}
