<?php
return [
        'label'       => 'i-Care',
        'icon'        => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.317 4.317A4 4 0 016.414 3h7.172a4 4 0 012.096.317L19 6.414a2 2 0 01.586 1.414v7.172a2 2 0 01-.317 2.096l-2.097 2.097a4 4 0 01-2.096.317H6.414a4 4 0 01-2.096-.317L2 19l2.097-2.097z"/></svg>',
        'description' => 'Modul i-Care – Katalog iCare JKN',
        'base_url'    => getBaseUrl('icare', $currentDomain, $isDevMode),
        'sub_modules' => [
            [
                'key'         => 'fkrtl_validate',
                'label'       => 'FKRTL Validate',
                'method'      => 'POST',
                'path'        => '/api/rs/validate',
                //'path'        => '',
                'description' => 'API Data Riwayat Pelayanan - FKRTL (Fasilitas Kesehatan Rumah Sakit)',
                'body'        => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nomor Kartu (misal: 2200009338321)', 'default' => ''],
                    ['name' => 'kodedokter', 'type' => 'number', 'placeholder' => 'Kode Dokter', 'default' => ''],
                ],
            ],
            [
                'key'         => 'fktp_validate',
                'label'       => 'FKTP Validate',
                'method'      => 'POST',
                'path'        => '/api/pcare/validate',
                'description' => 'API Data Riwayat Pelayanan - FKTP (Fasilitas Kesehatan Tingkat Pertama)',
                'body'        => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nomor Kartu (misal: 2200009338321)', 'default' => ''],
                ],
            ],
        ],
    ];