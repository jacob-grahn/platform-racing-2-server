<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pages/vault/kong_order_placed.php';
require_once HTTP_FNS . '/pages/vault/kong_order_request.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';

require_once QUERIES_DIR . '/guilds/guild_select.php';
require_once QUERIES_DIR . '/messages/message_insert.php';
require_once QUERIES_DIR . '/purchases/purchase_insert.php';
require_once QUERIES_DIR . '/rank_token_rentals/rank_token_rentals_count.php';
require_once QUERIES_DIR . '/rank_token_rentals/rank_token_rental_insert.php';
require_once QUERIES_DIR . '/servers/server_select.php';
require_once QUERIES_DIR . '/servers/server_select_by_guild_id.php';
require_once QUERIES_DIR . '/servers/server_insert.php';
require_once QUERIES_DIR . '/servers/server_update_expire_date.php';
require_once QUERIES_DIR . '/servers/servers_select.php';
require_once QUERIES_DIR . '/servers/servers_select_highest_port.php';
require_once QUERIES_DIR . '/users/user_select_expanded.php';


try {
    //--- parse the incoming message
    $encrypted_request = $_POST['signed_request'];
    $request = parse_signed_request($encrypted_request, $KONG_API_PASS);

    //--- connect
    $pdo = pdo_connect();

    //---
    $event = $request->event;
    if ($event == 'item_order_request') {
        $reply = order_request_handler($pdo, $request);
    }
    if ($event == 'item_order_placed') {
        $reply = order_placed_handler($pdo, $request);
    }

    //---
    echo json_encode($reply);
} catch (Exception $e) {
    $r = new stdClass();
    $r->error = $e->getMessage();
    error_log('caught error: ' . $r->error);
    echo json_encode($r);
}
