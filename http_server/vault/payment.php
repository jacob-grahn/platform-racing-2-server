<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once QUERIES_DIR . '/servers.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';

$PAYPAL_JS_SDK_URL = "https://www.paypal.com/sdk/js?client-id=$PAYPAL_CLIENT_ID&currency=USD&commit=false";

$token = find('token', '', false);
$slug = find('slug', 'none', false);

$header = false;
try {
    // rate limiting
    rate_limit('vault-payment-'.$ip, 3, 1);
    rate_limit('vault-payment-'.$ip, 15, 4);

    // connect
    $pdo = pdo_connect();

    // check user
    $user_id = token_login($pdo, false); // is it a valid token?
    $user = user_select($pdo, $user_id);
    if ($user->power <= 0) { // are they a guest?
        throw new Exception('Guests can\'t buy things. How about creating your own account?');
    } elseif ($user->server_id <= 0) { // are they online?
        throw new Exception('You are not online. Please log in to purchase items from the vault.');
    }

    // check item
    $item = describeVault($pdo, $user, [$slug])[0]; // is it valid?
    if (!$item->available) { // is it available?
        throw new Exception('You cannot purchase this item at this time. Please try again later.');
    } elseif ($item->price === 0) { // is it free?
        throw new Exception('This item isn\'t for sale.');
    }

    // format info
    $vom_faqs = '<a href="https://pr2hub.com/vault_faq.php" target="_blank">Vault of Magics FAQs</a>';
    $item_price = '$' . number_format($item->price, 2);

    // start page
    $header = true;
    output_header('Vault Payment', $user->power >= 2, $user->power == 3);

    // phpcs:disable
    ?>

        <!-- Custom functions -->
        <script type='text/javascript'>
            function toggleFAQs()
            {
                var toggleBtn = document.getElementById('toggle_btn');
                var faqsDiv = document.getElementById('item_faqs');
                var curDisp = faqsDiv.style.display;
                toggleBtn.innerHTML = '<u>' + (curDisp == 'none' ? 'Hide' : 'Show') + ' Item FAQs</u>';
                faqsDiv.style.display = curDisp == 'none' ? 'block' : 'none';
            }

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
        <div style="font-family: Gwibble; font-size: 30px; text-align: center;">-- Vault Purchase --</div>
        <p>
            <div style="font-style: italic;">
                You are about to purchase an item from the Vault of Magics.
                Please make sure to double-check that all order details listed below are correct.
                <b>All sales are final</b>. For more information, please read the <?= $vom_faqs ?>.
            </div>
        </p>

        <hr />

        <p>Welcome, <b><?= htmlspecialchars($user->name, ENT_QUOTES) ?></b>. You are about to purchase:</p>

        <!-- Item info -->
        <p>
            <div id="item_name" style="font-size: 20px"><b><u><?= $item->title ?></u></b></div>
            <div style='font-size: 11px; color: slategray;'><?= $item->description ?></div>
            <div><img src="<?= $item->imgUrl ?>" style="padding-top: 5px" /></div>
            <div>
                Price: <b><?= $item_price ?></b> (USD)<br />
                <a id="toggle_btn" style="cursor: pointer;" onclick="toggleFAQs()"><u>Show Item FAQs</u></a>
                <div id="item_faqs" style="display: none; margin-top: 15px; border: 1px solid #9a9a9a; border-radius: 5px; padding: 10px;">
                    <?= nl2br($item->faq) ?>
                </div>
            </div>
        </p>

        <p>
            <div>
                If this information is correct, you may complete the transaction by choosing one of the options below.
            </div>
        </p>

        <br />

        <!-- START PAYPAL -->
        <!-- Set up a container element for the button -->
        <div id="smart-button-container">
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
                                description: 'Vault of Magics purchase for PR2 user #<?= $user_id ?>.',
                                amount: {
                                    currency_code: "USD",
                                    value: <?= $item->price ?>,
                                    breakdown: {
                                        item_total: {
                                            currency_code: "USD",
                                            value: <?= $item->price ?>
                                        }
                                    }
                                },
                                items: [
                                    {
                                        name: "<?= $item->title ?>",
                                        description: "<?= $item->description ?>",
                                        unit_amount: {
                                            currency_code: "USD",
                                            value: <?= $item->price ?>
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
                    var obj = {
                        order_id: data.orderID,
                        token: "<?= $token ?>"
                    }
                    console.log('orderID: ' + obj.order_id);
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
        output_header('Vault Payment', $is_mod, $is_admin);
    }
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
} finally {
    output_footer();
    die();
}
