<?php


function paypal_retrieve_order($order_id)
{
    global $PAYPAL_CLIENT_ID, $PAYPAL_SECRET, $PAYPAL_API_ENDPOINT;

    $ch = curl_init("$PAYPAL_API_ENDPOINT/v2/checkout/orders/$order_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_USERPWD, "$PAYPAL_CLIENT_ID:$PAYPAL_SECRET");
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response);
}


function paypal_capture_payment($order_id)
{
    global $PAYPAL_CLIENT_ID, $PAYPAL_SECRET, $PAYPAL_API_ENDPOINT;

    $ch = curl_init("$PAYPAL_API_ENDPOINT/v2/checkout/orders/$order_id/capture");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_USERPWD, "$PAYPAL_CLIENT_ID:$PAYPAL_SECRET");
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response);
}


function paypal_refund_payment($capture_id)
{
    global $PAYPAL_CLIENT_ID, $PAYPAL_SECRET, $PAYPAL_API_ENDPOINT;

    $ch = curl_init("$PAYPAL_API_ENDPOINT/v2/payments/captures/$capture_id/refund");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_USERPWD, "$PAYPAL_CLIENT_ID:$PAYPAL_SECRET");
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response);
}


function coins_send_confirmation_pm($pdo, $user_id, $order_id, $coins, $bonus, $price, $new_coins)
{
    $cam_link = urlify('https://jiggmin2.com/cam', 'Contact a Mod forum');
    $jv_link = urlify('https://jiggmin2.com/forums', 'Jiggmin\'s Village');
    $pm = 'Howdy! This PM is to confirm your recent Coins order.'
        ."\n\nPayPal Order ID: $order_id"
        ."\nCoins Purchased: $coins" . ($bonus > 0 ? " (+$bonus bonus)" : '')
        ."\nPrice (USD): $price"
        ."\nNew Total Coins: $new_coins"
        ."\n\nThis is an automatically generated PM, so please don't reply. "
        ."If you encounter any problems with your order, please contact us using the $cam_link on $jv_link."
        ."\n\nThanks for your support!\n\n- Jiggmin";
    message_insert($pdo, $user_id, 1, $pm, '0');
}
