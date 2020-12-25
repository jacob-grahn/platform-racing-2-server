<?php

header('Content-type: text/plain');

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once QUERIES_DIR . '/servers.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';

$slug = default_post('slug', 'none');
$quantity = (int) default_post('quantity', 0);

$ret = new stdClass();
$ret->success = false;
try {
    // rate limiting
    rate_limit('vault-purchase-item-'.$ip, 5, 1);

    // sanity: missing data?
    if (is_empty($slug) || empty($quantity)) {
        throw new Exception('Some data is missing.');
    }

    // connect
    $pdo = pdo_connect();

    // get user
    $user_id = token_login($pdo, false); // is it a valid token?
    $user = user_select($pdo, $user_id);

    // more rate limiting
    rate_limit('vault-purchase-item-'.$user_id, 5, 1);

    // check user
    if ($user->power == 0) { // are they a guest?
        throw new Exception('Guests can\'t buy things. How about creating your own account?');
    } elseif ($user->server_id == 0) { // are they online?
        throw new Exception('You are not online. Please log in to purchase items from the vault.');
    }

    // get/check item
    $item = describeVault($pdo, $user, [$slug])->listings[0]; // is it valid?
    if (!$item->available) { // is it available?
        throw new Exception('You cannot purchase this item at this time. Please try again later.');
    } elseif ($item->price === 0) { // is it free?
        throw new Exception('This item isn\'t for sale.');
    }

    // handle quantities
    if ($slug === 'rank_rental') {
        $rented_tokens = rank_token_rentals_count($pdo, $user->user_id, $user->guild);
        if ($rented_tokens + $quantity > $item->max_quantity) {
            throw new Exception("You may not rent more than $item->max_quantity rank tokens at once.");
        }
    } elseif ($quantity > $item->max_quantity) {
        throw new Exception("You may only purchase $item->max_quantity of this item at once.");
    }

    // handle pricing
    $price = 0;
    if ($slug === 'rank_rental' && $quantity > 1) {
        foreach (range(0, $quantity - 1) as $num) {
            $price += 50 + (20 * ($rented_tokens + $num));
        }
    } else {
        $price = $quantity * $item->price;
    }

    // activate sale pricing
    if ($item->sale->active && ($item->sale->expires === 0 || $item->sale->expires > time())) {
        $price = round($price * (100 - $item->sale->value) / 100);
    }

    // check coins
    if ($user->coins < $price) {
        $coins_link = urlify('https://pr2hub.com/vault/buy_coins.php', 'Click here to purchase more!');
        throw new Exception("You don't have enough coins to purchase this item. $coins_link");
    }

    // place the order
    // make this logic tomorrow

    $ret->success = true;
} catch (Exception $e) {
    $ret->error = 'Error: ' . $e->getMessage();
} finally {
    die(json_encode($ret));
}
