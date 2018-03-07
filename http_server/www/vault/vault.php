<?php

header("Content-type: text/plain");

require_once '../../fns/all_fns.php';
require_once 'vault_descriptions.php';

$ip = get_ip();

try {
    // rate limiting
    rate_limit('vault-listing-'.$ip, 3, 1);
    rate_limit('vault-listing-'.$ip, 15, 4);
    
    // connect
    $db = new DB();
    
    // get login
    $user_id = token_login($db);
    
    // more rate limiting
    rate_limit('vault-listing-'.$user_id, 5, 2);
    rate_limit('vault-listing-'.$user_id, 30, 10);
    
    // create listing
    $raw_listings = describeVault($db, $user_id, array('stats-boost', 'epic-everything', 'guild-fred', 'guild-ghost', 'guild-artifact', 'happy-hour', 'rank-rental', 'djinn-set', 'king-set', 'queen-set', 'server-1-day', 'server-30-days'));
    
    // weed out only the info we want to return
    $listings = array();
    foreach ($raw_listings as $raw) {
        $listings[] = makeListing($raw);
    }

    // reply
    $r = new stdClass();
    $r->success = true;
    $r->listings = $listings;
    $r->title = 'Vault of Magics';
    $r->sale = false;
    echo json_encode($r);
} catch (Exception $e) {
    $r = new stdClass();
    $r->state = 'canceled';
    $r->error = $e->getMessage();
    echo json_encode($r);
}


function makeListing($desc)
{
    $obj = new stdClass();
    $obj->slug = $desc->slug;
    $obj->title = $desc->title;
    $obj->imgUrl = $desc->imgUrl;
    $obj->price = $desc->price;
    $obj->description = $desc->description;
    $obj->longDescription = $desc->faq;
    $obj->available = $desc->available;
    if (isset($desc->discount)) {
        $obj->discount = $desc->discount;
    }
    return $obj;
}
