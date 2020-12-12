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

    // create listing
    $slug_array = [
        'stats-boost',
        'epic-everything',
        'guild-fred',
        'guild-ghost',
        'guild-artifact',
        'happy-hour',
        'rank-rental',
        'djinn-set',
        'king-set',
        'queen-set',
        'server-1-day',
        'server-30-days'
    ];
    $raw_listings = describeVault($pdo, $user_id, $slug_array);

    // weed out only the info we want to return
    $listings = array();
    foreach ($raw_listings as $raw) {
        $listings[] = make_listing($raw);
    }

    // reply
    $ret->success = true;
    $ret->listings = $listings;
    $ret->title = $VAULT_SALE ? $VAULT_SALE_TITLE : 'Vault of Magics';
    $ret->sale = $VAULT_SALE;
} catch (Exception $e) {
    $ret->state = 'canceled';
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
