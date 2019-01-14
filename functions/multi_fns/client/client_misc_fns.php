<?php


// convert client -> process function
function client_become_process($socket, $data)
{
    global $PROCESS_PASS, $PROCESS_IP;
    
    if ($data === $PROCESS_PASS && ($socket->ip === $PROCESS_IP || $socket->ip === '127.0.0.1')) {
        $socket->process = true;
    }
}


// check status
function client_check_status($socket)
{
    $socket->write('ok');
}


// close client connection
function client_close($socket)
{
    $socket->close();
    $socket->onDisconnect();
}


// ping
function client_ping($socket)
{
    $socket->write('ping`' . time());
}


// request a login id
function client_request_login_id($socket)
{
    if (!isset($socket->login_id)) {
        global $login_array;
        $socket->login_id = get_login_id();
        $login_array[$socket->login_id] = $socket;
        $socket->write('setLoginID`'.$socket->login_id);
    }
}
