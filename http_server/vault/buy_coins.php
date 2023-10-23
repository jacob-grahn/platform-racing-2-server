<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';

$PAYPAL_JS_SDK_URL = "https://www.paypal.com/sdk/js?client-id=$PAYPAL_CLIENT_ID&currency=USD&commit=false";

$encrypted_data = find('data', '', false);

$ip = get_ip();

try {
    // rate limiting
    rate_limit('vault-buy-coins-'.$ip, 3, 1);
    rate_limit('vault-buy-coins-'.$ip, 15, 4);

    // check for data
    if (empty($encrypted_data)) {
        throw new Exception('Some data is missing.');
    }

    // decrypt data
    $encryptor = new \pr2\http\Encryptor();
    $encryptor->setKey($URL_KEY);
    $passed = @json_decode($encryptor->decrypt($encrypted_data, $URL_IV));
    if (!$passed) {
        throw new Exception('Invalid data received.');
    } else {
        $user_token = $passed->token;
        $time_started = $passed->time;
    }

    // sanity: older than 5 mins?
    if ($time_started + 300 < time() || $time_started > time() + 15) {
        throw new Exception('Your request timed out. Please return to PR2 to restart the order process.');
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

    // populate options info
    $options = $coins_options->options;
    $prices = $bonuses = [];
    foreach ($options as $num => $option_data) {
        $prices[$num] = $option_data->price;
        $bonuses[$num] = $option_data->bonus;
    }

    // make data to send to the next page
    $send_data = new stdClass();
    $send_data->token = $user_token;
    $send_data->start_time = $time_started;
    $send_data->rand = mt_rand() / mt_getrandmax();
    $encryptor->setKey($PAYPAL_DATA_KEY);
    $encrypted_send_data = $encryptor->encrypt(json_encode($send_data), $PAYPAL_DATA_IV);

    // format info
    $prices = '[' . join(', ', $prices) . ']';
    $bonuses = '[' . join(', ', $bonuses) . ']';

    // start page
    $head_extras = [
        '<link href="/style/vault.css" rel="stylesheet" type="text/css" />'
    ];
    output_header('Buy Coins', $user->power >= 2, $user->power == 3, true, $head_extras);

    // phpcs:disable
    ?>

        <!-- jQuery -->
        <script type="text/javascript">
            $(function () {
                var prices = <?= $prices ?>;
                var bonuses = <?= $bonuses ?>;

                $('table#coins_select tr').each(function() {
                    $(this).click(function() {
                        var num_row = $(this).attr('id').split('_')[2];
                        $('#coins_opt_' + num_row).prop('checked', true);

                        if ($('div#smart-button-container').css('display') === 'none') {
                            $('div#smart-button-container').css('display', 'block');
                        }
                        if ($('p#coins_sel_total').css('display') === 'none') {
                            $('p#coins_sel_total').css('display', 'block');
                        }
                        $('span#total_cost').text(prices[num_row].toFixed(2));
                        $('span#total_coins').text((prices[num_row] * 10 + bonuses[num_row]).toLocaleString('en-US'));
                    });
                });
            });
        </script>

        <!-- Heading -->
        <div style="font-family: Gwibble; font-size: 30px; text-align: center;">-- Buy Coins --</div>
        <p>
            <div style="font-style: italic; text-align:center;">
                This is an order form for Coins. Coins can be used to purchase items for sale in the Vault of Magics.<br />
                <b>All sales are final</b>. For more information, please read the <a href="https://pr2hub.com/terms_of_use.php" target="_blank">PR2 Terms of Use</a>.
            </div>
        </p>

        <hr />

        <p>Welcome, <b><?= htmlspecialchars($user->name, ENT_QUOTES) ?></b>. Please select your coins package:</p>

        <!-- Coins options -->
        <p>
            <table id="coins_select">
            <?php

            foreach ($options as $num => $option) {
                if ($num == 0) {
                    continue;
                }
                ?>
                <tr id="coins_row_<?= $num ?>">
                    <td><input type="radio" name="opt_sel" value="<?= $num ?>" id="coins_opt_<?= $num ?>"></input></td>
                    <th>$<?= $option->price ?></th>
                    <td><?= number_format($option->price * 10) ?> <span class="smalltext">COINS</span></td>
                    <td><?= $option->bonus > 0 ? "+ $option->bonus<br /><span class=\"smalltext\">BONUS COINS</span>" : '' ?></td>
                </tr>
                <?php
            }
            ?>
            </table>
        </p>

        <p id="coins_sel_total" style="display: none">
            You will be charged <b>$<span id="total_cost">0.00</span></b> and <b><span id="total_coins">0</span> Coins</b> will be added to your account.
        </p>

        <p>
            <div>
                All prices in USD. If this information is correct, you may complete the transaction by choosing one of the options below.
            </div>
        </p>

        <br />

        <!-- START PAYPAL -->
        <!-- Set up a container element for the button -->
        <div id="smart-button-container" style="display: none;">
            <div id="paypal-button-container" style="margin: 0 auto; max-width: 75%;"></div>
        </div>

        <!-- Include the PayPal JavaScript SDK -->
        <script src="<?= $PAYPAL_JS_SDK_URL ?>" data-sdk-integration-source="button-factory"></script>

        <script type="text/javascript">
            // Render the PayPal button into #paypal-button-container
            paypal.Buttons({
                style: {
                    shape: 'pill',
                    color: 'blue',
                    layout: 'vertical',
                    label: 'pay'
                },

                createOrder: function(data, actions) {
                    if (<?= $time_started ?> + 300 < Math.round(Date.now() / 1000)) {
                        return location.reload();
                    }

                    var prices = <?= $prices ?>;
                    var bonuses = <?= $bonuses ?>;
                    var optionSelected = parseInt($('input[name="opt_sel"]:checked').val());
                    if (optionSelected > prices.length - 1 || optionSelected <= 0) {
                        return alert('Invalid option chosen.');
                    }

                    var price = prices[optionSelected];
                    var bonus = bonuses[optionSelected];
                    var totalCoins = price * 10 + bonus;

                    return actions.order.create({
                        application_context: {
                            brand_name: 'Platform Racing 2',
                            user_action: "CONTINUE",
                            shipping_preference: "NO_SHIPPING"
                        },
                        purchase_units: [
                            {
                                /* custom_id: "purchaseId", */
                                /* soft_descriptor: "PR2VOM_purchaseId_userId", */
                                description: 'Coins replenishment for PR2 user #<?= $user_id ?>.',
                                amount: {
                                    currency_code: "USD",
                                    value: price,
                                    breakdown: {
                                        item_total: {
                                            currency_code: "USD",
                                            value: price
                                        }
                                    }
                                },
                                items: [
                                    {
                                        name: totalCoins.toLocaleString('en-US') + " Coins",
                                        description: (price * 10).toLocaleString('en-US') + " Coins, plus " + bonus + " bonus Coins.",
                                        unit_amount: {
                                            currency_code: "USD",
                                            value: price
                                        },
                                        quantity: 1,
                                        category: "DIGITAL_GOODS"
                                    }
                                ]
                            }
                        ]
                    });
                },

                onApprove: function(data, actions) {
                    console.log('data: ' + JSON.stringify(data));
                    var obj = {
                        encrypted_data: "<?= $encrypted_send_data ?>",
                        coin_option: parseInt($('input[name="opt_sel"]:checked').val()),
                        order_id: data.orderID
                    }

                    // redirect to confirm order page
                    var form = document.createElement('form');
                    document.body.appendChild(form);
                    form.method = 'post';
                    form.action = '/vault/confirm_order.php';
                    for (var name in obj) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = obj[name];
                        form.appendChild(input);
                    }
                    return form.submit();
                },

                onError: function(err) {
                    console.error(err);
                }
            }).render('#paypal-button-container');
        </script>
        <!-- END PAYPAL -->

    <?php
    // phpcs:enable
} catch (Exception $e) {
    output_error_page($e->getMessage(), @$user, 'Buy Coins');
} finally {
    output_footer();
    die();
}
