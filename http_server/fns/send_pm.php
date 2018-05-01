<?php

function send_pm($pdo, $from_user_id, $to_user_id, $message)
{
    // call user info from the db
    $from_power = (int) user_select_power($pdo, $from_user_id);
    $to_power = (int) user_select_power($pdo, $to_user_id);
    $active_rank = (int) pr2_select_true_rank($pdo, $from_user_id);

    // let admins use the [url=][/url] tags
    if ($from_power >= 3) {
        $message = preg_replace(
            '/\[url=(.+?)\](.+?)\[\/url\]/',
            '<a href="\1" target="_blank"><u><font color="#0000FF">\2</font></u></a>',
            $message
        );
    }

    // get sender ip
    $ip = get_ip();

    // make sure the user's rank is above 3 (min rank to send PMs) and they aren't a guest
    if ($active_rank < 3) {
        throw new Exception('You need to be rank 3 or above to send private messages.');
    }
    if ($from_power <= 0) {
        throw new Exception(
            "Guests can't use the private messaging system. ".
            "To access this feature, please create your own account."
        );
    }
    if ($to_power <= 0) {
         throw new Exception("You can't send private messages to guests.");
    }

    // check the length of their message
    $message_len = strlen($message);
    if ($message_len > 1000) {
        throw new Exception('Could not send. The maximum message length is '
            .'1,000 characters. Your message is '. number_format($message_len) .' characters long.');
    }

    // see if they've been ignored
    $ignored = ignored_select($pdo, $to_user_id, $from_user_id, true);
    if ($ignored) {
        throw new Exception(
            "You have been ignored by this player. ".
            "They won't receive any chat or messages from you."
        );
    }

    // prevent flooding
    rate_limit(
        'pm-'.$from_user_id,
        60,
        4,
        "You've sent 4 messages in the past 60 seconds. Please wait a bit before sending another message."
    );
    rate_limit(
        'pm-'.$ip,
        60,
        4,
        "You've sent 4 messages in the past 60 seconds. Please wait a bit before sending another message."
    );

    // add the message to the db
    message_insert($pdo, $to_user_id, $from_user_id, $message, $ip);
}
