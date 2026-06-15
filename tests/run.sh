#!/usr/bin/env bash
# Run the multiplayer server transport tests in a PHP 7.3 container.
#
# Builds a small cached image (php:7.3-cli + sockets extension) the first time,
# then reuses it so subsequent runs are fast. Pass a specific test file to run
# just that one, e.g.:  tests/run.sh tests/WebSocketTest.php
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
image="pr2-php-test"
target="${1:-tests/run.php}"

# build the test image only if it is missing
if ! docker image inspect "$image" >/dev/null 2>&1; then
    echo "Building $image (php:7.3-cli + sockets)..."
    docker build -t "$image" - <<'DOCKERFILE'
FROM php:7.3-cli
RUN docker-php-ext-install sockets
DOCKERFILE
fi

exec docker run --rm -v "$repo_root":/app -w /app "$image" php "$target"
