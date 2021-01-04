<?php


function retrieve_order($order_id)
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
