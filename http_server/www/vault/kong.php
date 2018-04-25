<?php

header("Content-type: text/plain");

require_once __DIR__ . '/kong_order_placed.php';
require_once __DIR__ . '/kong_order_request.php';
require_once __DIR__ . '/vault_fns.php';
require_once __DIR__ . '/../../fns/all_fns.php';

require_once __DIR__ . '/../../queries/guilds/guild_select.php';
require_once __DIR__ . '/../../queries/messages/message_insert.php';
require_once __DIR__ . '/../../queries/purchases/purchase_insert.php';
require_once __DIR__ . '/../../queries/rank_token_rentals/rank_token_rentals_count.php';
require_once __DIR__ . '/../../queries/rank_token_rentals/rank_token_rental_insert.php';
require_once __DIR__ . '/../../queries/servers/server_select.php';
require_once __DIR__ . '/../../queries/servers/server_select_by_guild_id.php';
require_once __DIR__ . '/../../queries/servers/server_insert.php';
require_once __DIR__ . '/../../queries/servers/server_update_expire_date.php';
require_once __DIR__ . '/../../queries/servers/servers_select.php';
require_once __DIR__ . '/../../queries/servers/servers_select_highest_port.php';
require_once __DIR__ . '/../../queries/users/user_select_expanded.php';


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
