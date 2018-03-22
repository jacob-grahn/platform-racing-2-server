<?php

function client_check_status($socket)
{
    $socket->write('ok');
}
