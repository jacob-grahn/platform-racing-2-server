<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';
require_once HTTP_FNS . '/pages/vault/vault_fns.php';
require_once QUERIES_DIR . '/servers.php';
require_once QUERIES_DIR . '/rank_token_rentals.php';

$PAYPAL_JS_SDK_URL = "https://www.paypal.com/sdk/js?client-id=$PAYPAL_CLIENT_ID&currency=USD&commit=false";

$slug = find('slug', 'none', false); //, 'none');

$header = false;
try {
    // rate limiting
    rate_limit('vault-payment-'.$ip, 3, 1);
    rate_limit('vault-payment-'.$ip, 15, 4);

    // connect
    $pdo = pdo_connect();

    // check user
    $user_id = token_login($pdo);//, false);
    $user = user_select($pdo, $user_id);

    // sanity: is this user online?
    if ($user->server_id <= 0) {
        throw new Exception('You are not online. Please log in to purchase items from the vault.');
    }

    // item
    $item = describeVault($pdo, $user, [$slug])[0]; // checks item validity
    if (!$item->available) { // is it available?
        throw new Exception('You cannot purchase this item at this time. Please try again later.');
    } elseif ($item->price === 0) { // is it free?
        throw new Exception('This item isn\'t for sale.');
    }

    // format info
    $vom_faqs = '<a href="https://pr2hub.com/vault_faq.php" target="_blank">Vault of Magics FAQs</a>';
    $item_price = '$' . number_format($item->price, 2);
    $next_token_exp = $slug === 'rank-rental' ? rank_token_rentals_select_next_expiry($pdo, $user_id, $user->guild) : 0;

    // start page
    $header = true;
    output_header('Vault Payment', $user->power >= 2, $user->power == 3);

    // phpcs:disable
    ?>

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

        <script type='text/javascript'>
            function toggleFAQs()
            {
                var toggleBtn = document.getElementById('toggle_btn');
                var faqsDiv = document.getElementById('item_faqs');
                var curDisp = faqsDiv.style.display;
                toggleBtn.innerHTML = '<u>' + (curDisp == 'none' ? 'Hide' : 'Show') + ' Item FAQs</u>';
                faqsDiv.style.display = curDisp == 'none' ? 'block' : 'none';
            }
        </script>

        <p>Welcome, <b><?= htmlspecialchars($user->name, ENT_QUOTES) ?></b>. You are about to purchase:</p>

        <!-- item info -->
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
                    label: 'paypal'
                },

                createOrder: function(data, actions) {
                    /*if ('<?= $slug ?>' == 'rank-rental' && <?= $next_token_exp ?> > 0 && <?= $next_token_exp ?> > Math.floor(Date.now() / 1000)) {
                        alert('Error: One of your existing rank tokens expired since you loaded the page. Refreshing to save you money! :)');
                        window.location.reload();
                        return;
                    }*/
                    return actions.order.create({
                        application_context: {
                            shipping_preference: "NO_SHIPPING"
                        },
                        purchase_units: [
                            {
                                soft_descriptor: "PR2VAULT_<?= $user_id ?>",
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
                                        category: 'DIGITAL_GOODS'
                                    }
                                ]
                            }
                        ]
                    });
                },

                onApprove: function(data, actions) {
                    //alert('Transaction approved, but not complete. This is where something else is done before completing the transaction w/ commented code.');
                    return actions.order.capture().then(function(details) {
                        alert('Transaction completed by ' + details.payer.name.given_name + '!');
                    });
                },

                onError: function(err) {
                    console.log(err);
                }
            }).render('#paypal-button-container');
        </script>

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
