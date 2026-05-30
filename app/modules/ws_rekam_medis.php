<?php
return [
        'label'       => 'WS Rekam Medis',
        'icon'        => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V9m6 8V9m-6 8h6"/></svg>',
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