<?php

/**
 * BPJS API Request Handler
 * Handles HTTP requests to BPJS API
 */

class BPJSRequest
{
    /**
     * Send request to BPJS API
     *
     * @param array $config Request configuration
     * @return array Response data
     */
    public static function send($config)
    {
        $curl = curl_init();

        $headers = [
            'x-cons-id: ' . $config['cons_id'],
            'x-timestamp: ' . $config['timestamp'],
            'x-signature: ' . $config['signature'],
            'user_key: ' . $config['user_key'],
            'Content-Type: application/json'
        ];

        if (!empty($config['authorization'])) {
            $headers[] = 'X-authorization: ' . $config['authorization'];
        }

        // Validate scheme
        $scheme = parse_url($config['url'] ?? '', PHP_URL_SCHEME);
        if (!in_array(strtolower((string)$scheme), ['https', 'http'], true)) {
            return [
                'status' => false,
                'message' => 'Invalid URL scheme: only HTTP and HTTPS are permitted.',
                'http_code' => 0,
            ];
        }

        // Custom headers (e.g. x-token, x-username, x-password for antrean_fktp)
        if (!empty($config['custom_headers'])) {
            foreach ($config['custom_headers'] as $hName => $hValue) {
                // Strip CR and LF to prevent HTTP header injection
                $cleanName = trim(str_replace(["\r", "\n"], '', (string)$hName));
                $cleanValue = trim(str_replace(["\r", "\n"], '', (string)$hValue));
                if ($cleanName !== '') {
                    $headers[] = $cleanName . ': ' . $cleanValue;
                }
            }
        }

        $options = [
            CURLOPT_URL => $config['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $config['method'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        // Add JSON body for POST/PUT/PATCH requests
        if (!empty($config['body'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($config['body']);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // PHP 8.0+: curl_close() is a no-op; guard to avoid deprecation notice on 8.5+
        if (PHP_MAJOR_VERSION < 8) {
            curl_close($curl);
        }

        if ($error) {
            return [
                'status' => false,
                'message' => $error,
                'http_code' => 0,
            ];
        }

        return [
            'status' => true,
            'data' => json_decode($response, true),
            'raw_response' => $response,
            'http_code' => $httpCode,
        ];
    }
}
