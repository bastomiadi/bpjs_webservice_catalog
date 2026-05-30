<?php

/**
 * BPJS Signature Generator
 * Generates HMAC-SHA256 signature for BPJS API authentication
 */

class BPSignature
{
    /**
     * Generate signature for BPJS API request
     *
     * @param string $consId Consumer ID
     * @param string $secretKey Consumer Secret
     * @return array ['timestamp' => int, 'signature' => string]
     */
    public static function generate($consId, $secretKey)
    {
        date_default_timezone_set('UTC');

        $timestamp = strval(time() - strtotime('1970-01-01 00:00:00'));

        $signature = base64_encode(
            hash_hmac(
                'sha256',
                $consId . "&" . $timestamp,
                $secretKey,
                true
            )
        );

        return [
            'timestamp' => $timestamp,
            'signature' => $signature
        ];
    }
}