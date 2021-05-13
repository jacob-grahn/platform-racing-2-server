<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/ignored.php';
require_once QUERIES_DIR . '/friends.php';

$BETA = (bool) (int) default_get('beta', 0);

$target_id = (int) default_get('user_id', 0);
$target_name = default_get('name', '');
$friend = 0;
$ignored = 0;
$ip = get_ip();

$ret = new stdClass();
$ret->success = false;

try {
    // rate limit
    rate_limit('get-player-info-'.$ip, 3, 2);

    // connect
    $pdo = pdo_connect();

    // check their login
    try {
        $user_id = (int) token_login($pdo, true, false, 'n');
        rate_limit('get-player-info-2-'.$user_id, 3, 2);
    } catch (Exception $e) {
        $friend = 0;
        $ignored = 0;
    }

    // determine mode
    if (!is_empty($target_name) && $target_id === 0) {
        $target_id = name_to_id($pdo, $target_name, true);
        if ($target_id === false) {
            throw new Exception('Could not find a user with that name.');
        }
    }

    // get dem infos
    $target = user_select_expanded($pdo, $target_id, true);
    if ($target === false) {
        throw new Exception('Could not find a user with that ID.');
    }

    // get guild name
    if ((int) $target->guild !== 0) {
        try {
            $guild = guild_select($pdo, $target->guild);
            $guild_name = $guild->guild_name;
        } catch (Exception $e) {
            $guild_name = '';
        }
    } else {
        $guild_name = '';
    }

    // get other data
    $target->used_tokens = is_empty($target->used_tokens) ? 0 : (int) $target->used_tokens;
    $login_date = $BETA ? (int) $target->time : date('j/M/Y', $target->time);
    $register_date = $BETA ? (int) $target->register_time : date('j/M/Y', $target->register_time);
    $active_rank = $target->rank + $target->used_tokens;
    $hats = count(explode(',', $target->hat_array)) - 1;
    if (isset($user_id)) {
        $friend = (int) (bool) friend_select($pdo, $user_id, $target_id, true);
        $ignored = (int) (bool) ignored_select($pdo, $user_id, $target_id, true);
    }

    // april fools! :)
    if (date('M d') === 'Apr 01') {
        switch ($target->power) {
            case 1:
                $target->power = 3;
                break;
            case 2:
            case 3:
                $target->power = 1;
                break;
            default:
                break;
        }
    }

    // reply
    $ret->success = true;
    $ret->userId = $target->user_id;
    $ret->name = $target->name;
    $ret->status = $target->status;
    $ret->group = $target->power;
    $ret->trial_mod = (bool) (int) $target->trial_mod;
    $ret->guildId = $target->guild;
    $ret->guildName = $guild_name;
    $ret->rank = $active_rank;
    $ret->hats = $hats;
    $ret->registerDate = $register_date;
    $ret->loginDate = $login_date;
    $ret->hat = $target->hat;
    $ret->head = $target->head;
    $ret->body = $target->body;
    $ret->feet = $target->feet;
    $ret->hatColor = $target->hat_color;
    $ret->headColor = $target->head_color;
    $ret->bodyColor = $target->body_color;
    $ret->feetColor = $target->feet_color;

    // epic upgrades
    if (!isset($target->epic_hats)) {
        $ret->hatColor2 = -1;
        $ret->headColor2 = -1;
        $ret->bodyColor2 = -1;
        $ret->feetColor2 = -1;
    } else {
        $ret->hatColor2 = test_epic($target->hat_color_2, $target->epic_hats, $target->hat);
        $ret->headColor2 = test_epic($target->head_color_2, $target->epic_heads, $target->head);
        $ret->bodyColor2 = test_epic($target->body_color_2, $target->epic_bodies, $target->body);
        $ret->feetColor2 = test_epic($target->feet_color_2, $target->epic_feet, $target->feet);
    }

    $ret->exp_points = $target->exp_points;
    $ret->exp_to_rank = exp_required_for_ranking($target->rank + 1);
    $ret->friend = $friend;
    $ret->ignored = $ignored;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
