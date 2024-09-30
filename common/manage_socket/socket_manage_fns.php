<?php


// tests server connectivity
function connect_to_server($address, $port, $salt)
{
    output("Attempting to connect to server at $address:$port...");
    $result = talk_to_server($address, $port, $salt, 'check_status`', true);
    return $result;
}


// send a command to the server
function talk_to_server($address, $port, $salt, $process_function, $receive = true, $output = true)
{
    global $PROCESS_PASS;

    if ($receive === false) {
        $output = false;
    }

    // build the parts of the message
    $end = chr(0x04);
    $send_num = 1;
    $data = $PROCESS_PASS;
    $intro_function = 'become_process';
    $str_to_hash = $salt . $send_num . '`' . $intro_function . '`' . $data;
    $local_hash = md5($str_to_hash);
    $sub_hash = substr($local_hash, 0, 3);

    // throw it together
    $to_process = $sub_hash . '`' . $send_num . '`' . $intro_function . '`' . $data . $end;
    $command = $process_function . $end;
    $send_str = $to_process . $command;

    // connect to the server
    if ($output === true) {
        output("Attempting to talk to server at $address:$port...");
    }
    $reply = true;
    $fsock = $output ? @fsockopen($address, $port, $errno, $errstr, 5) : fsockopen($address, $port, $errno, $errstr, 5);
    if ($fsock !== false) {
        if ($output === true) {
            output("Successfully connected! Writing: $process_function");
        }
        fputs($fsock, $send_str);
        stream_set_timeout($fsock, 5);
        if ($receive === true) {
            $reply = fread($fsock, 99999);
        }
        fclose($fsock);
    } else {
        $reply = false;
        if ($output === true) {
            output("Error $errno: $errstr. Could not connect to $address:$port.");
        }
    }

    // skip output if told to do so
    if ($output === false) {
        return $reply;
    }

    // interpret the reply
    if (empty($reply)) {
        output("ERROR: No response received from the server.");
        return false;
    } else {
        output("Server Reply: $reply");
        return $reply;
    }
}


// send a message to every server
// DO NOT OUTPUT ANYTHING FROM THIS FUNCTION FOR TESTING
function poll_servers($servers, $message, $output = true, $server_ids = array())
{
    $results = array();
    $query = array();

    foreach ($servers as $server) {
        $id = (int) $server->server_id;
        $query = new stdClass();

        if (count($server_ids) == 0 || array_search($id, $server_ids) !== false) {
            $result = (string) talk_to_server($server->address, $server->port, $server->salt, $message, $output);
            $result = preg_replace('/[[:cntrl:]]/', '', $result); // remove control characters causing errors
            $query->result = json_decode($result);
            $query->command = $message;
            $query->server_id = $id;
            $results[$id] = $query;
        }
    }

    return $results;
}
