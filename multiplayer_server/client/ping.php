<?php

function client_ping($socket)
{
    $socket->write('ping`' . time());
}
