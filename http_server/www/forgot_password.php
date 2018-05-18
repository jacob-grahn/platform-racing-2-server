<?php

header("Content-type: text/plain");

require_once HTTP_FNS . '/all_fns.php';
require_once HTTP_FNS . '/rand_crypt/to_hash.php';
require_once QUERIES_DIR . '/users/user_select_by_name.php';
require_once QUERIES_DIR . '/users/user_update_temp_pass.php';

$name = $_POST['name'];
$ip = get_ip();

// sanitize email
$problematic_chars = array('&', '"', "'", "<", ">");
$email = str_replace($problematic_chars, '', $email);

try {
    // check for post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // check referrer
    require_trusted_ref('request a new password');

    // rate limiting
    rate_limit('forgot-password-attempt-'.$ip, 5, 1);

    // sanity check: is it a valid email address?
    if (!valid_email($email)) {
        $email = htmlspecialchars($email);
        throw new Exception("\"$email\" is not a valid email address.");
    }

    // easter egg: Jiggmin's luggage
    if (strtolower($name) == 'jiggmin') {
        throw new Exception("The password to Jiggmin's luggage is 12345.");
    }

    // connect to the db
    $pdo = pdo_connect();

    // load the user account
    $user = user_select_by_name($pdo, $name);
    if ($user->power <= 0) {
        throw new Exception("Guests don't have accounts to recover. Try creating your own account.");
    }

    // the email must match
    if (strtolower($user->email) !== strtolower($email)) {
        $name = htmlspecialchars($name);
        $email = htmlspecialchars($email);
        throw new Exception("No account was found with the username \"$name\" and the email address \"$email\".");
    }

    // get the user id
    $user_id = $user->user_id;

    // more rate limiting
    rate_limit('forgot-password-'.$user_id, 900, 1, 'You may only request a new password once every 15 minutes.');

    // give them a new pass
    $pass = random_str(12);
    $pass_hash = to_hash($pass);
    user_update_temp_pass($pdo, $user_id, $pass_hash);

    // --- email them their new pass --- \\
    include 'Mail.php';
    
    $recipient = $user->email;

    $headers = array();
    $headers['From']    = 'Fred the Giant Cactus <contact@jiggmin.com>';
    $headers['To']      = $recipient;
    $headers['Subject'] = 'A password and chocolates from PR2';

    $body = "Hi $name,\n\n"
                ."It seems you forgot your password. Here's a new one: $pass\n\n"
                ."If you didn't request this email, then just ignore it. "
                ."Your old password will still work as long as you don't log in with this one.\n\n"
                ."All the best,\n"
                ."Fred";

    // Define SMTP Parameters
    $params['host'] = $EMAIL_HOST;
    $params['port'] = '465';
    $params['auth'] = 'PLAIN';
    $params['username'] = $EMAIL_USER;
    $params['password'] = $EMAIL_PASS;

    // Create the mail object using the Mail::factory method
    $mail_object = Mail::factory('smtp', $params);

    // Send the message
    $mail_object->send($recipient, $headers, $body);

    // tell the world
    echo 'message=Great success! You should receive an email with your new password shortly.';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
} finally {
    die();
}
