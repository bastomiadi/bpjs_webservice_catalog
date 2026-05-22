<?php

function bpjsRequest($config)
{
    $curl = curl_init();

    $headers = [
        'x-cons-id: ' . $config['cons_id'],
        'x-timestamp: ' . $config['timestamp'],
        'x-signature: ' . $config['signature'],
        'user_key: ' . $config['user_key'],
        'Content-Type: application/json'
    ];

    $options = [
        CURLOPT_URL => $config['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $config['method']
    ];

    // jika ada body json
    if (!empty($config['body'])) {
        $options[CURLOPT_POSTFIELDS] = json_encode($config['body']);
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);

    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return [
            'status' => false,
            'message' => $error
        ];
    }

    return [
        'status' => true,
        'data' => json_decode($response, true),
        'raw_response' => $response
    ];
}