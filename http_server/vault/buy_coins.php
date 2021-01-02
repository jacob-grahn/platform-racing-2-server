<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once HTTP_FNS . '/rand_crypt/Encryptor.php';

$PAYPAL_JS_SDK_URL = "https://www.paypal.com/sdk/js?client-id=$PAYPAL_CLIENT_ID&currency=USD&commit=false";

$encrypted_data = find('data', '', false);

$ip = get_ip();
$header = false;
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
    $data = @json_decode($encryptor->decrypt($encrypted_data, $URL_IV));
    if (!$data) {
        throw new Exception('Invalid data received.');
    } else {
        $user_token = $data->token;
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

    // format info
    $prices = '[' . join(', ', $prices) . ']';
    $bonuses = '[' . join(', ', $bonuses) . ']';
    $vom_faqs = '<a href="https://pr2hub.com/vault/faq.php" target="_blank">Vault of Magics FAQs</a>';

    // start page
    $header = true;
    output_header('Buy Coins', $user->power >= 2, $user->power == 3, true);

    // phpcs:disable
    ?>

        <!-- Custom functions -->
        <script type='text/javascript'>
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

            function redirectPost(url, data)
            {
                var form = document.createElement('form');
                document.body.appendChild(form);
                form.method = 'post';
                form.action = url;
                for (var name in data) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = data[name];
                    form.appendChild(input);
                }
                form.submit();
            }
        </script>

        <!-- Heading -->
        <div style="font-family: Gwibble; font-size: 30px; text-align: center;">-- Buy Coins --</div>
        <p>
            <div style="font-style: italic; text-align:center;">
                This is an order form for Coins. Coins can be used to purchase items for sale in the Vault of Magics.<br />
                <b>All sales are final</b>. For more information, please read the <?= $vom_faqs ?>.
            </div>
        </p>

        <hr />

        <p>Welcome, <b><?= htmlspecialchars($user->name, ENT_QUOTES) ?></b>. Please select your coins package:</p>

        <!-- Table styling -->
        <style type="text/css">
            table#coins_select {
                border: none;
            }

            table#coins_select tr {
                cursor: default;
                line-height: 1;
            }

            table#coins_select td, table#coins_select th {
                border: none;
                border-bottom: gray 1px dotted;
                font-size: 18px;
                height: 40px;
                width: 85px;
            }

            table#coins_select .smalltext {
                font-size: 10px;
            }

            table#coins_select td:last-child {
                color: darkgreen;
            }

            table#coins_select tr:last-child td, table#coins_select tr:last-child th {
                border: none;
            }
        </style>

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
                        encrypted_token: "<?= $encrypted_data ?>",
                        coin_option: parseInt($('input[name="opt_sel"]:checked').val()),
                        order_id: data.orderID
                    }
                    console.log('obj: ' + JSON.stringify(obj));
                    return redirectPost('/vault/confirm_order.php', obj);
                },

                onError: function(err) {
                    console.log(err);
                }
            }).render('#paypal-button-container');
        </script>
        <!-- END PAYPAL -->

    <?php
    // phpcs:enable
} catch (Exception $e) {
    if ($header === false) {
        $is_mod = isset($user->power) && $user->power >= 2;
        $is_admin = isset($user->power) && $user->power == 3;
        output_header('Buy Coins', $is_mod, $is_admin);
    }
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
} finally {
    output_footer();
    die();
}
