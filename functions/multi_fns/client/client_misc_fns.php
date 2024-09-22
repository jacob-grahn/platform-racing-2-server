<?php


// convert client -> process function
function client_become_process($socket, $data)
{
    global $PROCESS_PASS, $PROCESS_IP;
    
    output('Ip is attempting to become process: ' . $socket->ip);
    if ($data === $PROCESS_PASS && (preg_match($PROCESS_IP, $socket->ip) || $socket->ip === '127.0.0.1')) {
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

// get player info (tries from socket first, then HTTP if not online)
function client_get_player_info($socket, $data)
{
    $me = $socket->getPlayer();
    $target = name_to_player($data);
    if (isset($target)) {
        $obj = $target->getInfo();
        $obj->following = in_array($obj->userId, $me->following_array);
        $obj->friend = in_array($obj->userId, $me->friends_array);
        $obj->ignored = in_array($obj->userId, $me->ignored_array);
        $ret = json_encode($obj);
        $socket->write("playerInfo`$ret");
        return;
    }
    $socket->write('playerInfo`0');
}
