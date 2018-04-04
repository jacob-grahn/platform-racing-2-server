<?php

require_once __DIR__ . '/../queries/level_backups/level_backups_insert.php';

function backup_level($pdo, $s3, $user_id, $level_id, $version, $title, $live = 0, $rating = 0, $votes = 0, $note = '', $min_level = 0, $song = 0, $play_count = 0)
{
    $filename = "$level_id.txt";
    $backup_filename = "$level_id-v$version.txt";
    $success = true;

    try {
        $result = $s3->copyObject('pr2levels1', $filename, 'pr2backups', $backup_filename);
        if (!$result) {
            throw new Exception('Could not save a backup of your level.');
        }

        level_backups_insert($pdo, $user_id, $level_id, $title, $version, $live, $rating, $votes, $note, $min_level, $song, $play_count);
    } catch (Exception $e) {
        $success = false;
    }

    return $success;
}



require_once __DIR__ . '/../queries/users/user_select_power.php';
require_once __DIR__ . '/../queries/pr2/pr2_select_true_rank.php';
require_once __DIR__ . '/../queries/ignored/ignored_select.php';
require_once __DIR__ . '/../queries/messages/message_insert.php';

function send_pm($pdo, $from_user_id, $to_user_id, $message)
{
    // call user info from the db
    $power = (int) user_select_power($pdo, $from_user_id);
    $active_rank = (int) pr2_select_true_rank($pdo, $from_user_id);

    // let admins use the [url=][/url] tags
    if ($power >= 3) {
        $message = preg_replace('/\[url=(.+?)\](.+?)\[\/url\]/', '<a href="\1" target="_blank"><u><font color="#0000FF">\2</font></u></a>', $message);
    }

    // get sender ip
    $ip = get_ip();

    // make sure the user's rank is above 3 (min rank to send PMs) and they aren't a guest
    if ($active_rank < 3) {
        throw new Exception('You need to be rank 3 or above to send private messages.');
    }
    if ($power <= 0) {
        throw new Exception("Guests can't send private messages. To use this feature, please create an account.");
    }

    // check the length of their message
    $message_len = strlen($message);
    if ($message_len > 1000) {
        throw new Exception('Could not send. The maximum message length is 1,000 characters. Your message is '. number_format($message_len) .' characters long.');
    }
    
    // see if they've been ignored
    $ignored = ignored_select($pdo, $to_user_id, $from_user_id, true);
    if ($ignored) {
        throw new Exception("You have been ignored by this player. They won't receive any chat or messages from you.");
    }

    // prevent flooding
    rate_limit('pm-'.$from_user_id, 60, 4, "You've sent 4 messages in the past 60 seconds. Please wait a bit before sending another message.");
    rate_limit('pm-'.$ip, 60, 4, "You've sent 4 messages in the past 60 seconds. Please wait a bit before sending another message.");

    // add the message to the db
    message_insert($pdo, $to_user_id, $from_user_id, $message, $ip);
}


require_once __DIR__ . '/../queries/guilds/guild_select_active_member_count.php';

function guild_count_active($pdo, $guild_id)
{
    $key = 'ga' . $guild_id;

    if (apcu_exists($key)) {
        $active_count = apcu_fetch($key);
    } else {
        $active_count = guild_select_active_member_count($pdo, $guild_id);
        apcu_store($key, $active_count, 3600); // one hour
    }
    return( $active_count );
}
