<?php
return [
        'label'       => 'WS Rekam Medis',
        'icon'        => '📁',
        'description' => 'Modul WS Rekam Medis – Data Rekam Medis Elektronik',
        'base_url'    => getBaseUrl('ws_rekam_medis', $currentDomain, $isDevMode),
        'sub_modules' => [
            [
                'key'         => 'insert_medical_record',
                'label'       => 'Insert Medical Record',
                'method'      => 'POST',
                'path'        => '/eclaim/rekammedis/insert',
                'description' => 'Insert Medical Record - Menyimpan data rekam medis pasien',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body medical record', 'default' => '{"noSep": "1111R0010921V014299", "jnsPelayanan": "2", "bulan": "1", "tahun": "2019", "dataMR": "encrypted_gzip_data"}'],
                ],
            ],
        ],
    ];