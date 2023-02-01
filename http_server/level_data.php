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
    $level_txt = substr($level_txt, 0, -32); // trim hash
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
    $ret->gameMode = isset($ldata['gameMode']) ? $ldata['gameMode'] : 'race';
    $ret->cowboyChance = isset($ldata['cowboyChance']) ? (int) $ldata['cowboyChance'] : 5;
    $ret->has_pass = isset($ldata['has_pass']) ? (bool) (int) $ldata['has_pass'] : false;
    $ret->version = isset($ldata['version']) ? (int) $ldata['version'] : 1;
    $ret->items = isset($ldata['items']) ? $ldata['items'] : '1`2`3`4`5`6`7`8`9';
    $ret->badHats = isset($ldata['badHats']) ? $ldata['badHats'] : '';

    // connect
    $pdo = pdo_connect();

    // get level + author info
    $level = level_select_from_search($pdo, $level_id)[0];
    $ret->rating = (float) $level->rating;
    $ret->play_count = (int) $level->play_count;
    $ret->user_name = $level->name;
    $ret->user_group = get_group_info($level)->str;
} catch (Exception $e) {
    $ret = new stdClass();
    $ret->success = false;
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
