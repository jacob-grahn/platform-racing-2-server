<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/pages/vault/kong_order_placed.php';
require_once HTTP_FNS . '/pages/vault/kong_order_request.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/part_awards.php';
require_once QUERIES_DIR . '/purchases.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';
require_once QUERIES_DIR . '/servers.php';

try {
    // parse the incoming message
    $encrypted_request = $_POST['signed_request'];
    $request = parse_signed_request($encrypted_request, $KONG_API_PASS);

    // connect
    $pdo = pdo_connect();

    // handle event
    $event = $request->event;
    if ($event === 'item_order_request') {
        $ret = order_request_handler($pdo, $request);
    } elseif ($event === 'item_order_placed') {
        $ret = order_placed_handler($pdo, $request);
    }
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->error = $e->getMessage();
    error_log('Caught error: ' . $ret->error);
} finally {
    die(json_encode($ret));
}
