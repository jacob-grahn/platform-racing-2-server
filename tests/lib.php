<?php

// Minimal, dependency-free test harness for the multiplayer server.
// Run with: php tests/run.php   (see tests/README.md)

namespace pr2\tests;

class Test
{
    public static $passed = 0;
    public static $failed = 0;
    public static $current = '';

    public static function group($name)
    {
        echo "\n# $name\n";
    }

    public static function it($name, callable $fn)
    {
        self::$current = $name;
        try {
            $fn();
            self::$passed++;
            echo "  ok   - $name\n";
        } catch (\Throwable $e) {
            self::$failed++;
            echo "  FAIL - $name\n";
            echo "         " . $e->getMessage() . "\n";
        }
    }

    public static function assert($cond, $message = '')
    {
        if (!$cond) {
            throw new \Exception('assertion failed' . ($message ? ": $message" : ''));
        }
    }

    public static function eq($expected, $actual, $message = '')
    {
        if ($expected !== $actual) {
            throw new \Exception(
                'expected ' . self::dump($expected)
                . ' but got ' . self::dump($actual)
                . ($message ? " ($message)" : '')
            );
        }
    }

    public static function dump($v)
    {
        if (is_string($v)) {
            // make control characters visible
            return "'" . preg_replace_callback('/[^\x20-\x7e]/', function ($m) {
                return '\x' . str_pad(dechex(ord($m[0])), 2, '0', STR_PAD_LEFT);
            }, $v) . "'";
        }
        return var_export($v, true);
    }

    public static function summary()
    {
        $total = self::$passed + self::$failed;
        echo "\n" . self::$passed . "/$total passed, " . self::$failed . " failed\n";
        return self::$failed === 0 ? 0 : 1;
    }
}
