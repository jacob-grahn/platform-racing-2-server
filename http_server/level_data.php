<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;

$ip = get_ip();
$level_id = (int) default_get('level_id', 0);

$ret = new stdClass();
$ret->success = true;

try {
    // rate limiting
    rate_limit('view-level-'.$ip, 5, 2);

    // sanity: getting a level?
    if ($level_id <= 0) {
        throw new Exception('Invalid level ID specified.');
    }

    // make sure level exists
    $level_txt = file_get_contents("https://pr2hub.com/levels/$level_id.txt");
    if ($level_txt === false) {
        throw new Exception("No level exists with the ID: $level_id.");
    }

    // parse level data
    parse_str($level_txt, $ldata);
    
    // assign level data to ret object
    $ret->level_id = (int) $ldata['level_id'];
    $ret->max_time = (int) $ldata['max_time'];
    $ret->min_rank = (int) $ldata['min_level'];
    $ret->live = (bool) (int) $ldata['live'];
    $ret->time = (int) $ldata['time'];
    $ret->user_id = (int) $ldata['user_id'];
    $ret->gravity = (float) $ldata['gravity'];
    $ret->song = $ldata['song'];
    $ret->title = urldecode($ldata['title']);
    $ret->note = urldecode($ldata['note']);

    // handle keys that may not exist
    $ret->gameMode = default_val($ldata['gameMode'], 'race');
    $ret->cowboyChance = (int) default_val($ldata['cowboyChance'], 5);
    $ret->has_pass = (bool) (int) default_val($ldata['has_pass'], 0);
    $ret->version = (int) default_val($ldata['version'], 1);

    // handle items (may contain a hash)
    $ret->items = $ldata['items'];
    if (strlen($ret->items) >= 32 && substr($level_txt, -32) === substr($ret->items, -32)) {
        $ret->items = substr($ret->items, 0, strpos($ret->items, substr($level_txt, -32)));
    }

    // connect
    $pdo = pdo_connect();

    // get level info
    $level = level_select($pdo, $level_id);
    $ret->rating = (float) $level->rating;
    $ret->play_count = (int) $level->play_count;

    // get author
    $author = user_select_name_and_power($pdo, $ret->user_id);
    $ret->user_name = htmlspecialchars($author->name, ENT_QUOTES);
    $ret->user_group = (int) $author->power >= 0 && (int) $author->power <= 3 ? (int) $author->power : 0;
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
