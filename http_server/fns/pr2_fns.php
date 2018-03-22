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



require_once __DIR__ . '/../queries/users/user_select.php';
require_once __DIR__ . '/../queries/pr2/pr2_select.php';
require_once __DIR__ . '/../queries/ignored/ignored_select.php';
require_once __DIR__ . '/../queries/messages/message_insert.php';

function send_pm($pdo, $from_user_id, $to_user_id, $message)
{

    // call user info from the db
    $account = user_select($pdo, $from_user_id);
    $pr2_account = pr2_select($pdo, $from_user_id);

    // interpret user info from the db
    $account_power = $account->power;
    $account_rank = $pr2_account->rank;

    // let admins use the [url=][/url] tags
    if ($account_power >= 3) {
        $message = preg_replace('/\[url=(.+?)\](.+?)\[\/url\]/', '<a href="\1" target="_blank"><u><font color="#0000FF">\2</font></u></a>', $message);
    }

    // get sender ip and time of message
    $ip = get_ip();
    $time = time();

    // make sure the user's rank is above 3 (min rank to send PMs) and they aren't a guest
    if ($account_rank < 3) {
        throw new Exception('You need to level up to rank 3 to send private messages.');
    }
    if ($account_power <= 0) {
        throw new Exception('Guests cannot send private messages.');
    }


    // check the length of their message
    $message_len = strlen($message);
    if ($message_len > 1000) {
        throw new Exception('Could not send. The maximum message length is 1,000 characters. Your message is '. number_format($message_len) .' characters long.');
    }


    // prevent flooding
    $key1 = 'pm-'.$from_user_id;
    $key2 = 'pm-'.$ip;
    $interval = 60;
    $limit = 4;
    $error_message = 'You have sent 4 messages in the past 60 seconds, please wait a bit before sending another message.';
    rate_limit($key1, $interval, $limit, $error_message);
    rate_limit($key2, $interval, $limit, $error_message);


    // see if they've been ignored
    $ignored = ignored_select($pdo, $to_user_id, $from_user_id, true);
    if ($ignored) {
        throw new Exception('You have been ignored by this player. They won\'t recieve any chat or messages from you.');
    }


    // add the message to the db
    messages_insert($pdo, $to_user_id, $from_user_id, $message, $ip);
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
