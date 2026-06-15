<?php

namespace pr2\tests;

use pr2\multi\WebSocket;
use pr2\multi\PR2Client;

require_once __DIR__ . '/lib.php';

// PR2Client::write() hashes outgoing messages with SALT; the value is irrelevant
// to transport framing but must exist. handleRequest is overridden below so the
// inbound hash check is never reached.
if (!defined('SALT')) {
    define('SALT', 'test-salt');
}

// PR2Client (and its glue) call output() on errors / buffer kills. Provide a
// quiet global stub so the real logging stack is not required.
if (!function_exists('output')) {
    function output($str)
    {
        // swallow; uncomment to debug: fwrite(STDERR, "output: $str\n");
    }
}

require_once __DIR__ . '/../vend/socket/index.php';
require_once __DIR__ . '/../multiplayer_server/WebSocket.php';
require_once __DIR__ . '/../multiplayer_server/PR2Client.php';

// A PR2Client that records the application messages it decodes instead of
// dispatching them to game handlers. This isolates the transport edge.
class RecordingClient extends PR2Client
{
    public $received = array();

    protected function handleRequest($string)
    {
        $this->received[] = $string;
    }
}

// Build a real connected socket pair and wrap the server end in a client object.
function make_pair()
{
    $pair = array();
    if (!socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $pair)) {
        throw new \Exception('socket_create_pair failed');
    }
    list($server_sock, $client_sock) = $pair;
    socket_set_nonblock($server_sock);
    // socketpairs have no peer address, so every client shares the same (empty)
    // ip; reset the shared per-ip counter so the connection limit isn't hit.
    PR2Client::$ip_array = array();
    $server = new RecordingClient($server_sock);
    return array($server, $client_sock);
}

// Push bytes from the far (client) end, then let the server consume them.
function client_send($client_sock, $bytes)
{
    socket_write($client_sock, $bytes, strlen($bytes));
}

// Drain whatever the server has written back to the client end.
function client_recv($client_sock)
{
    $out = '';
    socket_set_nonblock($client_sock);
    while (($chunk = @socket_read($client_sock, 65536, PHP_BINARY_READ)) !== false && $chunk !== '') {
        $out .= $chunk;
        if (strlen($chunk) < 65536) {
            break;
        }
    }
    return $out;
}

// Same masked-frame builder used by the codec test.
function ws_client_frame($payload, $opcode = WebSocket::OP_TEXT, $mask_key = "\xAA\xBB\xCC\xDD")
{
    $len = strlen($payload);
    $header = chr(0x80 | ($opcode & 0x0F));
    if ($len < 126) {
        $header .= chr(0x80 | $len);
    } elseif ($len < 65536) {
        $header .= chr(0x80 | 126) . pack('n', $len);
    } else {
        $header .= chr(0x80 | 127) . pack('J', $len);
    }
    $masked = '';
    for ($i = 0; $i < $len; $i++) {
        $masked .= $payload[$i] ^ $mask_key[$i & 3];
    }
    return $header . $mask_key . $masked;
}


Test::group('Direct (raw) socket transport');

Test::it('decodes 0x04 delimited messages from a raw stream', function () {
    list($server, $client) = make_pair();
    client_send($client, 'first`msg' . chr(0x04) . 'second`msg' . chr(0x04));
    $server->read();
    Test::eq(array('first`msg', 'second`msg'), $server->received);
});

Test::it('buffers a partial raw message until the delimiter arrives', function () {
    list($server, $client) = make_pair();
    client_send($client, 'split');
    $server->read();
    Test::eq(array(), $server->received);
    client_send($client, 'message' . chr(0x04));
    $server->read();
    Test::eq(array('splitmessage'), $server->received);
});

Test::it('frames raw writes with a trailing 0x04 and no websocket header', function () {
    list($server, $client) = make_pair();
    client_send($client, 'login' . chr(0x04)); // establish raw transport
    $server->read();
    $server->write('hello');
    $wire = client_recv($client);
    // raw frame is hash`send_num`hello\x04 -- ends in 0x04, no 0x81 frame byte
    Test::assert(substr($wire, -1) === chr(0x04), 'ends with 0x04 delimiter');
    Test::assert(strpos($wire, 'hello') !== false, 'contains payload');
    Test::assert(ord($wire[0]) !== 0x81 && ord($wire[0]) !== 0x82, 'not a websocket frame');
});


