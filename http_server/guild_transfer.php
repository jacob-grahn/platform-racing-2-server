<?php

require_once 'Mail.php';
require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once QUERIES_DIR . '/guild_transfers.php';

$encrypted_data = default_post('data', '');

$ip = get_ip();
$ret = new stdClass();
$ret->success = false;
try {
    // post check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // check referrer
    require_trusted_ref('transfer your guild');

    // rate limit
    rate_limit('guild-transfer-attempt-'.$ip, 5, 2, $rl_msg);

    // sanity check
    if (is_empty($encrypted_data)) {
        throw new Exception('No data was recieved.');
    }

    // decrypt data from client
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($ACCOUNT_CHANGE_KEY);
    $str_data = $encryptor->decrypt($encrypted_data, $ACCOUNT_CHANGE_IV);
    $data = json_decode($str_data);

    // check email
    $email = str_replace(['&', '"', "'", '<', '>'], '', $data->email);
    if (!valid_email($email)) { // valid email?
        throw new Exception(htmlspecialchars($email, ENT_QUOTES) . ' is not a valid email address.');
    }

    // connect
    $pdo = pdo_connect();

    // log in
    $user = pass_login($pdo, $data->name, $data->pass, 'g');
    $user_id = (int) $user->user_id;
    $safe_name = htmlspecialchars($user->name, ENT_QUOTES);

    // sanity: emails match?
    if (strtolower($email) !== strtolower($user->email)) {
        throw new Exception('The email address you entered is incorrect.');
    }

    // sanity: are they in a guild?
    $guild_id = (int) $user->guild;
    if ($guild_id === 0) {
        throw new Exception('You are not a member of a guild.');
    }

    // get guild info
    $guild = guild_select($pdo, $guild_id);
    $owner_id = (int) $guild->owner_id;
    $safe_guild_name = htmlspecialchars($guild->guild_name, ENT_QUOTES);

    // sanity: are they the guild owner?
    if ($user_id !== $owner_id) {
        throw new Exception("You aren't the owner of $safe_guild_name.");
    }

    // get new user's info
    $new_user = user_select_by_name($pdo, trim($data->new_owner));
    $safe_new_name = htmlspecialchars($new_user->name, ENT_QUOTES);

    // sanity checks
    if ($user->power < 1 || $new_user->power < 1) { // is either player a guest?
        throw new Exception('Guests can\'t even really own guilds...');
    } elseif ($user_id == $new_user->user_id) { // new owner same as old one?
        throw new Exception("You already own $safe_guild_name, silly!");
    }

    // don't let a guild change hands more than once in a week
    $rl_msg = 'Guild ownership can only be transferred once per week.';
    rate_limit('guild-transfer-'.$guild->guild_id, 604800, 1, $rl_msg);

    // begin a guild transfer confirmation
    $code = random_str(24);
    guild_transfer_insert($pdo, $guild->guild_id, $user_id, $new_user->user_id, $code, $ip);

    // send a confirmation email
    $from = 'Fred the Giant Cactus <no-reply@mg.pr2hub.com>';
    $to = $user->email;
    $subject = 'PR2 Guild Transfer Confirmation';
    $body = "Howdy $safe_name,\n\nWe received a request to change the "
        ."owner of your guild $safe_guild_name to $safe_new_name. If you "
        ."requested this change, please click the link below to complete the "
        ."guild ownership transfer.\n\n"
        ."https://pr2hub.com/confirm_guild_transfer.php?code=$code\n\n"
        ."If you didn't request this change, you may need to change your password.\n\n"
        ."All the best,\nFred";
    send_email($from, $to, $subject, $body);

    // tell the world
    $ret->success = true;
    $ret->message = 'Almost done! We just sent a confirmation email to the email address on your account.'
        .' You\'ll still own your guild until you confirm the transfer.';
} catch (Exception $e) {
    $ret->error = $e->getMessage();
} finally {
    die(json_encode($ret));
}
