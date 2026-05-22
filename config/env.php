<?php

function loadEnv($path)
{
    if (!file_exists($path)) {
        die('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        // skip komentar
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // skip line kosong
        if (trim($line) === '') {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);

        $_ENV[trim($name)] = trim($value);
    }
}