<?php

die('Page currently disabled.');

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/vault/paypal_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';

$encrypted_token = default_post('encrypted_token', '');
$num_option = (int) default_post('coin_option', '');
$order_id = default_post('order_id', '');

$ip = get_ip();
$header = false;
try {
    // rate limiting
    rate_limit('vault-confirm-order-'.$ip, 3, 1);
    rate_limit('vault-confirm-order-'.$ip, 15, 4);

    // check for data
    if (empty($encrypted_token) || $num_option <= 0 || empty($order_id)) {
        throw new Exception('Some data is missing.');
    }

    // decrypt data
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($URL_KEY);
    $decrypted_token = @json_decode($encryptor->decrypt($encrypted_token, $URL_IV));
    if (empty($decrypted_token)) {
        throw new Exception('Could not find a valid login token. Please log in again.');
    } else {
        $user_token = $decrypted_token->token;
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
    }

    // get order info
    $paypal_data = paypal_retrieve_order($order_id);

    // check order validity
    if ($paypal_data->status !== 'APPROVED') { // incorrect status?
        throw new Exception('Order not eligible for completion.');
    } elseif ($paypal_data->id !== $order_id) { // order ID mismatch?
        throw new Exception('Order ID mismatch.');
    } elseif (strtotime($paypal_data->create_time) + 3600 < time()) { // too far in the past?
        throw new Exception('Order timed out.');
    }

    // check order information
    $paypal_item = $paypal_data->purchase_units[0]->items[0];
    $item_price = (float) $paypal_item->unit_amount->value;
    if ($item_price != $option->price) {
        throw new Exception('Your order has been cancelled due to an information mismatch.');
    }

    // make data to send to the next page
    $send_data = new stdClass();
    $send_data->token = $user_token;
    $send_data->pr2_order_id = '';
    $send_data->paypal_order_id = $paypal_data->id;

    // format info
    $vom_faqs = '<a href="https://pr2hub.com/vault/faq.php" target="_blank">Vault of Magics FAQs</a>';

    // start page
    $header = true;
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
                <b>All sales are final</b>. For more information, please read the <?= $vom_faqs ?>.
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
    if ($header === false) {
        $is_mod = isset($user->power) && $user->power >= 2;
        $is_admin = isset($user->power) && $user->power == 3;
        output_header('Confirm Order', $is_mod, $is_admin);
    }
    $suppl = ' Please return to PR2 to restart the order process. If this persists, please contact a PR2 staff member.';
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . (isset($coins_options) ? $suppl : '');
} finally {
    output_footer();
    die();
}
