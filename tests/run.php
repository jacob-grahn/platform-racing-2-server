<?php

// Aggregate test runner. Includes every *Test.php file in this directory and
// prints a single summary. Run with:
//   docker run --rm -v "$PWD":/app -w /app php:7.3-cli php tests/run.php

require_once __DIR__ . '/lib.php';

foreach (glob(__DIR__ . '/*Test.php') as $file) {
    require_once $file;
}

exit(\pr2\tests\Test::summary());
