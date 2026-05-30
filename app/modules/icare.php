<?php
return [
        'label'       => 'i-Care',
        'icon'        => '💙',
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