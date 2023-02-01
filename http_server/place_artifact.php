<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/artifact_location.php';
require_once QUERIES_DIR . '/mod_actions.php';

$level_id = (int) default_post('level_id', 0);
$x = (int) default_post('x', 0);
$y = (int) default_post('y', 0);
$rot = (int) default_post('rot', 0);
$set_time = (int) default_post('set_time', 0);
$ip = get_ip();

$override_sched = (bool) (int) default_post('override_sched', 0);

$willbeup = 'will be updated within the next minute';

$ret = new stdClass();
$ret->success = false;

try {
    // sanity check: is data missing?
    if (is_empty($x, false) || is_empty($y, false) || is_empty($level_id, false) || is_null(default_post('rot'))) {
        throw new Exception('Some data is missing.');
    }

    // check referrer
    require_trusted_ref('', true);

    // rate limiting
    $rl_msg = 'Please wait at least 30 seconds before trying to set a new artifact location again.';
    rate_limit('place-artifact-attempt-'.$ip, 30, 1, $rl_msg);

    // connect
    $pdo = pdo_connect();

    // check their login
    $mod = check_moderator($pdo);
    if ($mod->trial_mod) {
        $msg = 'You lack the power to access this resource. Please ask a moderator to place the artifact.';
        throw new Exception($msg);
    }

    // more rate limiting
    $rl_msg = 'The artifact can only be placed a maximum of 5 times per hour. Try again later.';
    rate_limit('place-artifact-'.$ip, 3600, 5, $rl_msg);
    rate_limit('place-artifact-'.$user_id, 3600, 5, $rl_msg);

    // sanity check: does the level exist? is it a hat attack level?
    $level = level_select($pdo, $level_id);
    if ($level->type !== 'r' && $level->type !== 'o') {
        throw new Exception('The artifact can only be set on levels with a race or objective game mode.');
    }

    // get current and scheduled artifact
    $locations = artifact_locations_select($pdo);

    // warn of overwriting a scheduled artifact placement
    if (isset($locations[1]) && !$override_sched && $set_time > time() && $locations[0]->level_id != $level_id) {
        $ret->status = 'scheduled';
        die(json_encode($ret));
    }

    // update (don't replace) if the new level id matches either the current or scheduled id
    foreach ($locations as $loc) {
        if ($level_id == $loc->level_id) {
            try {
                $set_time = $loc->artifact_id == 1 ? $loc->set_time : $set_time;
                artifact_location_update_by_id($pdo, $loc->artifact_id, $level_id, $x, $y, $rot, $set_time);
                $msg = "Great success! Since the artifact was already placed on this level, the position $willbeup.";
                if ($loc->artifact_id == 2) {
                    $msg = "Great success! The scheduled artifact placement $willbeup.";
                    if ($set_time <= time()) {
                        artifact_location_delete_old($pdo);
                        $msg = "Great success! The artifact location $willbeup.";
                    }
                }
                $ret->success = true;
                $ret->message = $msg;
            } catch (Exception $e) {
                throw new Exception($e);
            }
        }
    }

    // update the artifact normally
    if (!$ret->success) {
        if ($set_time <= time()) { // instantly
            artifact_location_instant_update($pdo, $level_id, $x, $y, $rot);
            $ret->message = "Great success! The artifact location $willbeup.";
        } else { // scheduled
            artifact_location_schedule_update($pdo, $level_id, $x, $y, $rot, $set_time);
            $ret->message = "Great success! The scheduled artifact placement $willbeup.";
        }
    }

    // action log
    $action_lang = $set_time > time() ? 'scheduled the artifact placement' : 'placed the artifact';
    $info = "{mod_id: $mod->user_id, level_id: $level_id, x: $x, y: $y, rot: $rot, set_time: $set_time}";
    $msg = "$mod->name $action_lang from $ip $info";
    mod_action_insert($pdo, $mod->user_id, $msg, 'place-artifact', $ip);

    // tell the world
    $ret->success = true;
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
