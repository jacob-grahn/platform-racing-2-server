<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/vault/paypal_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';
require_once QUERIES_DIR . '/vault_coins_orders.php';

$encrypted_data = default_post('encrypted_data', '');
$num_option = (int) default_post('coin_option', '');
$order_id = default_post('order_id', '');

$ip = get_ip();
$suppl = ' Please return to PR2 to restart the order process.';
try {
    // rate limiting
    rate_limit('vault-confirm-order-'.$ip, 3, 1);
    rate_limit('vault-confirm-order-'.$ip, 15, 4);

    // check for data
    if (empty($encrypted_data) || $num_option <= 0 || empty($order_id)) {
        throw new Exception('Some data is missing.');
    }

    // decrypt data
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($PAYPAL_DATA_KEY);
    $passed = @json_decode($encryptor->decrypt($encrypted_data, $PAYPAL_DATA_IV));
    if (empty($passed)) {
        throw new Exception('Could not find a valid login token. Please log in again.');
    } else {
        $user_token = $passed->token;
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
    $option = $coins_options->options[$num_option];
    if (count($coins_options->options) < $num_option || empty($option)) {
        throw new Exception('Invalid Coins package selected.');
    } elseif ($user->coins + $option->coins + $option->bonus > 16777215) { // this is ridiculous. I realize that.
        unset($coins_options); // no suppl
        throw new Exception('You\'re rich!');
    }

    // get order info
    $paypal_data = paypal_retrieve_order($order_id);

    // check order validity
    if (!empty($paypal_data->name) && $paypal_data->name === 'RESOURCE_NOT_FOUND') {
        throw new Exception('Order not found.');
    } elseif (strtotime($paypal_data->create_time) + 3600 < time()) { // too far in the past?
        throw new Exception('Order timed out.');
    } elseif ($paypal_data->status !== 'APPROVED') { // incorrect status?
        throw new Exception('Order not eligible for completion.');
    } elseif ($paypal_data->id !== $order_id) { // order ID mismatch?
        throw new Exception('Order ID mismatch.');
    }

    // check order information
    $paypal_item = $paypal_data->purchase_units[0]->items[0];
    $item_price = (float) $paypal_item->unit_amount->value;
    if ($item_price != $option->price) {
        throw new Exception('Your order has been cancelled due to an information mismatch.');
    }

    // check for pending order, insert if none found
    $pending_order = vault_coins_order_select($pdo, $order_id);
    if (!empty($pending_order)) { // error handling for odd scenarios ordered by likelihood of occurring
        if ($pending_order->coins_before != $user->coins) {
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
    } else {
        vault_coins_order_insert($pdo, $user, $option, $order_id);
    }

    // make data to send to the next page
    $send_data = new stdClass();
    $send_data->token = $user_token;
    $send_data->coins_option = $num_option;
    $send_data->paypal_order_id = $paypal_data->id;
    $send_data->start_time = $time_started;
    $send_data->rand = mt_rand() / mt_getrandmax();
    $encryptor->setKey($PAYPAL_DATA_KEY);
    $encrypted_send_data = $encryptor->encrypt(json_encode($send_data), $PAYPAL_DATA_IV);

    // start page
    $head_extras = [
        '<link href="/style/vault.css" rel="stylesheet" type="text/css" />'
    ];
    output_header('Confirm Order', $user->power >= 2, $user->power == 3, true, $head_extras);

    // phpcs:disable
    ?>

        <!-- Submit script -->
        <script type="text/javascript">
            $(function () {
                var formConfirmed = false;
                $('form#confirm_order').submit(function (e) {
                    return confirm('Are you sure you\'d like to buy <?= $paypal_item->name ?>?');
                });
            });
        </script>

        <!-- Heading -->
        <div style="font-family: Gwibble; font-size: 30px; text-align: center;">-- Confirm Order --</div>
        <p>
            <div style="font-style: italic; text-align:center;">
                This is an order form for Coins. Coins can be used to purchase items for sale in the Vault of Magics.<br />
                <b>All sales are final</b>. For more information, please read the <a href="/terms_of_use.php" target="_blank">PR2 Terms of Use</a>.
            </div>
        </p>

        <hr />

        <p>Welcome, <b><?= htmlspecialchars($user->name, ENT_QUOTES) ?></b>. Here's your order summary:</p>

        <!-- Item info -->
        <p>
            <table id="order_breakdown">
                <tr style="margin-bottom: 4px;">
                    <th class="left">Item</th>
                    <th class="right">Price (USD)</th>
                </tr>
                <tr>
                    <td class="left"><?= $paypal_item->name ?></td>
                    <td class="right">$<?= number_format($item_price, 2) ?></td>
                </tr>
                <tr class="sep">
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td class="left">Subtotal</td>
                    <td class="right">$<?= number_format($item_price, 2) ?></td>
                </tr>
                <tr>
                    <td class="left">Fees</td>
                    <td class="right">$0.00</td>
                </tr>
                <tr>
                    <td class="left"><b>Total</b></td>
                    <td class="right"><b>$<?= number_format($item_price, 2) ?></b></td>
                </tr>
            </table>
        </p>

        <p>
            <div>
                Please make sure to double-check that all order details are correct. If they are, you may complete the transaction by clicking the button below.
            </div>
        </p>

        <p>
            <div>
                <form id="confirm_order" action="finish_order.php" method="POST">
                    <input type="hidden" name="data" value="<?= $encrypted_send_data ?>" />
                    <input type="submit" value="Complete Order" />
                </form>
            </div>
        </p>
    <?php
    // phpcs:enable
} catch (Exception $e) {
    output_header('Confirm Order', isset($user->power) && $user->power >= 2, isset($user->power) && $user->power == 3);
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . (isset($coins_options) ? $suppl : '');
} finally {
    output_footer();
    die();
}
