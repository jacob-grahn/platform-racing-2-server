<?php

namespace pr2\tests;

use chabot\Socket;

require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/../vend/socket/index.php';

Test::group('Socket compatibility');

Test::it('recognizes sockets on PHP 7 and PHP 8', function () {
    $pair = array();
    if (!socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $pair)) {
        throw new \Exception('socket_create_pair failed');
    }

    Test::assert(Socket::isValidSocket($pair[0]), 'first socket is valid');
    Test::assert(Socket::isValidSocket($pair[1]), 'second socket is valid');
    Test::assert(!Socket::isValidSocket('not a socket'), 'string is not a socket');

    socket_close($pair[0]);
    socket_close($pair[1]);
});

Test::it('returns stable distinct ids for sockets', function () {
    $pair = array();
    if (!socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $pair)) {
        throw new \Exception('socket_create_pair failed');
    }

    $first_id = Socket::id($pair[0]);
    Test::eq($first_id, Socket::id($pair[0]), 'same socket id is stable');
    Test::assert($first_id !== Socket::id($pair[1]), 'paired sockets have distinct ids');

    socket_close($pair[0]);
    socket_close($pair[1]);
});

// when run directly (not aggregated by run.php), report and set exit status
if (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    exit(Test::summary());
}
