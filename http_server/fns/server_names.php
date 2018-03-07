<?php

function get_server_name($port)
{
    if ($port == 9160) {
        $server_name = 'Derron';
    } elseif ($port == 9161) {
        $server_name = 'Carina';
    } elseif ($port == 9162) {
        $server_name = 'Grayan';
    } elseif ($port == 9163) {
        $server_name = 'Fitz';
    } elseif ($port == 9164) {
        $server_name = 'Loki';
    } elseif ($port == 9165) {
        $server_name = 'Promie';
    } elseif ($port == 9166) {
        $server_name = 'Morgana';
    } elseif ($port == 9167) {
        $server_name = 'Andres';
    } elseif ($port == 9168) {
        $server_name = 'Fred';
    } elseif ($port == 9169) {
        $server_name = 'Isabel';
    } else {
        $server_name = 'Port: '.$port;
    }
    
    return $server_name;
}
