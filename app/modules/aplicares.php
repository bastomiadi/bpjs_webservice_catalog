<?php
return [
        'label'       => 'Aplicares',
        'icon'        => '🏥',
        'description' => 'Modul Aplicares – Referensi & Ketersediaan Kamar',
        'base_url'    => getBaseUrl('aplicares', $currentDomain, $isDevMode),
        'sub_modules' => [
            [
                'key'         => 'referensi_kamar',
                'label'       => 'Referensi Kamar',
                'method'      => 'GET',
                'path'        => '/ref/kelas',
                'description' => 'Mendapatkan daftar referensi kamar rumah sakit',
                'params'      => [],
                'format'      => [
                    'response' => json_encode([
                        'metadata' => [
                            'code' => 1,
                            'message' => 'OK',
                            'totalitems' => 16
                        ],
                        'response' => [
                            'list' => [
                                ['kodekelas' => 'NON', 'namakelas' => '-'],
                                ['kodekelas' => 'VVP', 'namakelas' => 'VVIP']
                            ]
                        ]
                    ], JSON_PRETTY_PRINT),
                ],
            ],
            [
                'key'         => 'update_ketersediaan_tidur',
                'label'       => 'Update Ketersediaan Tempat Tidur',
                'method'      => 'POST',
                'path'        => '/bed/update/{kodeppk}',
                'description' => 'Update data ketersediaan tempat tidur kamar',
                'params'      => [
                    ['name' => 'kodeppk', 'type' => 'text', 'placeholder' => 'Kode PPK', 'default' => ''],
                ],
                'body'        => [
                    ['name' => 'kodekelas', 'type' => 'text', 'placeholder' => 'Kode Kelas (misal: VIP)', 'default' => ''],
                    ['name' => 'koderuang', 'type' => 'text', 'placeholder' => 'Kode Ruangan (misal: RG01)', 'default' => ''],
                    ['name' => 'namaruang', 'type' => 'text', 'placeholder' => 'Nama Ruangan (misal: Ruang Anggrek VIP)', 'default' => ''],
                    ['name' => 'kapasitas', 'type' => 'number', 'placeholder' => 'Kapasitas', 'default' => ''],
                    ['name' => 'tersedia', 'type' => 'number', 'placeholder' => 'Jumlah Tersedia', 'default' => ''],
                    ['name' => 'tersediapria', 'type' => 'number', 'placeholder' => 'Tersedia untuk Pria', 'default' => ''],
                    ['name' => 'tersediawanita', 'type' => 'number', 'placeholder' => 'Tersedia untuk Wanita', 'default' => ''],
                    ['name' => 'tersediapriawanita', 'type' => 'number', 'placeholder' => 'Tersedia untuk Priawanita', 'default' => ''],
                ],
                'format'      => [
                    'request' => json_encode([
                        'kodekelas' => 'VIP',
                        'koderuang' => 'RG01',
                        'namaruang' => 'Ruang Anggrek VIP',
                        'kapasitas' => '20',
                        'tersedia' => '10',
                        'tersediapria' => '0',
                        'tersediawanita' => '0',
                        'tersediapriawanita' => '0'
                    ], JSON_PRETTY_PRINT),
                ],
            ],
            [
                'key'         => 'ruangan_baru',
                'label'       => 'Ruangan Baru',
                'method'      => 'POST',
                'path'        => '/bed/create/{kodeppk}',
                'description' => 'Tambah data ruangan / kamar baru',
                'params'      => [
                    ['name' => 'kodeppk', 'type' => 'text', 'placeholder' => 'Kode PPK', 'default' => ''],
                ],
                'body'        => [
                    ['name' => 'kodekelas', 'type' => 'text', 'placeholder' => 'Kode Kelas (misal: VIP)', 'default' => ''],
                    ['name' => 'koderuang', 'type' => 'text', 'placeholder' => 'Kode Ruangan (misal: RG01)', 'default' => ''],
                    ['name' => 'namaruang', 'type' => 'text', 'placeholder' => 'Nama Ruangan (misal: Ruang Anggrek VIP)', 'default' => ''],
                    ['name' => 'kapasitas', 'type' => 'number', 'placeholder' => 'Kapasitas', 'default' => ''],
                    ['name' => 'tersedia', 'type' => 'number', 'placeholder' => 'Jumlah Tersedia', 'default' => ''],
                    ['name' => 'tersediapria', 'type' => 'number', 'placeholder' => 'Tersedia untuk Pria', 'default' => ''],
                    ['name' => 'tersediawanita', 'type' => 'number', 'placeholder' => 'Tersedia untuk Wanita', 'default' => ''],
                    ['name' => 'tersediapriawanita', 'type' => 'number', 'placeholder' => 'Tersedia untuk Priawanita', 'default' => ''],
                ],
                'format'      => [
                    'request' => json_encode([
                        'kodekelas' => 'VIP',
                        'koderuang' => 'RG01',
                        'namaruang' => 'Ruang Anggrek VIP',
                        'kapasitas' => '20',
                        'tersedia' => '10',
                        'tersediapria' => '0',
                        'tersediawanita' => '0',
                        'tersediapriawanita' => '0'
                    ], JSON_PRETTY_PRINT),
                ],
            ],
            [
                'key'         => 'ketersediaan_kamar',
                'label'       => 'Ketersediaan Kamar RS',
                'method'      => 'GET',
                'path'        => '/bed/read/{kodeppk}/{start}/{limit}',
                'description' => 'Melihat data ketersediaan kamar rumah sakit',
                'params'      => [
                    ['name' => 'kodeppk', 'type' => 'text', 'placeholder' => 'Kode PPK', 'default' => ''],
                    ['name' => 'start', 'type' => 'number', 'placeholder' => 'Start (default: 1)', 'default' => '1'],
                    ['name' => 'limit', 'type' => 'number', 'placeholder' => 'Limit (default: 10)', 'default' => '10'],
                ],
                'format'      => [
                    'response' => json_encode([
                        'metadata' => [
                            'code' => 1,
                            'message' => 'OK'
                        ],
                        'response' => [
                            'list' => [
                                [
                                    'kodekelas' => 'VIP',
                                    'koderuang' => 'RG01',
                                    'namaruang' => 'Ruang Anggrek VIP',
                                    'kapasitas' => '20',
                                    'tersedia' => '10'
                                ]
                            ]
                        ]
                    ], JSON_PRETTY_PRINT),
                ],
            ],
            [
                'key'         => 'hapus_ruangan',
                'label'       => 'Hapus Ruangan',
                'method'      => 'POST',
                'path'        => '/bed/delete/{kodeppk}',
                'description' => 'Menghapus data ruangan / kamar',
                'params'      => [
                    ['name' => 'kodeppk', 'type' => 'text', 'placeholder' => 'Kode PPK', 'default' => ''],
                ],
                'body'        => [
                    ['name' => 'kodekelas', 'type' => 'text', 'placeholder' => 'Kode Kelas (misal: VIP)', 'default' => ''],
                    ['name' => 'koderuang', 'type' => 'text', 'placeholder' => 'Kode Ruangan (misal: RG01)', 'default' => ''],
                ],
                'format'      => [
                    'request' => json_encode([
                        'kodekelas' => 'VIP',
                        'koderuang' => 'RG01'
                    ], JSON_PRETTY_PRINT),
                ],
            ],
        ],
    ];