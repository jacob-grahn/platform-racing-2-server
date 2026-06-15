# Tests

Dependency-free PHP tests for the multiplayer server transport layer. They cover
the WebSocket codec (`multiplayer_server/WebSocket.php`) and the raw-vs-websocket
transport handling in `PR2Client`, including the protocol sniffing that lets a
single port serve both the legacy Flash/socket client and WebSocket clients.

There is no local PHP requirement — the server targets PHP 7.3, so run the tests
in the same image used to build it:

```sh
# all tests
docker run --rm -v "$PWD":/app -w /app php:7.3-cli php tests/run.php

# a single file
docker run --rm -v "$PWD":/app -w /app php:7.3-cli php tests/WebSocketTest.php
docker run --rm -v "$PWD":/app -w /app php:7.3-cli php tests/TransportIntegrationTest.php
```

The runner exits non-zero if any assertion fails.

- `WebSocketTest.php` — unit tests for sniff / handshake / frame decode + encode.
- `TransportIntegrationTest.php` — drives a real `socket_create_pair` through
  `PR2Client` to confirm raw and websocket streams yield identical application
  messages and are framed correctly on write.