Test::group('WebSocket transport');

Test::it('completes the handshake and replies with 101', function () {
    list($server, $client) = make_pair();
    $req = "GET / HTTP/1.1\r\nHost: x\r\nUpgrade: websocket\r\nConnection: Upgrade\r\n"
        . "Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==\r\nSec-WebSocket-Version: 13\r\n\r\n";
    client_send($client, $req);
    $server->read();
    $resp = client_recv($client);
    Test::assert(strpos($resp, '101 Switching Protocols') !== false, 'got 101');
    Test::assert(
        strpos($resp, 'Sec-WebSocket-Accept: s3pPLMBiTxaQ9kYGzzhZRbK+xOo=') !== false,
        'correct accept key'
    );
});

Test::it('decodes the same application messages as the raw transport', function () {
    list($server, $client) = make_pair();
    $req = "GET / HTTP/1.1\r\nSec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==\r\n\r\n";
    client_send($client, $req);
    $server->read();
    client_recv($client); // discard handshake response

    client_send($client, ws_client_frame('first`msg' . chr(0x04) . 'second`msg' . chr(0x04)));
    $server->read();
    Test::eq(array('first`msg', 'second`msg'), $server->received);
});

Test::it('reassembles a game message split across two websocket frames', function () {
    list($server, $client) = make_pair();
    client_send($client, "GET / HTTP/1.1\r\nSec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==\r\n\r\n");
    $server->read();
    client_recv($client);

    client_send($client, ws_client_frame('hel'));
    $server->read();
    Test::eq(array(), $server->received);
    client_send($client, ws_client_frame('lo' . chr(0x04)));
    $server->read();
    Test::eq(array('hello'), $server->received);
});

Test::it('handshake and first data frame in a single read still work', function () {
    list($server, $client) = make_pair();
    $req = "GET / HTTP/1.1\r\nSec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==\r\n\r\n";
    client_send($client, $req . ws_client_frame('boot`up' . chr(0x04)));
    $server->read();
    Test::eq(array('boot`up'), $server->received);
});

Test::it('wraps websocket writes in a frame whose payload ends in 0x04', function () {
    list($server, $client) = make_pair();
    client_send($client, "GET / HTTP/1.1\r\nSec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==\r\n\r\n");
    $server->read();
    client_recv($client);

    $server->write('hello');
    $wire = client_recv($client);
    $frames = WebSocket::decode($wire, $consumed);
    Test::eq(1, count($frames));
    Test::eq(WebSocket::OP_TEXT, $frames[0][0]);
    Test::assert(substr($frames[0][1], -1) === chr(0x04), 'payload ends in 0x04');
    Test::assert(strpos($frames[0][1], 'hello') !== false, 'payload contains message');
});

Test::it('mirrors a binary client opcode on its replies', function () {
    list($server, $client) = make_pair();
    client_send($client, "GET / HTTP/1.1\r\nSec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==\r\n\r\n");
    $server->read();
    client_recv($client);

    client_send($client, ws_client_frame('hi' . chr(0x04), WebSocket::OP_BINARY));
    $server->read();
    $server->write('yo');
    $wire = client_recv($client);
    $frames = WebSocket::decode($wire, $consumed);
    Test::eq(WebSocket::OP_BINARY, $frames[0][0]);
});

Test::it('responds to a ping with a pong', function () {
    list($server, $client) = make_pair();
    client_send($client, "GET / HTTP/1.1\r\nSec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==\r\n\r\n");
    $server->read();
    client_recv($client);

    client_send($client, ws_client_frame('ping-payload', WebSocket::OP_PING));
    $server->read();
    $wire = client_recv($client);
    $frames = WebSocket::decode($wire, $consumed);
    Test::eq(WebSocket::OP_PONG, $frames[0][0]);
    Test::eq('ping-payload', $frames[0][1]);
});

// when run directly (not aggregated by run.php), report and set exit status
if (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    exit(Test::summary());
}
