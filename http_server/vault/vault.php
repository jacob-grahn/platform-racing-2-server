<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once QUERIES_DIR . '/servers.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';

$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limiting
    rate_limit('vault-listing-'.$ip, 3, 1);
    rate_limit('vault-listing-'.$ip, 15, 4);

    // connect
    $pdo = pdo_connect();

    // get login
    $user_id = token_login($pdo);

    // more rate limiting
    rate_limit('vault-listing-'.$user_id, 5, 2);
    rate_limit('vault-listing-'.$user_id, 30, 10);

    // populate items
    $vault = describeVault($pdo, $user_id, 'all');

    // title
    if ($VAULT_TITLE !== 'Vault of Magics') {
        $vault->info->title = new stdClass();
        $vault->info->title->title = $VAULT_TITLE;
        $vault->info->title->flashing = true;
    }

    // reply
    $ret->success = true;
    $ret->info = $vault->info;
    $ret->listings = $vault->listings;
} catch (Exception $e) {
    $ret->state = 'canceled';
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
