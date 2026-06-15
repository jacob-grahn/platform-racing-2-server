<?php

namespace pr2\multi;

/**
 * Stateless WebSocket (RFC 6455) helpers.
 *
 * The PR2 multiplayer protocol is already self-framed (chr(0x04) delimited
 * messages with its own hash/sequence handshake). WebSocket support is therefore
 * implemented as a transparent transport: incoming frame payloads are fed into
 * the exact same message-processing path used by raw sockets, and outgoing
 * (already framed) buffers are simply wrapped in a WebSocket frame.
 *
 * Every method here is pure (no sockets, no globals) so it can be unit tested
 * with plain byte strings.
 */
class WebSocket
{
    // magic GUID from RFC 6455, section 1.3
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    // frame opcodes
    const OP_CONTINUATION = 0x0;
    const OP_TEXT         = 0x1;
    const OP_BINARY       = 0x2;
    const OP_CLOSE        = 0x8;
    const OP_PING         = 0x9;
    const OP_PONG         = 0xA;

    /**
     * Decide how to treat a brand new connection based on its first bytes.
     *
     * Returns 'ws' for a WebSocket handshake, 'raw' for the legacy Flash/socket
     * protocol, or null when there are not yet enough bytes to be sure.
     */
    public static function sniff($buffer)
    {
        if ($buffer === '') {
            return null;
        }
        $len = strlen($buffer);
        // a WebSocket client always opens with "GET " ... HTTP request
        if (strncmp($buffer, 'GET ', min($len, 4)) === 0) {
            return $len >= 4 ? 'ws' : null;
        }
        return 'raw';
    }

    /**
     * Build the HTTP 101 handshake response for an incoming request.
     *
     * $request must contain the full HTTP header block (up to and including the
     * terminating CRLFCRLF). Returns the response string on success, or null if
     * the header is incomplete or missing the Sec-WebSocket-Key.
     */
    public static function buildHandshakeResponse($request)
    {
        if (strpos($request, "\r\n\r\n") === false) {
            return null; // header not fully received yet
        }
        if (!preg_match('/Sec-WebSocket-Key:\s*(.+)\r\n/i', $request, $m)) {
            return null;
        }
        $accept = self::acceptKey(trim($m[1]));
        return "HTTP/1.1 101 Switching Protocols\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Accept: $accept\r\n"
            . "\r\n";
    }

    /**
     * Compute the Sec-WebSocket-Accept value for a given Sec-WebSocket-Key.
     */
    public static function acceptKey($key)
    {
        return base64_encode(sha1($key . self::GUID, true));
    }

    /**
     * Decode as many complete frames as are available in $buffer.
     *
     * Returns an array of [opcode, payload] pairs. $consumed is set (by
     * reference) to the number of leading bytes that were fully decoded; the
     * caller should drop those and keep the remainder for the next read.
     * Incomplete trailing frames are left untouched.
     */
    public static function decode($buffer, &$consumed)
    {
        $frames = array();
        $consumed = 0;
        $len = strlen($buffer);

        while (true) {
            $offset = $consumed;
            if ($len - $offset < 2) {
                break; // need at least the 2 byte header
            }

            $byte1 = ord($buffer[$offset]);
            $byte2 = ord($buffer[$offset + 1]);
            $opcode = $byte1 & 0x0F;
            $masked = ($byte2 & 0x80) !== 0;
            $payload_len = $byte2 & 0x7F;
            $offset += 2;

            if ($payload_len === 126) {
                if ($len - $offset < 2) {
                    break;
                }
                $payload_len = (ord($buffer[$offset]) << 8) | ord($buffer[$offset + 1]);
                $offset += 2;
            } elseif ($payload_len === 127) {
                if ($len - $offset < 8) {
                    break;
                }
                // 64-bit length; PHP_INT on 64-bit hosts handles realistic sizes
                $payload_len = 0;
                for ($i = 0; $i < 8; $i++) {
                    $payload_len = ($payload_len << 8) | ord($buffer[$offset + $i]);
                }
                $offset += 8;
            }

            $mask_key = '';
            if ($masked) {
                if ($len - $offset < 4) {
                    break;
                }
                $mask_key = substr($buffer, $offset, 4);
                $offset += 4;
            }

            if ($len - $offset < $payload_len) {
                break; // full payload not yet received
            }

            $payload = substr($buffer, $offset, $payload_len);
            $offset += $payload_len;

            if ($masked && $payload_len > 0) {
                $payload = self::applyMask($payload, $mask_key);
            }

            $frames[] = array($opcode, $payload);
            $consumed = $offset;
        }

        return $frames;
    }

    /**
     * Encode a server -> client frame. Server frames are never masked.
     */
    public static function encode($payload, $opcode = self::OP_TEXT)
    {
        $len = strlen($payload);
        $header = chr(0x80 | ($opcode & 0x0F)); // FIN + opcode

        if ($len < 126) {
            $header .= chr($len);
        } elseif ($len < 65536) {
            $header .= chr(126) . pack('n', $len);
        } else {
            // pack a 64-bit big-endian length
            $header .= chr(127) . pack('J', $len);
        }

        return $header . $payload;
    }

    /**
     * XOR a payload against a 4 byte masking key.
     */
    private static function applyMask($payload, $mask_key)
    {
        $out = '';
        $len = strlen($payload);
        for ($i = 0; $i < $len; $i++) {
            $out .= $payload[$i] ^ $mask_key[$i & 3];
        }
        return $out;
    }
}
