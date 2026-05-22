<?php

function generateSignature($consId, $secretKey)
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