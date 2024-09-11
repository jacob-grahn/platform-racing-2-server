<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/artifact_location.php';
require_once QUERIES_DIR . '/follows.php';
require_once QUERIES_DIR . '/level_backups.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/new_levels.php';

$title = default_post('title');
$note = default_post('note');
$data = default_post('data');
$live = (int) default_post('live');
$to_newest = min((int) default_post('to_newest', 1), $live);
$min_level = (int) default_post('min_level');
$song = default_post('song');
$gravity = default_post('gravity');
$max_time = (int) default_post('max_time');
$items = default_post('items');
$remote_hash = default_post('hash');
$pass_hash = default_post('passHash', '');
$has_pass = (int) default_post('hasPass', 0);
$game_mode = default_post('gameMode', 'race');
$cowboy_chance = (int) default_post('cowboyChance', 5);
$bad_hats = trim(default_post('badHats', ''));
$num_hats = 15; // find a better way to do this

$override_banned = (bool) (int) default_post('override_banned', 0);
$overwrite_existing = (bool) (int) default_post('overwrite_existing', 0);

$time = time();
$ip = get_ip();
$on_success = 'normal';

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // sanity check: level data okay?
    if (empty($data) || strpos($data, 'm4') !== 0) {
        throw new Exception("Could not publish level. There was a problem with the data.");
    }

    // sanity check: obscenities?
    if ($live == 1 && (is_obscene($title) || is_obscene($note))) {
        throw new Exception('Could not publish level. Check the title and note for obscenities.');
    }

    // sanity check: title too long?
    if (strlen($title) > 50) {
        throw new Exception('The title is too long. Please limit it to 50 characters.');
    }

    // sanity check: note too long?
    if (strlen($note) > 255) {
        throw new Exception('The note is too long. Please limit it to 255 characters.');
    }

    // rate limiting
    $rl_msg = 'Please wait at least 10 seconds before trying to save again.';
    rate_limit('upload-level-attempt-'.$ip, 10, 3, $rl_msg);

    // connect
    $pdo = pdo_connect();
    $s3 = s3_connect();

    // check their login
    $user_id = (int) token_login($pdo, false, false, 'n');
    $user_name = id_to_name($pdo, $user_id);

    // check if banned (used later)
    $ban = check_if_banned($pdo, $user_id, $ip, 'b', false);

    // more rate limiting
    rate_limit('upload-level-attempt-'.$user_id, 10, 3, $rl_msg);

    // ensure the level survived the upload without data corruption
    $local_hash = md5($title . strtolower($user_name) . $data . $LEVEL_SALT);
    if ($local_hash !== $remote_hash) {
        throw new Exception('The level did not upload correctly. Maybe try again?');
    }

    // sanity check: are they a guest?
    $power = (int) user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $msg = 'Guests can\'t load or save levels. To access this feature, please create your own account.';
        throw new Exception($msg);
    }

    // sanity check: rank requirement
    $rank = pr2_select_true_rank($pdo, $user_id);
    if ($live == 1 && $rank < 3) {
        $msg = 'You need to be rank 3 or above to publish levels. Please uncheck the publish box and try again.';
        throw new Exception($msg);
    }

    // check game mode
    if ($game_mode === 'race') {
        $type = 'r';
    } elseif ($game_mode === 'deathmatch') {
        $type = 'd';
    } elseif ($game_mode === 'eggs' || $game_mode === 'egg') {
        $type = 'e';
    } elseif ($game_mode === 'objective') {
        $type = 'o';
    } elseif ($game_mode === 'hat') {
        $type = 'h';
    } else {
        $type = 'r';
    }

    // "bad" hats validation
    try {
        // skip if blank
        if ($bad_hats === '' && $type !== 'h') {
            throw new Exception('skip');
        }

        // preg_match raw "bad" hats input string
        if (empty(preg_match('/^\d+(,\d+)*$/', $bad_hats)) && $bad_hats !== '') {
            throw new Exception('The level did not upload correctly. Maybe try again?');
        }

        // verify that all of the "bad" hats passed are valid
        $arti_in_bad_hats = false;
        $num_bad_hats = 0;
        foreach (explode(',', $bad_hats) as $hat_id) {
            if ($hat_id < 2 || $hat_id > $num_hats + 1) {
                continue;
            }
            validate_prize('hat', $hat_id);
            $arti_in_bad_hats = !$arti_in_bad_hats ? $hat_id == 14 : true;
            $num_bad_hats++;
        }

        // hat attack checks
        if ($type === 'h') {
            if (strpos($bad_hats, '14') === false) { // make sure artifact is disabled
                $bad_hats = "$bad_hats,14";
                $num_bad_hats++;
            }
            if ($num_bad_hats >= $num_hats) { // check to make sure at least one hat is enabled
                throw new Exception('You must allow at least one hat in hat attack mode.');
            }
        }

        // just in case
        $bad_hats = trim($bad_hats, ',');
    } catch (Exception $e) {
        if ($e->getMessage() !== 'skip') {
            throw new Exception($e->getMessage());
        }
    }

    // allow saving as unpublished if banned
    if (!empty($ban)) {
        if (!$override_banned) {
            die("status=banned&scope=$ban->scope&ban_id=$ban->ban_id");
        }
        $live = $has_pass = 0;
        $pass_hash = '';
    }

    // unpublish if the level has a pass
    if ($has_pass === 1 && $live != 0) {
        $live = 0;
        $on_success = 'pass set with live';
    }

    // load the existing level
    $hash2 = null;
    $org_rating = 0;
    $org_votes = 0;
    $org_play_count = 0;
    $level = level_select_by_title($pdo, $user_id, $title);
    if ($level) {
        $level_id = (int) $level->level_id;

        // make sure the user really wants to overwrite
        if (!$overwrite_existing) {
            die("status=exists");
        }

        // preserve pass
        if ($has_pass === 1) {
            $hash2 = empty($pass_hash) ? $level->pass : sha1($pass_hash . $LEVEL_PASS_SALT);
        }

        // check if this is currently (or will be) the level of the week
        if (is_arti_level(artifact_locations_select($pdo), $level->level_id)) {
            $msg = 'Your level could not be modified because it is or will be the Level of the Week. '
                .'To save your progress, save this level under a different name. '
                .'Please contact a member of the PR2 Staff Team for more information.';
            throw new Exception($msg);
        }

        // backup the file that is about to be overwritten if not backed up within the last day
        $latest_bu = level_backups_select_latest_by_level($pdo, $level_id);
        if (empty($latest_bu) || ($latest_bu->version < $level->version && $level->time - $latest_bu->time > 86400)) {
            backup_level(
                $pdo,
                $s3,
                $user_id,
                $level_id,
                (int) $level->version,
                $title,
                (int) $level->live,
                (float) $level->rating,
                (int) $level->votes,
                $level->note,
                (int) $level->min_level,
                $level->song,
                (int) $level->play_count,
                $level->pass,
                $level->type,
                $level->bad_hats
            );
        }

        // update existing level
        $version = $level->version + 1;
        // phpcs:disable
        level_update($pdo, $level_id, $title, $note, $live, $time, $ip, $min_level, $song, $version, $hash2, $type, $bad_hats);
        // phpcs:enable

        // delete from newest if there and not published
        if (!$live || !$to_newest) {
            delete_from_newest($pdo, $level_id);
        }
    } else {
        if ($has_pass === 1) {
            $hash2 = empty($pass_hash) ? null : sha1($pass_hash . $LEVEL_PASS_SALT);
        }
        level_insert($pdo, $title, $note, $live, $time, $ip, $min_level, $song, $user_id, $hash2, $type, $bad_hats);
        $level = level_select_by_title($pdo, $user_id, $title);
        $level_id = (int) $level->level_id;
        $version = (int) $level->version;
    }

    // add to 'newest' level list
    $max_on_newest = (bool) check_newest_max($pdo, $user_name, $ip);
    if ($live && $to_newest) {
        if (!$max_on_newest) {
            new_level_insert($pdo, $level_id, $time, $ip);
        } else {
            $on_success = 'no newest';
        }
    }

    // create the save string
    $url_note = str_replace('&', '%26', $note);
    $url_title = str_replace('&', '%26', $title);
    $str = "level_id=$level_id&version=$version&user_id=$user_id&credits="
        ."&cowboyChance=$cowboy_chance&title=$url_title&time=$time"
        ."&note=$url_note&min_level=$min_level&song=$song&gravity=$gravity&max_time=$max_time"
        ."&has_pass=$has_pass&live=$live&items=$items&gameMode=$game_mode&badHats=$bad_hats"
        ."&data=$data";
    $str_to_hash = $version . $level_id . $str . $LEVEL_SALT_2;
    $hash = md5($str_to_hash);
    $str .= $hash;

    // save this file to the new level system
    if (!$s3->putObjectString($str, 'pr2levels1', "$level_id.txt")) {
        throw new Exception('A server error was encountered. Your level could not be saved.');
    }

    // write to the file system
    $file_path = WWW_ROOT . "/levels/$level_id.txt";
    if (is_writable($file_path)) {
        $file = fopen($file_path, "w");
        if ($file !== false) {
            fwrite($file, $str);
            fclose($file);
        }
    }

    // save the new file to the backup system
    backup_level(
        $pdo,
        $s3,
        $user_id,
        $level_id,
        $version,
        $title,
        $live,
        (float) $level->rating,
        (int) $level->votes,
        $note,
        $min_level,
        $song,
        (int) $level->play_count,
        $level->pass,
        $level->type,
        $bad_hats
    );

    // notify followers
    if ($live) {
        notify_followers($pdo, $user_id, $ip, $level_id, $title, $version, $note);
    }

    // tell every one it's time to party
    if ($on_success === 'pass set with live') {
        echo 'message=The save was successful, but since you set a password, '.
            'your level has been left unpublished. If you wish to publish '.
            'your level, remove the password and check the box to publish '.
            'the level.';
    } elseif ($on_success === 'no newest') {
        echo 'message=The save was successful, but since you recently published more than 3 maps, '.
            'your level was not added to the newest levels list. If you wish to have your level on newest, '.
            'wait for your other levels to disappear off page 1 of newest and then publish again.';
    } else {
        echo 'message=The save was successful.';
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
