<?php

// server shutdown
function process_shut_down($socket)
{
    if ($socket->process === true) {
        output('Received shutdown command. Initializing shutdown...');
        $socket->write('The shutdown was successful.');
        shutdown_server();
    }
}
