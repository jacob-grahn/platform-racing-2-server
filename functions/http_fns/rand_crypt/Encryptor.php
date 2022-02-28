<?php

namespace pr2\http;

class Encryptor
{

    private static $algorithm = 'AES-128-CBC';
    private static $full_opts = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
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
        $binary_iv = openssl_random_pseudo_bytes(16);
        $base64_iv = base64_encode($binary_iv);
        return $base64_iv;
    }

    public static function setIV($base64_iv)
    {
        $binary_iv = base64_decode($base64_iv);
        self::$base64_iv = $base64_iv;
        self::$binary_iv = $binary_iv;
    }

    private static function padPKCS5($str)
    {
        $pad = 16 - (strlen($str) % 16);
        return $str . str_repeat(chr($pad), $pad);
    }

    public static function encrypt($string, $base64_iv)
    {
        $binary_iv = base64_decode($base64_iv);
        $padstr = self::padPKCS5($string);
        $binary_encrypted = openssl_encrypt($padstr, self::$algorithm, self::$binary_key, OPENSSL_RAW_DATA, $binary_iv);
        $base64_encrypted = base64_encode($binary_encrypted);
        return $base64_encrypted;
    }

    public static function decrypt($base64_encrypted, $base64_iv)
    {
        $binary_iv = base64_decode($base64_iv);
        $binary_encrypted = base64_decode($base64_encrypted);
        $string = openssl_decrypt($binary_encrypted, self::$algorithm, self::$binary_key, self::$full_opts, $binary_iv);
        return preg_replace('/[[:cntrl:]]/', '', rtrim($string, "\0"));
    }
}
