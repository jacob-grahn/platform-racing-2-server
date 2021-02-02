<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/vault/paypal_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once QUERIES_DIR . '/messages.php';
require_once QUERIES_DIR . '/vault_coins_orders.php';

$encrypted_data = default_post('data', '');

$ip = get_ip();
$header = false;
$suppl = ' Please return to PR2 to restart the order process.';
try {
    // rate limiting
    rate_limit('vault-finish-order-'.$ip, 3, 1);
    rate_limit('vault-finish-order-'.$ip, 15, 4);

    // check for data
    if (empty($encrypted_data)) {
        throw new Exception("No data received.$suppl");
    }

    // decrypt data
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($PAYPAL_DATA_KEY);
    $passed = @json_decode($encryptor->decrypt($encrypted_data, $PAYPAL_DATA_IV));
    if (empty($passed)) {
        throw new Exception("Invalid data received.$suppl");
    } else {
        $user_token = $passed->token;
        $coins_package_num = $passed->coins_option;
        $order_id = $passed->paypal_order_id;
        $time_started = $passed->start_time;
    }

    // sanity: older than 15 mins?
    if ($time_started + 900 < time() || $time_started > time()) {
        throw new Exception("Your request timed out.$suppl");
    }

    // connect
    $pdo = pdo_connect();

    // check user
    $user_id = token_login($pdo, false); // is it a valid token?
    $user = user_select($pdo, $user_id);
    if ($user->power <= 0) { // are they a guest?
        throw new Exception('Guests can\'t buy things. How about creating your own account?');
    } elseif ($user->server_id <= 0) { // are they online?
        throw new Exception('You are not online. Please log in to buy Coins.');
    }

    // get options info
    $coins_options = json_decode(file_get_contents(CACHE_DIR . '/coins_options.json'));
    if ($coins_options === false) {
        throw new Exception('Could not retrieve Coins package information.');
    }

    // sanity: valid option selected?
    $option = $coins_options->options[$coins_package_num];
    if (count($coins_options->options) < $coins_package_num || empty($option)) {
        throw new Exception('Invalid Coins package selected.');
    } elseif ($user->coins + $option->coins + $option->bonus > 16777215) { // this is ridiculous. I realize that.
        unset($coins_options); // no suppl
        throw new Exception('You\'re rich!');
    }

    // get order info
    $paypal_data = paypal_retrieve_order($order_id);

    // check paypal order information
    if (!empty($paypal_data->name) && $paypal_data->name === 'RESOURCE_NOT_FOUND') {
        throw new Exception('Order not found.');
    } elseif (strtotime($paypal_data->create_time) + 3600 < time()) { // too far in the past?
        throw new Exception('Order timed out.');
    } elseif ($paypal_data->status !== 'APPROVED') { // incorrect status?
        throw new Exception('Order not eligible for completion.');
    } elseif ($paypal_data->id !== $order_id) { // order ID mismatch?
        throw new Exception('Order ID mismatch.');
    }

    // check price match
    $paypal_item = $paypal_data->purchase_units[0]->items[0];
    $item_price = (float) $paypal_item->unit_amount->value;
    if ($item_price != $option->price) {
        throw new Exception('Your order has been cancelled due to an information mismatch.');
    }

    // check local order information
    $pending_order = vault_coins_order_select($pdo, $order_id);
    if (empty($pending_order)) {
        throw new Exception('Invalid order specified.');
    } elseif ($pending_order->coins_before != $user->coins) {
        throw new Exception('Your coin total doesn\'t match.'); // user ordered more coins or from vault since start
    } elseif ($pending_order->coins + $pending_order->bonus != $option->coins + $option->bonus) {
        throw new Exception('Coins purchasing options have been updated since you started your order.');
    } elseif ($pending_order->pr2_user_id != $user_id) {
        throw new Exception('This account didn\'t initiate this order.'); // won't happen if no funny business
    } elseif ($pending_order->status === 'expired' || $pending_order->created_time + 3600 < time()) {
        throw new Exception('This order is expired.'); // should be caught above
    } elseif ($pending_order->status === 'complete') {
        throw new Exception('This order is already complete.'); // should be caught above
    }

    // capture the PayPal payment
    $captured_data = paypal_capture_payment($order_id);
    if (empty($captured_data)) {
        unset($coins_options); // no suppl
        throw new Exception('Invalid response received from PayPal. Please report this error to a PR2 staff member.');
    } elseif (!empty($captured_data->name) && $captured_data->name === 'RESOURCE_NOT_FOUND') {
        throw new Exception('Invalid order specified.');
    } elseif (!empty($captured_data->status) && $captured_data->status !== 'COMPLETED') {
        throw new Exception('Order already completed.');
    }

    // pray that nothing fails here
    $completed_order = false;
    try {
        // make sure this is final
        $capture = $captured_data->purchase_units[0]->payments->captures[0];
        if (!$capture->final_capture) {
            throw new Exception('Your payment could not be captured.');
        } elseif ($capture->status !== 'COMPLETED' && $capture->status !== 'PENDING') {
            throw new Exception('This order is incomplete.');
        }

        // update local order information
        $net_money = $capture->seller_receivable_breakdown->net_amount->value;
        vault_coins_order_complete($pdo, $order_id, $net_money, $capture->id);
        $completed_order = true;

        // award coins
        user_update_coins($pdo, $user->user_id, $option->coins + $option->bonus);
        $new_coins = $user->coins + $option->coins + $option->bonus;
    } catch (Exception $e) {
        if (!empty($capture->id)) {
            paypal_refund_payment($capture->id);
        }
        if ($completed_order) {
            vault_coins_order_refund($pdo, $order_id, 'error in finish_order.php');
        }
        throw new Exception($e->getMessage() . ' Your money has been refunded to your payment source.');
    }

    // send confirmation PM
    $disp_coins = number_format($option->coins);
    $disp_bonus = number_format($option->bonus);
    $disp_price = '$' . number_format($option->price, 2);
    $disp_new_coins = number_format($new_coins);
    coins_send_confirmation_pm($pdo, $user_id, $order_id, $disp_coins, $disp_bonus, $disp_price, $disp_new_coins);

    // start page
    $header = true;
    $head_extras = [
        '<link href="/style/vault.css" rel="stylesheet" type="text/css" />'
    ];
    output_header('Finish Order', $user->power >= 2, $user->power == 3, false, $head_extras);

    // phpcs:disable
    ?>
        <!-- Heading -->
        <div style="font-family: Gwibble; font-size: 30px; text-align: center;">-- Finish Order --</div>
        <p>
            <div style="font-style: italic; text-align:center;">
                This is an order form for Coins. Coins can be used to purchase items for sale in the Vault of Magics.<br />
                <b>All sales are final</b>. For more information, please read the <a href="/terms_of_use.php" target="_blank">PR2 Terms of Use</a>.
            </div>
        </p>

        <hr />

        <p>
            Thank you for your order, <b><?= htmlspecialchars($user->name, ENT_QUOTES) ?></b>!
            Your account now has <b><?= number_format($user->coins + $option->coins + $option->bonus) ?> Coins</b>.
        </p>

        <p>
            You can now return to PR2 to purchase items from the Vault of Magics. If you have any questions or concerns about your order, please contact a member of the PR2 staff team using the <a href="https://jiggmin2.com/cam" target="_blank">Contact a Mod</a> forum on <a href="https://jiggmin2.com/forums" target="_blank">Jiggmin's Village</a>.
        </p>

    <?php
    // phpcs:enable
} catch (Exception $e) {
    if ($header === false) {
        $is_mod = isset($user->power) && $user->power >= 2;
        $is_admin = isset($user->power) && $user->power == 3;
        output_header('Finish Order', $is_mod, $is_admin);
    }
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . (isset($coins_options) ? $suppl : '');
} finally {
    output_footer();
    die();
}
