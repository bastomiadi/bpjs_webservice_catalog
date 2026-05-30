<?php

/**
 * BPJS Response Decryptor
 * Handles decryption and decompression of BPJS API responses
 */

class BPJSDecrypt
{
    /**
     * Decrypt string using AES-256-CBC
     *
     * @param string $key Decryption key
     * @param string $string Encrypted string
     * @return string Decrypted string
     */
    public static function stringDecrypt($key, $string)
    {
        $encrypt_method = 'AES-256-CBC';
        $key_hash = hex2bin(hash('sha256', $key));
        $iv = substr($key_hash, 0, 16);

        return openssl_decrypt(
            base64_decode($string),
            $encrypt_method,
            $key_hash,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Decompress LZString compressed data
     *
     * @param string $string Compressed string
     * @return string Decompressed string
     */
    public static function decompress($string)
    {
        return \LZCompressor\LZString::decompressFromEncodedURIComponent($string);
    }

    /**
     * Decrypt and decompress BPJS API response
     *
     * @param string $consId Consumer ID
     * @param string $secretKey Secret Key
     * @param string $timestamp Timestamp
     * @param string $response Encrypted response
     * @return array Decrypted response data
     */
    public static function decryptResponse($consId, $secretKey, $timestamp, $response)
    {
        $key = $consId . $secretKey . $timestamp;
        $decrypt = self::stringDecrypt($key, $response);
        $decompress = self::decompress($decrypt);
        return json_decode($decompress, true);
    }
}