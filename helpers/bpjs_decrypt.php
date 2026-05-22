<?php

function stringDecrypt($key, $string)
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

function decompress($string)
{
    return \LZCompressor\LZString::decompressFromEncodedURIComponent($string);
}

function decryptResponse($consId, $secretKey, $timestamp, $response)
{
    $key = $consId . $secretKey . $timestamp;

    $decrypt = stringDecrypt($key, $response);

    $decompress = decompress($decrypt);

    return json_decode($decompress, true);
}