<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once QUERIES_DIR . '/artifact_locations/artifact_location_update.php';
require_once QUERIES_DIR . '/servers/server_select.php';

$x = (int) find('x', 0);
$y = (int) find('y', 0);
$level_id = (int) find('levelId', 0);
$ip = get_ip();

try {
    // sanity check: is data missing?
    if (is_empty($x, false) || is_empty($y, false) || is_empty($level_id, false)) {
        throw new Exception("Some data is missing.");
    }

    // check referrer
    require_trusted_ref();

    // rate limiting
    rate_limit(
        'place-artifact-attempt-'.$ip,
        30,
        1,
        "Please wait at least 30 seconds before trying to set a new artifact location again."
    );

    // connect
    $pdo = pdo_connect();

    // check their login
    $user_id = (int) token_login($pdo);

    // more rate limiting
    if ($user_id != 1) {
        rate_limit(
            'place-artifact-'.$ip,
            3600,
            10,
            "The artifact can only be placed a maximum of 10 times per hour. Try again later."
        );
        rate_limit(
            'place-artifact-'.$user_id,
            3600,
            10,
            "The artifact can only be placed a maximum of 10 times per hour. Try again later."
        );
    }

    // sanity check: are they Fred?
    if ($user_id != 1 && $user_id != 4291976) {
        throw new Exception('You are not Fred.');
    }

    // update the artifact location in the database
    artifact_location_update($pdo, $level_id, $x, $y);

    // tell the world
    $message = "Great success! The artifact location will be updated at the top of the next minute.";
    echo "message=$message";
} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage());
    echo "error=$error";
    $message = "Error: $error";
} finally {
    echo "&message2=";
    try {
        $user = user_select($pdo, $user_id);
        $server_id = (int) $user->server_id;
        if ($user->server_id != 0) {
            $server = server_select($pdo, $server_id);
            $data = new stdClass();
            $data->user_id = $user_id;
            $data->message = $message;
            $data = json_encode($data);
            $reply = talk_to_server(
                $server->address,
                $server->port,
                $server->salt,
                'message_player`' . $data,
                true,
                false
            );
            
            if ($reply !== false) {
                echo $reply;
            } else {
                echo "No reply from the server.";
            }
        } else {
            echo "You're not online, so you couldn't be notified on the server.";
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    
    die();
}
