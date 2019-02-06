<?php

header("Content-type: text/plain");

require_once GEN_HTTP_FNS;
require_once QUERIES_DIR . '/level_backups.php';
require_once QUERIES_DIR . '/new_levels.php';

$title = default_post('title');
$note = default_post('note');
$data = default_post('data');
$live = (int) default_post('live');
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

$time = time();
$ip = get_ip();
$on_success = 'normal';

try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // sanity check
    if ($live == 1 && (is_obscene($title) || is_obscene($note))) {
        throw new Exception('Could not publish level. Check the title and note for obscenities.');
    }

    // rate limiting
    $rl_msg = 'Please wait at least 10 seconds before trying to save again.';
    rate_limit('upload-level-attempt-'.$ip, 10, 3, $rl_msg);

    // connect
    $pdo = pdo_connect();
    $s3 = s3_connect();

    // check their login
    $user_id = (int) token_login($pdo, false);
    $user_name = id_to_name($pdo, $user_id);

    // more rate limiting
    rate_limit('upload-level-attempt-'.$user_id, 10, 3, $rl_msg);

    // ensure the level survived the upload without data curruption
    $local_hash = md5($title . strtolower($user_name) . $data . $LEVEL_SALT);
    if ($local_hash !== $remote_hash) {
        throw new Exception('The level did not upload correctly. Maybe try again?');
    }

    // sanity check: are they a guest?
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        $msg = 'Guests can\'t load or save levels. To access this feature, please create your own account.';
        throw new Exception($msg);
    }

    // check game mode
    if ($game_mode == 'race') {
        $type = 'r';
    } elseif ($game_mode == 'deathmatch') {
        $type = 'd';
    } elseif ($game_mode == 'eggs') {
        $type = 'e';
    } elseif ($game_mode == 'objective') {
        $type = 'o';
    } else {
        $type = 'r';
    }

    // hash the password
    $hash2 = null;
    if ($has_pass == 1) {
        if ($live != 0) {
            $live = 0;
            $on_success = 'pass set with live';
        }
        $hash2 = is_empty($pass_hash) ? $org_pass_hash2 : sha1($pass_hash . $LEVEL_PASS_SALT);
    }

    // load the existing level
    $org_rating = 0;
    $org_votes = 0;
    $org_play_count = 0;
    $level = level_select_by_title($pdo, $user_id, $title);
    if ($level) {
        // backup the file that is about to be overwritten
        if ($time - $level->time > 1209600) { // 2 weeks
            backup_level(
                $pdo,
                $s3,
                $user_id,
                (int) $level->level_id,
                $level->version - 1,
                $title,
                (int) $level->live,
                (float) $level->rating,
                (int) $level->votes,
                $level->note,
                (int) $level->min_level,
                $level->song,
                (int) $level->play_count
            );
        }

        // update existing level
        $version = $level->version + 1;
        $level_id = (int) $level->level_id;
        level_update($pdo, $level_id, $title, $note, $live, $time, $ip, $min_level, $song, $version, $hash2, $type);

        // delete from newest if there and not published
        if (!$live) {
            delete_from_newest($pdo, $level_id);
        }
    } else {
        level_insert($pdo, $title, $note, $live, $time, $ip, $min_level, $song, $user_id, $hash2, $type);
        $level = level_select_by_title($pdo, $user_id, $title);
        $level_id = (int) $level->level_id;
        $version = (int) $level->version;
    }

    // add to 'newest' level list
    $to_newest = (bool) check_newest($pdo, $user_name, $ip);
    if ($live && $to_newest === true) {
        new_level_insert($pdo, $level_id, $time, $ip);
    } elseif ($live && $to_newest === false) {
        $on_success = 'no newest';
    }

    // create the save string
    $url_note = str_replace('&', '%26', $note);
    $url_title = str_replace('&', '%26', $title);
    $str = "level_id=$level_id&version=$version&user_id=$user_id&credits="
        ."&cowboyChance=$cowboy_chance&title=$url_title&time=$time"
        ."&note=$url_note&min_level=$min_level&song=$song&gravity=$gravity&max_time=$max_time"
        ."&has_pass=$has_pass&live=$live&items=$items&gameMode=$game_mode"
        ."&data=$data";
    $str_to_hash = $version . $level_id . $str . $LEVEL_SALT_2;
    $hash = md5($str_to_hash);
    $str .= $hash;


    // save this file to the new level system
    if (!$s3->putObjectString($str, 'pr2levels1', "$level_id.txt")) {
        throw new Exception('A server error was encountered. Your level could not be saved.');
    }

    $file = fopen(WWW_ROOT . "/levels/$level_id.txt", "w");
    fwrite($file, $str);
    fclose($file);


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
        (int) $level->play_count
    );


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
