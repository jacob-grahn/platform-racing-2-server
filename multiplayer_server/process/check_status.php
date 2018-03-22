<?php

function process_check_status($socket)
{
    $socket->write('ok');
}
