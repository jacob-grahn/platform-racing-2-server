<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;

$ip = get_ip();
$level_id = (int) default_get('level_id', 0);

try {
    // rate limiting
    rate_limit('view-level-'.$ip, 5, 2, $rl_msg);

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
    $ret = new stdClass();
    $ret->success = true;

    // these will always be present (and always integers):
    $ret->level_id = (int) $ldata['level_id']; // done
    $ret->max_time = (int) $ldata['max_time']; // done
    $ret->min_rank = (int) $ldata['min_level']; // done
    $ret->live = (bool) (int) $ldata['live']; // done
    $ret->time = (int) $ldata['time']; // done
    $ret->user_id = (int) $ldata['user_id']; // done

    // these either have potential to be or are definitely something other than integers:
    $ret->gravity = $ldata['gravity'];
    $ret->items = $ldata['items'];
    $ret->song = $ldata['song'];
    $ret->title = htmlspecialchars(urldecode($ldata['title']), ENT_QUOTES);
    $ret->note = htmlspecialchars(urldecode($ldata['note']), ENT_QUOTES);

    // handle keys that may not exist
    $ret->gameMode = default_val($ldata['gameMode'], 'race');
    $ret->cowboyChance = (int) default_val($ldata['cowboyChance'], 5);
    $ret->has_pass = (bool) (int) default_val($ldata['has_pass'], 0);
    $ret->version = (int) default_val($ldata['version'], 1);

    // connect
    $pdo = pdo_connect();

    // get level info
    $level = level_select($pdo, $level_id);
    $ret->rating = (float) $level->rating;
    $ret->play_count = (int) $level->plays;

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
