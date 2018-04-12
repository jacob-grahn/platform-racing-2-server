<?php

function client_close($socket)
{
    $socket->close();
    $socket->onDisconnect();
}
