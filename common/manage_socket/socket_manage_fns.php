<?php


// restarts all servers (shuts down and starts)
function restart_servers($pdo, $output = true)
{
    global $SERVER_IP;

    // find servers
    $servers = servers_select($pdo);

    // output
    if ($output === true) {
        output('Restarting all active servers...');
    }

    // shut down and restart all servers
    foreach ($servers as $server) {
        if ($server->address == $SERVER_IP) {
            restart_server(PR2_ROOT . '/pr2.php', $server->address, $server->port, $server->salt, $server->server_id);
        } else {
            output("ERROR: Server #$server->server_id's address doesn't match the server IP.");
            continue;
        }
    }
    
    // output
    if ($output === true) {
        output('All server restart operations complete.');
    }
}


// restart a server
function restart_server($script, $address, $port, $salt, $server_id)
{
    $pid = read_pid($port);
    shut_down_server($pid, $address, $port, $salt);
    start_server($script, $port, $server_id);
}


// test all servers and start the ones that aren't running
function check_servers($pdo)
{
    global $SERVER_IP, $COMM_PASS;

    // test the policy server
    test_server(ROOT_DIR . '/policy_server/run_policy.php', 'localhost', 843, $COMM_PASS, 0);

    // load all active servers
    $servers = servers_select($pdo);

    // test all active servers at this address
    foreach ($servers as $server) {
        if ($server->address == $SERVER_IP) {
            output("Testing $server->server_name (ID #$server->server_id)...");
            test_server(PR2_ROOT . '/pr2.php', 'localhost', $server->port, $server->salt, $server->server_id);
        }
    }

    // tell it to the world
    output('All servers tested.');
}


// starts a server if it is not running
function test_server($script, $address, $port, $salt, $server_id)
{
    // tell the world
    output("Beginning test for server #$server_id at $address:$port ($script)");

    // connect
    $result = connect_to_server($address, $port, $salt);
    
    // test the server if able to connect
    if ($result !== false) {
        output("GOOD - Server #$server_id is running.");
    } else {
        output("BAD - Bad/No response from server #$server_id.");

        $pid = read_pid($port);
        shut_down_server($pid, $address, $port, $salt);

        start_server($script, $port, $server_id);
    }

    // tell the world
    output("Test of server #$server_id at $address:$port complete.");
}


// starts a server
function start_server($script, $port, $server_id)
{
    output("start_server: $script, $port");

    $log = '/home/jiggmin/pr2/log/'.$port.'-'.date("Fj-Y-g:ia").'.log';
    $command = "nohup php $script $server_id > $log & echo $!";
    output("Executing command: $command");
    $pid = exec($command);

    write_pid($pid, $port);
    return($pid);
}


// tests server connectivity
function connect_to_server($address, $port, $salt)
{
    output("Attempting to connect to server at $address:$port...");
    $result = talk_to_server($address, $port, $salt, 'check_status`', true);
    return $result;
}


// graceful shutdown
function shut_down_server($pid, $address, $port, $salt)
{
    $result = talk_to_server($address, $port, $salt, 'shut_down`', true);

    // make sure the port is dead
    if (!$result) {
        $kill_res = kill_pid($pid);
    }

    // tell the world
    if ($kill_res === true) {
        output("Shutdown of server running at $address:$port successful.");
    } else {
        output("Unable to kill port. The server could already be shut down.");
    }
}


// gets the pid file
function get_pid_file($port)
{
    $pid_file = '/home/jiggmin/pr2/pid/'.$port.'.txt';
    return($pid_file);
}


// write pid to a file
function write_pid($pid, $port)
{
    $pid_file = get_pid_file($port);
    output("Writing PID for port $port to $pid_file: $pid...");
    $handle = fopen($pid_file, 'w');
    if ($handle) {
        fwrite($handle, $pid);
        fclose($handle);
    }
    output("Write operation concluded.");
}


// read pid from file
function read_pid($port)
{
    $pid_file = get_pid_file($port);
    $pid = 0;
    output("Reading the PID from $pid_file...");
    $handle = fopen($pid_file, 'r');
    if ($handle !== false) {
        $pid = fread($handle, 999);
        fclose($handle);
        output("PID is: $pid \n");
    } else {
        output("The PID file does not exist.");
    }
    return($pid);
}


// kills a port
function kill_pid($pid)
{
    if ($pid != null && $pid != 0 && $pid != '') {
        system("kill ".$pid, $k);
        $pid = null;
        if (!$k) {
            return true;
        } else {
            return false;
        }
    } else {
        output('There is no PID to kill.');
        return true;
    }
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
    $fsock = fsockopen($address, $port, $errno, $errstr, 5);
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

    foreach ($servers as $server) {
        if (count($server_ids) == 0 || array_search($server->server_id, $server_ids) !== false) {
            $result = talk_to_server($server->address, $server->port, $server->salt, $message, $output);
            $server->command = $message;
            $server->result = json_decode($result);
            $results[] = $server;
        }
    }

    return($results);
}


// shell output format
function output($str)
{
    echo "* $str\n";
}
