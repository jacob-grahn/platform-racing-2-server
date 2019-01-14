<?php

namespace pr2\http;

class Encryptor
{

    private static $algorithm = MCRYPT_RIJNDAEL_128;
    private static $mode = MCRYPT_MODE_CBC;
    private static $binary_key;
    private static $base64_key;
    private static $binary_iv;
    private static $base64_iv;

    public static function init($key)
    {
        $iv = self::generateIV();
        self::setIV($iv);
        self::setKey($key);
    }

    public static function setKey($base64_key)
    {
        self::$base64_key = $base64_key;
        self::$binary_key = base64_decode($base64_key);
    }

    public static function generateIV()
    {
        $binary_iv = mcrypt_create_iv(mcrypt_get_iv_size(self::$algorithm, self::$mode), MCRYPT_RAND);
        $base64_iv = base64_encode($binary_iv);
        return($base64_iv);
    }

    public static function setIV($base64_iv)
    {
        $binary_iv = base64_decode($base64_iv);
        self::$base64_iv = $base64_iv;
        self::$binary_iv = $binary_iv;
    }

    public static function encrypt($string, $base64_iv)
    {
        $binary_iv = base64_decode($base64_iv);
        $binary_encrypted = mcrypt_encrypt(self::$algorithm, self::$binary_key, $string, self::$mode, $binary_iv);
        $base64_encrypted = base64_encode($binary_encrypted);
        return $base64_encrypted;
    }

    public static function decrypt($base64_encrypted, $base64_iv)
    {
        $binary_iv = base64_decode($base64_iv);
        $binary_encrypted = base64_decode($base64_encrypted);
        $string = mcrypt_decrypt(self::$algorithm, self::$binary_key, $binary_encrypted, self::$mode, $binary_iv);
        $string = rtrim($string, "\0");
        return $string;
    }
}
