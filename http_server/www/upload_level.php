<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/pr2/pr2_fns.php';
require_once QUERIES_DIR . '/levels/level_select_by_title.php';
require_once QUERIES_DIR . '/levels/level_insert.php';
require_once QUERIES_DIR . '/levels/level_update.php';
require_once QUERIES_DIR . '/new_levels/new_level_insert.php';

$title = $_POST['title'];
$note = $_POST['note'];
$data = $_POST['data'];
$live = (int) $_POST['live'];
$min_level = (int) $_POST['min_level'];
$song = $_POST['song'];
$gravity = $_POST['gravity'];
$max_time = (int) $_POST['max_time'];
$items = $_POST['items'];
$remote_hash = $_POST['hash'];
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
    rate_limit('upload-level-attempt-'.$ip, 10, 3, "Please wait at least 10 seconds before trying to save again.");

    // connect
    $pdo = pdo_connect();
    $s3 = s3_connect();

    // check their login
    $user_id = token_login($pdo, false);
    $user_name = id_to_name($pdo, $user_id);

    // more rate limiting
    rate_limit('upload-level-attempt-'.$user_id, 10, 3, "Please wait at least 10 seconds before trying to save again.");

    // ensure the level survived the upload without data curruption
    $local_hash = md5($title . strtolower($user_name) . $data . $LEVEL_SALT);
    if ($local_hash != $remote_hash) {
        throw new Exception('The level did not upload correctly. Maybe try again?');
    }

    // sanity check: are they a guest?
    $power = user_select_power($pdo, $user_id);
    if ($power <= 0) {
        throw new Exception(
            "Guests can't load or save levels. ".
            "To access this feature, please create your own account."
        );
    }

    // check game mode
    if ($game_mode == 'race') {
        $type = 'r';
    } elseif ($game_mode == 'deathmatch') {
        $type = 'd';
    } elseif ($game_mode == 'egg') {
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
        if ($pass_hash == '') {
            $hash2 = $org_pass_hash2;
        } else {
            $hash2 = sha1($pass_hash . $LEVEL_PASS_SALT);
        }
    }

    // load the existing level
    $org_rating = 0;
    $org_votes = 0;
    $org_play_count = 0;
    $level = level_select_by_title($pdo, $user_id, $title);
    if ($level) {
        $org_level_id = $level->level_id;
        $org_version = $level->version;
        $org_rating = $level->rating;
        $org_votes = $level->votes;
        $org_play_count = $level->play_count;
        $org_note = $level->note;
        $org_min_level = $level->min_level;
        $org_song = $level->song;
        $org_live = $level->live;
        $org_time = $level->time;
        $org_pass_hash2 = $level->pass;

        // backup the file that is about to be overwritten
        if (($time - $org_time) > (60*60*24*14)) {
            backup_level(
                $pdo,
                $s3,
                $user_id,
                $org_level_id,
                $org_version-1,
                $title,
                $org_live,
                $org_rating,
                $org_votes,
                $org_note,
                $org_min_level,
                $org_song,
                $org_play_count
            );
        }

        // update existing level
        $version = $level->version + 1;
        $level_id = $level->level_id;
        level_update(
            $pdo,
            $level_id,
            $title,
            $note,
            $live,
            $time,
            $ip,
            $min_level,
            (int) $song,
            $version,
            $hash2,
            $type
        );
    } else {
        level_insert($pdo, $title, $note, $live, $time, $ip, $min_level, (int) $song, $user_id, $hash2, $type);
        $level = level_select_by_title($pdo, $user_id, $title);
        $level_id = $level->level_id;
        $version = $level->version;
    }

    // add to 'newest' level list
    if ($live) {
        new_level_insert($pdo, $level_id, $time, $ip);
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
    $result = $s3->putObjectString($str, 'pr2levels1', $level_id.'.txt');
    if (!$result) {
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
        $org_rating,
        $org_votes,
        $note,
        $min_level,
        $song,
        $org_play_count
    );


    // tell every one it's time to party
    if ($on_success == 'pass set with live') {
        echo 'message=The save was successful, but since you set a password, '.
            'your level has been left unpublished. If you wish to publish '.
            'your level, remove the password and check the box to publish '.
            'the level.';
    } else {
        echo 'message=The save was successful.';
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
