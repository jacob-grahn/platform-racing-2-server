<?php

header("Content-type: text/plain");

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/s3_connect.php';
require_once __DIR__ . '/../queries/users/user_select_expanded.php';

$ip = get_ip();

try {
    // POST check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // check referrer
    $ref = check_ref();
    if ($ref !== true) {
        throw new Exception("It looks like you're using PR2 from a third-party website. For security reasons, you may only upload a guild emblem from an approved site such as pr2hub.com.");
    }

    // rate limiting
    rate_limit('emblem-upload-attempt-'.$ip, 15, 1, "Please wait at least 15 seconds before trying to upload a guild emblem again.");
    rate_limit('emblem-upload-attempt-'.$ip, 900, 10, "Please wait at least 15 minutes before trying to upload a guild emblem again.");

    $image = file_get_contents("php://input");
    $image_rendered = imagecreatefromstring($image);

    // connect to the db
    $pdo = pdo_connect();
    $s3 = s3_connect();


    // check their login
    $user_id = token_login($pdo, false);

    // more rate limiting
    rate_limit('emblem-upload-attempt-'.$user_id, 15, 1, "Please wait at least 15 seconds before trying to upload a guild emblem again.");
    rate_limit('emblem-upload-attempt-'.$user_id, 900, 10, "Please wait at least 15 minutes before trying to upload a guild emblem again.");

    // get user info
    $account = user_select_expanded($pdo, $user_id);

    // sanity checks
    if ($account->rank < 20) {
        throw new Exception('Must be rank 20 or above to upload an emblem.');
    }
    if ($account->power <= 0) {
        throw new Exception('Guests can not upoad emblems.');
    }
    if (!isset($image)) {
        throw new Exception('No image recieved.');
    }
    if (strlen($image) > 20000) {
        throw new Exception('Image is too large. ' . strlen($image));
    }
    if (getimagesize($image_rendered) === false) {
        throw new Exception('File is not an image');
    }

    //--- send the image to s3
    $filename = $user_id . '-' . time() . '.jpg';
    $bucket = 'pr2emblems';
    $result = $s3->putObject($image, $bucket, $filename);
    if (!$result) {
        throw new Exception('Could not save image. :(');
    }

    // more rate limiting
    rate_limit('emblem-upload-'.$ip, 86400, 2, "You can upload a maximum of two guild emblem images per day. Try again tomorrow.");
    rate_limit('emblem-upload-'.$user_id, 86400, 2, "You can upload a maximum of two guild emblem images per day. Try again tomorrow.");

    //--- tell it to the world
    $reply = new stdClass();
    $reply->success = true;
    $reply->len = strlen($image);
    $reply->filename = $filename;
    echo json_encode($reply);
} catch (Exception $e) {
    $reply = new stdClass();
    $reply->error = $e->getMessage();
    echo json_encode($reply);
}
