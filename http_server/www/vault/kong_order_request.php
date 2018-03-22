<?php

function order_request_handler($pdo, $request)
{

    //--- sort incoming data
    $order_info = $request->order_info; //The order info string you passed into purchaseItemsRemote
    list($pr2_user_id, $item_slug) = explode(',', $order_info);

    //---
    $items_raw = describeVault($pdo, $pr2_user_id, array($item_slug));

    //---
    $items = array();
    foreach ($items_raw as $raw) {
        $items[] = format_for_kong($raw);
    }

    //---
    $reply = new stdClass();
    $reply->items = $items;
    return( $reply );
}



function format_for_kong($desc)
{
    $item = new stdClass();
    $item->name = $desc->title;
    $item->description = $desc->description;
    $item->price = $desc->price;
    $item->image_url = $desc->imgUrlSmall;
    return( $item );
}
