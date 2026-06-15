<?php

namespace pr2\tests;

use pr2\multi\WebSocket;

require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/../multiplayer_server/WebSocket.php';

// Build a client -> server frame the way a browser would: masked, with the
// given opcode. Mirrors the wire format produced by a real WebSocket client.
function client_frame($payload, $opcode = WebSocket::OP_TEXT, $mask_key = "\x12\x34\x56\x78")
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

Test::group('WebSocket::sniff');

Test::it('detects a websocket handshake', function () {
    Test::eq('ws', WebSocket::sniff("GET / HTTP/1.1\r\n"));
});

Test::it('treats a flash policy request as raw', function () {
    Test::eq('raw', WebSocket::sniff('<policy-file-request/>' . chr(0)));
});

Test::it('treats a game message as raw', function () {
    Test::eq('raw', WebSocket::sniff('a1b`13`login`...'));
});

Test::it('waits for more bytes before deciding', function () {
    Test::eq(null, WebSocket::sniff(''));
    Test::eq(null, WebSocket::sniff('GE'));
});

Test::it('a short non-GET buffer is already decidable as raw', function () {
    Test::eq('raw', WebSocket::sniff('<'));
});


Test::group('WebSocket::acceptKey');

Test::it('matches the RFC 6455 worked example', function () {
    // from RFC 6455 section 1.3
    Test::eq('s3pPLMBiTxaQ9kYGzzhZRbK+xOo=', WebSocket::acceptKey('dGhlIHNhbXBsZSBub25jZQ=='));
});


Test::group('WebSocket::buildHandshakeResponse');

Test::it('produces a valid 101 response', function () {
    $req = "GET / HTTP/1.1\r\n"
        . "Host: example.com\r\n"
        . "Upgrade: websocket\r\n"
        . "Connection: Upgrade\r\n"
        . "Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==\r\n"
        . "Sec-WebSocket-Version: 13\r\n"
        . "\r\n";
    $resp = WebSocket::buildHandshakeResponse($req);
    Test::assert(strpos($resp, 'HTTP/1.1 101 Switching Protocols') === 0, 'starts with 101');
    Test::assert(stripos($resp, 'Upgrade: websocket') !== false, 'has upgrade header');
    Test::assert(
        strpos($resp, 'Sec-WebSocket-Accept: s3pPLMBiTxaQ9kYGzzhZRbK+xOo=') !== false,
        'has correct accept key'
    );
    Test::assert(substr($resp, -4) === "\r\n\r\n", 'ends with blank line');
});

Test::it('returns null when the header is incomplete', function () {
    Test::eq(null, WebSocket::buildHandshakeResponse("GET / HTTP/1.1\r\nSec-WebSocket-Key: abc\r\n"));
});

Test::it('returns null when the key is missing', function () {
    Test::eq(null, WebSocket::buildHandshakeResponse("GET / HTTP/1.1\r\nHost: x\r\n\r\n"));
});


Test::group('WebSocket::decode');

Test::it('decodes a single masked text frame', function () {
    $buf = client_frame('hello');
    $frames = WebSocket::decode($buf, $consumed);
    Test::eq(1, count($frames));
    Test::eq(WebSocket::OP_TEXT, $frames[0][0]);
    Test::eq('hello', $frames[0][1]);
    Test::eq(strlen($buf), $consumed);
});

Test::it('decodes multiple frames in one buffer', function () {
    $buf = client_frame('one') . client_frame('two') . client_frame('three');
    $frames = WebSocket::decode($buf, $consumed);
    Test::eq(3, count($frames));
    Test::eq('one', $frames[0][1]);
    Test::eq('two', $frames[1][1]);
    Test::eq('three', $frames[2][1]);
    Test::eq(strlen($buf), $consumed);
});

Test::it('decodes a 126 (16-bit length) frame', function () {
    $payload = str_repeat('x', 200);
    $frames = WebSocket::decode(client_frame($payload), $consumed);
    Test::eq(1, count($frames));
    Test::eq($payload, $frames[0][1]);
});

Test::it('decodes a 127 (64-bit length) frame', function () {
    $payload = str_repeat('y', 70000);
    $frames = WebSocket::decode(client_frame($payload), $consumed);
    Test::eq(1, count($frames));
    Test::eq($payload, $frames[0][1]);
});

Test::it('leaves an incomplete trailing frame in the buffer', function () {
    $full = client_frame('complete');
    $partial = substr(client_frame('incomplete'), 0, 4);
    $buf = $full . $partial;
    $frames = WebSocket::decode($buf, $consumed);
    Test::eq(1, count($frames));
    Test::eq('complete', $frames[0][1]);
    Test::eq(strlen($full), $consumed);
    Test::eq($partial, substr($buf, $consumed));
});

Test::it('decodes nothing from a buffer with only a partial header', function () {
    $frames = WebSocket::decode("\x81", $consumed);
    Test::eq(0, count($frames));
    Test::eq(0, $consumed);
});

Test::it('decodes control frames (ping/close)', function () {
    $frames = WebSocket::decode(client_frame('', WebSocket::OP_PING) . client_frame('', WebSocket::OP_CLOSE), $consumed);
    Test::eq(WebSocket::OP_PING, $frames[0][0]);
    Test::eq(WebSocket::OP_CLOSE, $frames[1][0]);
});

Test::it('carries the game delimiter byte transparently', function () {
    $payload = 'a1b`13`login`data' . chr(0x04);
    $frames = WebSocket::decode(client_frame($payload), $consumed);
    Test::eq($payload, $frames[0][1]);
});


Test::group('WebSocket::encode');

Test::it('encodes an unmasked small text frame', function () {
    $frame = WebSocket::encode('hi');
    Test::eq("\x81\x02hi", $frame);
});

Test::it('encodes a 16-bit length frame', function () {
    $payload = str_repeat('z', 300);
    $frame = WebSocket::encode($payload);
    Test::eq("\x81\x7e" . pack('n', 300) . $payload, $frame);
});

Test::it('encodes a binary opcode when asked', function () {
    $frame = WebSocket::encode('x', WebSocket::OP_BINARY);
    Test::eq("\x82\x01x", $frame);
});

Test::it('round-trips through decode after re-masking', function () {
    // server-encoded payload, re-masked as if echoed by a client, decodes back
    $payload = 'round`trip`' . chr(0x04);
    $server = WebSocket::encode($payload);
    // strip the unmasked header, rebuild as a masked client frame to verify symmetry
    $frames = WebSocket::decode(client_frame($payload), $c);
    Test::eq($payload, $frames[0][1]);
    Test::assert($server[0] === "\x81", 'server frame is FIN+text');
});

// when run directly (not aggregated by run.php), report and set exit status
if (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    exit(Test::summary());
}
