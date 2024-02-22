<?php

namespace Helper;

use Exception;

/** Note :
 * Cipher Algo :
 * - BF-CBC
 * - AES-256-CBC    : expected 16 bytes
 *
 */
class EncryptionHelper
{
    private static $defaultCharset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private static $numberCharset  = '0123456789';
    private static $alphaCharset   = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * Encrypt function
     *
     * @param String $driver    : currently only support openssl.
     * @param String $word      : string to encrypt.
     * @param String $key       : paraphrase / key to encrypt.
     * @param string $cipher    : chiper algorithm.
     * @param Array $option     : extra data for each encryption, like iv on openssl encryption.
     * @return array
     */
    public static function encrypt(String $driver, String $word, String $key, String $cipher = 'AES-256-CBC', array $option = []): array
    {
        switch (strtolower($driver)) {
            case 'openssl':
            default:
                if (!array_key_exists('option', $option)) {
                    throw new Exception('Please provide option with integer value!');
                }

                if (!array_key_exists('iv', $option)) {
                    throw new Exception('Please provide Initialization Vector with string value or null to automatically generate iv!');
                }
                return self::opensslEncrypt($word, $key, $option['iv'], $cipher, $option['option']);
                break;
        }
    }

    /**
     * Decrypt function
     *
     * @param String $driver    : currently only support openssl.
     * @param String $word      : string to encrypt.
     * @param String $key       : paraphrase / key to encrypt.
     * @param string $cipher    : chiper algorithm.
     * @param Array $option     : extra data for each encryption, like iv on openssl encryption.
     * @return mixed
     */
    public static function decrypt(String $driver, String $word, String $key, String $cipher = 'AES-256-CBC', array $option = []): mixed
    {
        switch (strtolower($driver)) {
            case 'openssl':
            default:
                if (!array_key_exists('option', $option)) {
                    throw new Exception('Please provide option with integer value!');
                }

                if (!array_key_exists('iv', $option)) {
                    throw new Exception('Please provide Initialization Vector with string value or null to automatically generate iv!');
                }
                return self::opensslDecrypt($word, $key, $option['iv'], $cipher, $option['option']);
                break;
        }
    }

    /**
     * Openssl Encryption function
     *
     * @param String $word      : string to encrypt.
     * @param String $key       : paraphrase / key to encrypt.
     * @param string $cipher    : chiper algorithm.
     * @param integer $option   : openssl option.
     * @return array
     */
    protected static function opensslEncrypt(String $word, String $key, String $iv = null, String $cipher = 'AES-256-CBC', Int $option = 0): array
    {
        /** Using OpenSSl encryption method */
        $ivLength      = openssl_cipher_iv_length($cipher);
        // $encryptOption  = 0;

        /** Initialization vector */
        if (isset($iv) && !empty($iv) && $iv !== NULL) {
            $encryptionIV   = $iv;
        } else {
            // $encryptionIV   = random_bytes($ivLength);

            /** alternatively using random string as long as less than cipher expected. usually 1 normal char = 1 byte. CMIIW */
            $encryptionIV   = self::generateIV($ivLength);
        }

        /** Encryption Key */
        // $encryptionKey  = openssl_digest(php_uname(), 'MD5', true);

        /** Encryption of string process begins */
        $encrypted      = openssl_encrypt($word, $cipher, $key, $option, $encryptionIV);

        return [
            'encrypted' => $encrypted,
            'iv'        => $encryptionIV
        ];
    }

    /**
     * Undocumented function
     *
     * @param String $word  : Encrypted string
     * @param String $key   : Encryption key
     * @param string $cipher    : Cipher algorithm
     * @param integer $option   : Openssl decrypt option
     * @param String $iv        : Initizlitazion Vector
     * @return string
     */
    protected static function opensslDecrypt(String $word, String $key, String $iv, String $cipher = 'AES-256-CBC', Int $option = 0): string
    {
        return openssl_decrypt($word, $cipher, $key, $option, $iv);
    }

    /**
     * Generate random string function
     *
     * @param Int $length
     * @param string $charset
     * @return void
     */
    protected static function generateIV(Int $length, String $charset = "")
    {
        switch (true) {
            case strtolower($charset) == 'number':
            case strtolower($charset) == 'number-only':
                $charSet = self::$numberCharset;
                break;
            case strtolower($charset) == 'number-alpha':
                $charSet = self::$numberCharset . strtolower(self::$alphaCharset) . strtoupper(self::$alphaCharset);
                break;
            case strtolower($charset) == 'number-alpha-lower':
                $charSet = self::$numberCharset . strtolower(self::$alphaCharset);
                break;
            case strtolower($charset) == 'number-alpha-upper':
                $charSet = self::$numberCharset . strtoupper(self::$alphaCharset);
                break;
            case strtolower($charset) == 'alpha-upper':
            case strtolower($charset) == 'alpha-upper-only':
                $charSet = strtoupper(self::$alphaCharset);
                break;
            case strtolower($charset) == 'alpha-lower':
            case strtolower($charset) == 'alpha-lower-only':
                $charSet = strtolower(self::$alphaCharset);
                break;
            case strtolower($charset) == 'alpha':
            case strtolower($charset) == 'alpha-mix':
                $charSet = strtolower(self::$alphaCharset) . strtoupper(self::$alphaCharset);
                break;
            default:
                $charSet = !empty($charset) ? $charset : self::$defaultCharset;
                break;
        }

        $code = '';
        $charSetLength = strlen($charSet);

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charSetLength - 1);
            $code .= $charSet[$index];
        }

        return $code;
    }
}
