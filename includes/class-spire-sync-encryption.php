<?php
namespace SpireSync;

class Spire_Sync_Encryption {
    protected static $cipher = 'AES-256-CBC';

    /**
     * Encrypts data with a given key.
     *
     * @param string $data The data to encrypt.
     * @param string $key  The encryption key.
     * @return string|false The encrypted data or false on failure.
     */
    public static function encrypt( $data, $key ) {
        $iv_length = openssl_cipher_iv_length( self::$cipher );
        $iv = openssl_random_pseudo_bytes( $iv_length );
        $encrypted = openssl_encrypt( $data, self::$cipher, $key, 0, $iv );
        if ( false === $encrypted ) {
            return false;
        }
        return base64_encode( $iv . $encrypted );
    }

    /**
     * Decrypts data with a given key.
     *
     * @param string $data The data to decrypt.
     * @param string $key  The decryption key.
     * @return string|false The decrypted data or false on failure.
     */
    public static function decrypt( $data, $key ) {
        $data = base64_decode( $data );
        $iv_length = openssl_cipher_iv_length( self::$cipher );
        $iv = substr( $data, 0, $iv_length );
        $encrypted = substr( $data, $iv_length );
        return openssl_decrypt( $encrypted, self::$cipher, $key, 0, $iv );
    }
}