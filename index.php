<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| LOAD LIBRARY
|--------------------------------------------------------------------------
*/

foreach (glob(__DIR__ . '/library/lz-string/src/LZCompressor/*.php') as $file) {
    require_once $file;
}

/*
|--------------------------------------------------------------------------
| LOAD FILE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/helpers/bpjs_signature.php';
require_once __DIR__ . '/helpers/bpjs_request.php';
require_once __DIR__ . '/helpers/bpjs_decrypt.php';

loadEnv(__DIR__ . '/.env');

$consId    = $_ENV['BPJS_CONS_ID'] ?? '';
$secretKey = $_ENV['BPJS_SECRET_KEY'] ?? '';
$userKey   = $_ENV['BPJS_USER_KEY'] ?? '';

/*
|--------------------------------------------------------------------------
| BPJS API MODULES & SUB-MODULES CONFIGURATION
|--------------------------------------------------------------------------
*/

// API Domain Version Configuration
// V1: apijkn.bpjs-kesehatan.go.id (old)
// V2: new-apijkn.bpjs-kesehatan.go.id (new)
// Dev: apijkn-dev.bpjs-kesehatan.go.id (development)

// Check for dev mode (takes precedence over API version)
$isDevMode = isset($_GET['dev_mode']) ? ($_GET['dev_mode'] === 'true') : (($_COOKIE['bpjs_dev_mode'] ?? 'false') === 'true');

// API version (only used when dev mode is off)
$apiDomainVersion = isset($_GET['api_version']) ? $_GET['api_version'] : (($_COOKIE['bpjs_api_version'] ?? 'v1'));

// Domain maps
$prodDomainMap = [
    'v1' => 'apijkn.bpjs-kesehatan.go.id',
    'v2' => 'new-apijkn.bpjs-kesehatan.go.id'
];

// Determine current domain based on mode
if ($isDevMode) {
    $currentDomain = 'apijkn-dev.bpjs-kesehatan.go.id';
} else {
    $currentDomain = $prodDomainMap[$apiDomainVersion] ?? $prodDomainMap['v1'];
}

// Helper function to get base URL for each module
// Aplicares: Dev mode uses V1, Production mode uses V1/V2 switching
function getBaseUrl($moduleKey, $currentDomain, $isDevMode) {
    // Production paths for each module
    $prodPaths = [
        'vclaim' => '/vclaim-rest',
        'antrean_rs' => '/antreanrs',
        'antrean_fktp' => '/antreanfktp',
        'apotek' => '/apotek-rest',
        'pcare' => '/pcare-rest',
        'icare' => '/ihs',
        'ws_rekam_medis' => '/erekammedis',
        'aplicares' => '/aplicaresws/rest',
    ];
    
    // Dev domains with paths for each module
    $devDomains = [
        'vclaim' => 'apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev',
        'antrean_rs' => 'apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev',
        'antrean_fktp' => 'apijkn-dev.bpjs-kesehatan.go.id/antreanfktp_dev',
        'apotek' => 'apijkn-dev.bpjs-kesehatan.go.id/apotek-rest-dev',
        'pcare' => 'apijkn-dev.bpjs-kesehatan.go.id/pcare-rest-dev',
        'icare' => 'apijkn-dev.bpjs-kesehatan.go.id/ihs_dev',
        'ws_rekam_medis' => 'apijkn-dev.bpjs-kesehatan.go.id/erekammedis_dev',
    ];
    
    // Aplicares: Dev mode uses V1, Production mode uses V1/V2
    if ($moduleKey === 'aplicares') {
        if ($isDevMode) {
            return 'https://apijkn.bpjs-kesehatan.go.id/aplicaresws/rest';
        }
        $path = isset($prodPaths[$moduleKey]) ? $prodPaths[$moduleKey] : '';
        return 'https://' . $currentDomain . $path;
    }
    
    // If dev mode is active, use dev domain
    if ($isDevMode && isset($devDomains[$moduleKey])) {
        return 'https://' . $devDomains[$moduleKey];
    }
    
    // Otherwise use current domain (V1 or V2) with module-specific path
    $path = isset($prodPaths[$moduleKey]) ? $prodPaths[$moduleKey] : '';
    return 'https://' . $currentDomain . $path;
}

$modules = [
    'aplicares' => [
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
    ],
    'vclaim' => [
        'label'       => 'VClaim',
        'icon'        => '📋',
        'description' => 'Modul VClaim – Klaim & Jaminan Peserta',
        'base_url'    => getBaseUrl('vclaim', $currentDomain, $isDevMode),
        'sub_modules' => [
            // Lembar Pengajuan Klaim (LPK)
            [
                'key'         => 'lpk_insert',
                'label'       => 'Insert LPK',
                'method'      => 'POST',
                'path'        => '/LPK/insert',
                'description' => 'Insert Rujukan Lembar Pengajuan Klaim',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body LPK', 'default' => ''],
                ],
            ],
            [
                'key'         => 'lpk_update',
                'label'       => 'Update LPK',
                'method'      => 'PUT',
                'path'        => '/LPK/update',
                'description' => 'Update Rujukan Lembar Pengajuan Klaim',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body LPK', 'default' => ''],
                ],
            ],
            [
                'key'         => 'lpk_delete',
                'label'       => 'Delete LPK',
                'method'      => 'DELETE',
                'path'        => '/LPK/delete',
                'description' => 'Delete Rujukan Lembar Pengajuan Klaim',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body LPK', 'default' => ''],
                ],
            ],
            [
                'key'         => 'lpk_data',
                'label'       => 'Data Lembar Pengajuan Klaim',
                'method'      => 'GET',
                'path'        => '/LPK/TglMasuk/{tglMasuk}/JnsPelayanan/{jnsPelayanan}',
                'description' => 'Pencarian data peserta berdasarkan tanggal masuk dan jenis pelayanan',
                'params'      => [
                    ['name' => 'tglMasuk', 'type' => 'text', 'placeholder' => 'Tanggal Masuk (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'jnsPelayanan', 'type' => 'text', 'placeholder' => 'Jenis Pelayanan (1=Inap, 2=Jalan)', 'default' => ''],
                ],
            ],
            // Monitoring
            [
                'key'         => 'monitoring_kunjungan',
                'label'       => 'Data Kunjungan',
                'method'      => 'GET',
                'path'        => '/Monitoring/Kunjungan/Tanggal/{tanggal}/JnsPelayanan/{jnsPelayanan}',
                'description' => 'Data Kunjungan berdasarkan tanggal dan jenis pelayanan',
                'params'      => [
                    ['name' => 'tanggal', 'type' => 'text', 'placeholder' => 'Tanggal (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'jnsPelayanan', 'type' => 'text', 'placeholder' => 'Jenis Pelayanan (1=Inap, 2=Jalan)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'monitoring_klaim',
                'label'       => 'Data Klaim',
                'method'      => 'GET',
                'path'        => '/Monitoring/Klaim/Tanggal/{tglPulang}/JnsPelayanan/{jnsPelayanan}/Status/{status}',
                'description' => 'Data Klaim berdasarkan tanggal pulang, jenis pelayanan, dan status',
                'params'      => [
                    ['name' => 'tglPulang', 'type' => 'text', 'placeholder' => 'Tanggal Pulang (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'jnsPelayanan', 'type' => 'text', 'placeholder' => 'Jenis Pelayanan (1=Inap, 2=Jalan)', 'default' => ''],
                    ['name' => 'status', 'type' => 'text', 'placeholder' => 'Status Klaim (1=Proses, 2=Pending, 3=Klaim)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'monitoring_histori',
                'label'       => 'Histori Pelayanan Peserta',
                'method'      => 'GET',
                'path'        => '/monitoring/HistoriPelayanan/NoKartu/{noKartu}/tglMulai/{tglMulai}/tglAkhir/{tglAkhir}',
                'description' => 'Histori Pelayanan Per Peserta',
                'params'      => [
                    ['name' => 'noKartu', 'type' => 'text', 'placeholder' => 'Nomor Kartu Peserta', 'default' => ''],
                    ['name' => 'tglMulai', 'type' => 'text', 'placeholder' => 'Tanggal Mulai (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'tglAkhir', 'type' => 'text', 'placeholder' => 'Tanggal Akhir (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'monitoring_jasa_raharja',
                'label'       => 'Klaim Jaminan Jasa Raharja',
                'method'      => 'GET',
                'path'        => '/monitoring/JasaRaharja/JnsPelayanan/{jnsPelayanan}/tglMulai/{tglMulai}/tglAkhir/{tglAkhir}',
                'description' => 'Monitoring Klaim Jaminan Jasa Raharja',
                'params'      => [
                    ['name' => 'jnsPelayanan', 'type' => 'text', 'placeholder' => 'Jenis Pelayanan (1=Inap, 2=Jalan)', 'default' => ''],
                    ['name' => 'tglMulai', 'type' => 'text', 'placeholder' => 'Tanggal Mulai (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'tglAkhir', 'type' => 'text', 'placeholder' => 'Tanggal Akhir (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            // Peserta
            [
                'key'         => 'peserta_kartu',
                'label'       => 'Peserta by No Kartu',
                'method'      => 'GET',
                'path'        => '/Peserta/nokartu/{noKartu}/tglSEP/{tglSEP}',
                'description' => 'Pencarian data peserta BPJS Kesehatan',
                'params'      => [
                    ['name' => 'noKartu', 'type' => 'text', 'placeholder' => 'Nomor Kartu Peserta', 'default' => ''],
                    ['name' => 'tglSEP', 'type' => 'text', 'placeholder' => 'Tanggal SEP (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'peserta_nik',
                'label'       => 'Peserta by NIK',
                'method'      => 'GET',
                'path'        => '/Peserta/nik/{nik}/tglSEP/{tglSEP}',
                'description' => 'Pencarian data peserta berdasarkan NIK Kependudukan',
                'params'      => [
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'NIK Peserta', 'default' => ''],
                    ['name' => 'tglSEP', 'type' => 'text', 'placeholder' => 'Tanggal SEP (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            // PRB (Rujukan Balik)
            [
                'key'         => 'prb_insert',
                'label'       => 'Insert PRB',
                'method'      => 'POST',
                'path'        => '/PRB/insert',
                'description' => 'Insert Rujuk Balik (PRB)',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body PRB', 'default' => ''],
                ],
            ],
            [
                'key'         => 'prb_update',
                'label'       => 'Update PRB',
                'method'      => 'PUT',
                'path'        => '/PRB/Update',
                'description' => 'Update PRB',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body PRB', 'default' => ''],
                ],
            ],
            [
                'key'         => 'prb_delete',
                'label'       => 'Delete PRB',
                'method'      => 'DELETE',
                'path'        => '/PRB/Delete',
                'description' => 'Hapus Data PRB',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body PRB', 'default' => ''],
                ],
            ],
            [
                'key'         => 'prb_cari_srb',
                'label'       => 'Cari PRB by SRB',
                'method'      => 'GET',
                'path'        => '/prb/{noSrb}/nosep/{noSep}',
                'description' => 'Pencarian data PRB berdasarkan Nomor SRB dan No SEP',
                'params'      => [
                    ['name' => 'noSrb', 'type' => 'text', 'placeholder' => 'Nomor SRB', 'default' => ''],
                    ['name' => 'noSep', 'type' => 'text', 'placeholder' => 'Nomor SEP', 'default' => ''],
                ],
            ],
            [
                'key'         => 'prb_cari_tanggal',
                'label'       => 'Cari PRB by Tanggal',
                'method'      => 'GET',
                'path'        => '/prb/tglMulai/{tglMulai}/tglAkhir/{tglAkhir}',
                'description' => 'Pencarian data PRB berdasarkan Tanggal SRB',
                'params'      => [
                    ['name' => 'tglMulai', 'type' => 'text', 'placeholder' => 'Tanggal Mulai (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'tglAkhir', 'type' => 'text', 'placeholder' => 'Tanggal Akhir (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'prb_potensi',
                'label'       => 'Rekap Potensi PRB',
                'method'      => 'GET',
                'path'        => '/prbpotensi/tahun/{tahun}/bulan/{bulan}',
                'description' => 'Rekap data peserta potensi PRB',
                'params'      => [
                    ['name' => 'tahun', 'type' => 'text', 'placeholder' => 'Tahun (yyyy)', 'default' => ''],
                    ['name' => 'bulan', 'type' => 'text', 'placeholder' => 'Bulan (01-12)', 'default' => ''],
                ],
            ],
            // Referensi
            [
                'key'         => 'ref_diagnosa',
                'label'       => 'Referensi Diagnosa',
                'method'      => 'GET',
                'path'        => '/referensi/diagnosa/{param}',
                'description' => 'Pencarian data diagnosa (ICD-10)',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Kode atau Nama Diagnosa', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_poli',
                'label'       => 'Referensi Poli',
                'method'      => 'GET',
                'path'        => '/referensi/poli/{param}',
                'description' => 'Pencarian data poli',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Kode atau Nama Poli', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_faskes',
                'label'       => 'Referensi Faskes',
                'method'      => 'GET',
                'path'        => '/referensi/faskes/{param1}/{param2}',
                'description' => 'Pencarian data fasilitas kesehatan',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Nama atau Kode Faskes', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Jenis Faskes (1=Faskes 1, 2=Faskes 2)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_dokter_pelayanan',
                'label'       => 'Referensi Dokter DPJP',
                'method'      => 'GET',
                'path'        => '/referensi/dokter/pelayanan/{jnsPelayanan}/tglPelayanan/{tglPelayanan}/Spesialis/{spesialis}',
                'description' => 'Pencarian data dokter DPJP untuk pengisian DPJP Layan',
                'params'      => [
                    ['name' => 'jnsPelayanan', 'type' => 'text', 'placeholder' => 'Jenis Pelayanan (1=Inap, 2=Jalan)', 'default' => ''],
                    ['name' => 'tglPelayanan', 'type' => 'text', 'placeholder' => 'Tanggal Pelayanan (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'spesialis', 'type' => 'text', 'placeholder' => 'Kode Spesialis/Subspesialis', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_propinsi',
                'label'       => 'Referensi Propinsi',
                'method'      => 'GET',
                'path'        => '/referensi/propinsi',
                'description' => 'Pencarian data propinsi',
                'params'      => [],
            ],
            [
                'key'         => 'ref_kabupaten',
                'label'       => 'Referensi Kabupaten',
                'method'      => 'GET',
                'path'        => '/referensi/kabupaten/propinsi/{kodePropinsi}',
                'description' => 'Pencarian data kabupaten berdasarkan kode propinsi',
                'params'      => [
                    ['name' => 'kodePropinsi', 'type' => 'text', 'placeholder' => 'Kode Propinsi', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_kecamatan',
                'label'       => 'Referensi Kecamatan',
                'method'      => 'GET',
                'path'        => '/referensi/kecamatan/kabupaten/{kodeKabupaten}',
                'description' => 'Pencarian data kecamatan berdasarkan kode kabupaten',
                'params'      => [
                    ['name' => 'kodeKabupaten', 'type' => 'text', 'placeholder' => 'Kode Kabupaten', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_diagnosaprb',
                'label'       => 'Referensi Diagnosa PRB',
                'method'      => 'GET',
                'path'        => '/referensi/diagnosaprb',
                'description' => 'Pencarian data diagnosa program PRB',
                'params'      => [],
            ],
            [
                'key'         => 'ref_obatprb',
                'label'       => 'Referensi Obat PRB',
                'method'      => 'GET',
                'path'        => '/referensi/obatprb/{param}',
                'description' => 'Pencarian data obat generik PRB',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nama Obat Generik', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_procedure',
                'label'       => 'Referensi Procedure',
                'method'      => 'GET',
                'path'        => '/referensi/procedure/{param}',
                'description' => 'Pencarian data procedure/tindakan',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nama atau Kode Procedure', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_kelasrawat',
                'label'       => 'Referensi Kelas Rawat',
                'method'      => 'GET',
                'path'        => '/referensi/kelasrawat',
                'description' => 'Pencarian data kelas rawat',
                'params'      => [],
            ],
            [
                'key'         => 'ref_dokter',
                'label'       => 'Referensi Dokter',
                'method'      => 'GET',
                'path'        => '/referensi/dokter/{param}',
                'description' => 'Pencarian data dokter dalam faskes sesuai consid',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nama Dokter/DPJP', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ref_spesialistik',
                'label'       => 'Referensi Spesialistik',
                'method'      => 'GET',
                'path'        => '/referensi/spesialistik',
                'description' => 'Pencarian data spesialistik',
                'params'      => [],
            ],
            [
                'key'         => 'ref_ruangrawat',
                'label'       => 'Referensi Ruang Rawat',
                'method'      => 'GET',
                'path'        => '/referensi/ruangrawat',
                'description' => 'Pencarian data ruang rawat',
                'params'      => [],
            ],
            // ===== SEP ENDPOINTS =====
            [
                'key'         => 'sep_insert',
                'label'       => 'Insert SEP',
                'method'      => 'POST',
                'path'        => '/SEP/insert',
                'description' => 'Insert SEP (Surat Bukti Jaminan Pelayanan)',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body SEP', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_update',
                'label'       => 'Update SEP',
                'method'      => 'POST',
                'path'        => '/SEP/update',
                'description' => 'Update SEP',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body SEP', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_delete',
                'label'       => 'Delete SEP',
                'method'      => 'DELETE',
                'path'        => '/SEP/delete',
                'description' => 'Hapus Data SEP',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body SEP', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_cari',
                'label'       => 'Cari SEP',
                'method'      => 'GET',
                'path'        => '/SEP/{parameter}',
                'description' => 'Melihat data detail SEP Peserta',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor SEP Peserta (19 digit)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_insert_20',
                'label'       => 'Insert SEP 2.0',
                'method'      => 'POST',
                'path'        => '/SEP/2.0/insert',
                'description' => 'Insert SEP versi 2.0',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body SEP 2.0', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_update_20',
                'label'       => 'Update SEP 2.0',
                'method'      => 'POST',
                'path'        => '/SEP/2.0/update',
                'description' => 'Update SEP versi 2.0',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body SEP 2.0', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_delete_20',
                'label'       => 'Delete SEP 2.0',
                'method'      => 'POST',
                'path'        => '/SEP/2.0/delete',
                'description' => 'Hapus SEP versi 2.0',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body SEP 2.0', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_internal',
                'label'       => 'Data SEP Internal',
                'method'      => 'GET',
                'path'        => '/SEP/Internal/{parameter}',
                'description' => 'Data SEP Internal',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor SEP (19 digit)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_internal_delete',
                'label'       => 'Hapus SEP Internal',
                'method'      => 'POST',
                'path'        => '/SEP/Internal/delete',
                'description' => 'Hapus SEP Internal',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_lastsep',
                'label'       => 'Cari SEP Terakhir by No Rujukan',
                'method'      => 'GET',
                'path'        => '/Rujukan/lastsep/norujukan/{parameter}',
                'description' => 'Melihat data detail SEP Terakhir Peserta Berdasarkan Nomor Rujukan',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Rujukan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_cari_by_kartu',
                'label'       => 'Cari SEP by No Kartu',
                'method'      => 'GET',
                'path'        => '/Peserta/nokartu/{parameter1}/tglSEP/{parameter2}',
                'description' => 'Pencarian data peserta BPJS Kesehatan',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Nomor Kartu Peserta', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tanggal SEP (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_cari_by_nik',
                'label'       => 'Cari SEP by NIK',
                'method'      => 'GET',
                'path'        => '/Peserta/nik/{parameter1}/tglSEP/{parameter2}',
                'description' => 'Pencarian data peserta berdasarkan NIK Kependudukan',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'NIK Peserta', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tanggal SEP (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_insert',
                'label'       => 'Insert Rujukan',
                'method'      => 'POST',
                'path'        => '/Rujukan/insert',
                'description' => 'Insert Rujukan',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body rujukan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_update',
                'label'       => 'Update Rujukan',
                'method'      => 'PUT',
                'path'        => '/Rujukan/update',
                'description' => 'Update Rujukan',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body rujukan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_delete',
                'label'       => 'Delete Rujukan',
                'method'      => 'DELETE',
                'path'        => '/Rujukan/delete',
                'description' => 'Hapus Data Rujukan',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body rujukan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_cari',
                'label'       => 'Cari Rujukan by No',
                'method'      => 'GET',
                'path'        => '/Rujukan/{parameter}',
                'description' => 'Pencarian data rujukan berdasarkan nomor rujukan',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Rujukan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_rs',
                'label'       => 'Cari Rujukan by No (RS)',
                'method'      => 'GET',
                'path'        => '/Rujukan/RS/{parameter}',
                'description' => 'Pencarian data rujukan dari rumah sakit berdasarkan nomor rujukan',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Rujukan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_peserta',
                'label'       => 'Cari Rujukan by No Kartu (PCare)',
                'method'      => 'GET',
                'path'        => '/Rujukan/Peserta/{parameter}',
                'description' => 'Pencarian data rujukan dari PCare berdasarkan nomor kartu',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Kartu', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_rs_peserta',
                'label'       => 'Cari Rujukan by No Kartu (RS)',
                'method'      => 'GET',
                'path'        => '/Rujukan/RS/Peserta/{parameter}',
                'description' => 'Pencarian data rujukan dari rumah sakit berdasarkan nomor kartu',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Kartu', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_list_peserta',
                'label'       => 'Cari Rujukan List by No Kartu',
                'method'      => 'GET',
                'path'        => '/Rujukan/List/Peserta/{parameter}',
                'description' => 'Pencarian data rujukan dari PCare berdasarkan nomor kartu (multi record)',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Kartu', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rujukan_rs_list_peserta',
                'label'       => 'Cari Rujukan List by No Kartu (RS)',
                'method'      => 'GET',
                'path'        => '/Rujukan/RS/List/Peserta/{parameter}',
                'description' => 'Pencarian data rujukan dari rumah sakit berdasarkan nomor kartu (multi record)',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Kartu', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rencana_kontrol',
                'label'       => 'Rencana Kontrol',
                'method'      => 'GET',
                'path'        => '/RencanaKontrol/nosep/{parameter}',
                'description' => 'Melihat data SEP untuk keperluan rencana kontrol',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor SEP Peserta', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rencana_kontrol_surat',
                'label'       => 'Data Nomor Surat Kontrol',
                'method'      => 'GET',
                'path'        => '/RencanaKontrol/noSuratKontrol/{parameter}',
                'description' => 'Melihat data SEP untuk keperluan rencana kontrol',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Surat Kontrol', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rencana_kontrol_list',
                'label'       => 'List Rencana Kontrol',
                'method'      => 'GET',
                'path'        => '/RencanaKontrol/ListRencanaKontrol/bulan/{parameter1}/tahun/{parameter2}/nokartu/{parameter3}/filter/{parameter4}',
                'description' => 'Data Rencana Kontrol By No Kartu',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Bulan (01-12)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tahun (yyyy)', 'default' => ''],
                    ['name' => 'parameter3', 'type' => 'text', 'placeholder' => 'Nomor Kartu', 'default' => ''],
                    ['name' => 'parameter4', 'type' => 'text', 'placeholder' => 'Filter (1: tanggal entri, 2: tanggal rencana kontrol)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rencana_kontrol_list_tanggal',
                'label'       => 'List Rencana Kontrol by Tanggal',
                'method'      => 'GET',
                'path'        => '/RencanaKontrol/ListRencanaKontrol/tglAwal/{parameter1}/tglAkhir/{parameter2}/filter/{parameter3}',
                'description' => 'Data Rencana Kontrol',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Tanggal Awal (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tanggal Akhir (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'parameter3', 'type' => 'text', 'placeholder' => 'Filter (1: tanggal entri, 2: tanggal rencana kontrol)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rencana_kontrol_spesialistik',
                'label'       => 'List Spesialistik',
                'method'      => 'GET',
                'path'        => '/RencanaKontrol/ListSpesialistik/JnsKontrol/{parameter1}/nomor/{parameter2}/TglRencanaKontrol/{parameter3}',
                'description' => 'Data Rencana Kontrol',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Jenis kontrol (1: SPRI, 2: Rencana Kontrol)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Nomor (kartu atau SEP)', 'default' => ''],
                    ['name' => 'parameter3', 'type' => 'text', 'placeholder' => 'Tanggal rencana kontrol (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_rencana_kontrol_dokter',
                'label'       => 'Jadwal Dokter',
                'method'      => 'GET',
                'path'        => '/RencanaKontrol/JadwalPraktekDokter/JnsKontrol/{parameter1}/KdPoli/{parameter2}/TglRencanaKontrol/{parameter3}',
                'description' => 'Data Rencana Kontrol',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Jenis kontrol (1: SPRI, 2: Rencana Kontrol)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Kode poli', 'default' => ''],
                    ['name' => 'parameter3', 'type' => 'text', 'placeholder' => 'Tanggal rencana kontrol (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_fingerprint',
                'label'       => 'Get Finger Print',
                'method'      => 'GET',
                'path'        => '/SEP/FingerPrint/Peserta/{parameter1}/TglPelayanan/{parameter2}',
                'description' => 'Get Finger Print',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Nomor Kartu', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tanggal Pelayanan (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_fingerprint_list',
                'label'       => 'Get List Finger Print',
                'method'      => 'GET',
                'path'        => '/SEP/FingerPrint/List/Peserta/TglPelayanan/{parameter}',
                'description' => 'Get List Finger Print',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Kartu/Tanggal (format: nomorkartu|tanggal)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_fingerprint_random',
                'label'       => 'Get Random Question',
                'method'      => 'GET',
                'path'        => '/SEP/FingerPrint/randomquestion/faskesterdaftar/nokapst/{parameter1}/tglsep/{parameter2}',
                'description' => 'Get Random Question',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'No Kapst', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tanggal SEP (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_fingerprint_answer',
                'label'       => 'Post Random Answer',
                'method'      => 'POST',
                'path'        => '/SEP/FingerPrint/randomanswer',
                'description' => 'Post Random Answer',
                'body'        => [
                    ['name' => 'noKartu', 'type' => 'text', 'placeholder' => 'Nomor Kartu', 'default' => ''],
                    ['name' => 'tglSep', 'type' => 'text', 'placeholder' => 'Tanggal SEP (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'jnsPel', 'type' => 'text', 'placeholder' => 'Jenis pelayanan (1=Inap, 2=Jalan)', 'default' => ''],
                    ['name' => 'jawaban', 'type' => 'text', 'placeholder' => 'Jawaban pertanyaan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_suplesi',
                'label'       => 'Potensi SEP Suplesi',
                'method'      => 'GET',
                'path'        => '/sep/JasaRaharja/Suplesi/{parameter1}/tglPelayanan/{parameter2}',
                'description' => 'Pencarian data potensi SEP Sebagai Suplesi Jasa Raharja',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Nomor Rujukan', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tanggal Pelayanan (yyyy-MM-dd)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_kll_induk',
                'label'       => 'SEP KLL Induk',
                'method'      => 'GET',
                'path'        => '/sep/KllInduk/List/{parameter}',
                'description' => 'Pencarian data SEP Induk Kecelakaan Lalu Lintas',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Rujukan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_pengajuan',
                'label'       => 'Pengajuan SEP',
                'method'      => 'POST',
                'path'        => '/Sep/pengajuanSEP',
                'description' => 'Pengajuan SEP',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body pengajuan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_aproval',
                'label'       => 'Aproval Pengajuan SEP',
                'method'      => 'POST',
                'path'        => '/Sep/aprovalSEP',
                'description' => 'Aproval Pengajuan SEP',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body aproval', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_persetujuan_list',
                'label'       => 'List Data Persetujuan SEP',
                'method'      => 'GET',
                'path'        => '/Sep/persetujuanSEP/list/bulan/{parameter1}/tahun/{parameter2}',
                'description' => 'Get List Data Persetujuan SEP',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Bulan (01-12)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tahun (yyyy)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_updtglplg',
                'label'       => 'Update Tgl Pulang SEP',
                'method'      => 'POST',
                'path'        => '/Sep/updtglplg',
                'description' => 'Update tanggal pulang SEP',
                'body'        => [
                    ['name' => 'noSep', 'type' => 'text', 'placeholder' => 'Nomor SEP', 'default' => ''],
                    ['name' => 'tglPlg', 'type' => 'text', 'placeholder' => 'Tanggal Pulang (yyyy-MM-dd hh:mm:ss)', 'default' => ''],
                    ['name' => 'ppkPelayanan', 'type' => 'text', 'placeholder' => 'PPK Pelayanan SEP', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_updtglplg_20',
                'label'       => 'Update Tgl Pulang SEP 2.0',
                'method'      => 'POST',
                'path'        => '/SEP/2.0/updtglplg',
                'description' => 'Update tanggal pulang SEP 2.0',
                'body'        => [
                    ['name' => 'noSep', 'type' => 'text', 'placeholder' => 'Nomor SEP', 'default' => ''],
                    ['name' => 'tglPulang', 'type' => 'text', 'placeholder' => 'Tanggal Pulang (yyyy-MM-dd)', 'default' => ''],
                    ['name' => 'noLPManual', 'type' => 'text', 'placeholder' => 'Diisi jika SEPnya adalah KLL', 'default' => ''],
                    ['name' => 'user', 'type' => 'text', 'placeholder' => 'User', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep_cbg',
                'label'       => 'SEP & Inacbg',
                'method'      => 'GET',
                'path'        => '/sep/cbg/{parameter}',
                'description' => 'Pencarian No.SEP untuk Aplikasi Inacbg 4.1',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor SEP (19 digit)', 'default' => ''],
                ],
            ],
        ],
    ],
    'antrean_rs' => [
        'label'       => 'Antrean RS',
        'icon'        => '🩺',
        'description' => 'Modul Antrean Rumah Sakit',
        'base_url'    => getBaseUrl('antrean_rs', $currentDomain, $isDevMode),
        'sub_modules' => [
            // WS BPJS Endpoints (diakses oleh RS)
            [
                'key'         => 'ref_poli',
                'label'       => 'Referensi Poli',
                'method'      => 'GET',
                'path'        => '/ref/poli',
                'description' => 'Melihat referensi poli yang ada pada Aplikasi HFIS',
                'params'      => [],
            ],
            [
                'key'         => 'ref_dokter',
                'label'       => 'Referensi Dokter',
                'method'      => 'GET',
                'path'        => '/ref/dokter',
                'description' => 'Melihat referensi dokter yang ada pada Aplikasi HFIS',
                'params'      => [],
            ],
            [
                'key'         => 'jadwal_dokter',
                'label'       => 'Referensi Jadwal Dokter',
                'method'      => 'GET',
                'path'        => '/jadwaldokter/kodepoli/{kodepoli}/tanggal/{tanggal}',
                'description' => 'Melihat referensi jadwal dokter yang ada pada Aplikasi HFIS',
                'params'      => [
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli (misal: ANA)', 'default' => ''],
                    ['name' => 'tanggal', 'type' => 'text', 'placeholder' => 'Tanggal (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                ],
            ],
            [
                'key'         => 'ref_poli_fp',
                'label'       => 'Referensi Poli Finger Print',
                'method'      => 'GET',
                'path'        => '/ref/poli/fp',
                'description' => 'Melihat referensi poli finger print',
                'params'      => [],
            ],
            [
                'key'         => 'ref_pasien_fp',
                'label'       => 'Referensi Pasien Finger Print',
                'method'      => 'GET',
                'path'        => '/ref/pasien/fp/identitas/{nik}/noidentitas/{noidentitas}',
                'description' => 'Melihat referensi pasien finger print',
                'params'      => [
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'NIK', 'default' => ''],
                    ['name' => 'noidentitas', 'type' => 'text', 'placeholder' => 'No Identitas', 'default' => ''],
                ],
            ],
            [
                'key'         => 'update_jadwal_dokter',
                'label'       => 'Update Jadwal Dokter',
                'method'      => 'POST',
                'path'        => '/jadwaldokter/updatejadwaldokter',
                'description' => 'Update jadwal dokter yang ada pada Aplikasi HFIS',
                'body'        => [
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli BPJS', 'default' => ''],
                    ['name' => 'kodesubspesialis', 'type' => 'text', 'placeholder' => 'Kode Subspesialis BPJS', 'default' => ''],
                    ['name' => 'kodedokter', 'type' => 'number', 'placeholder' => 'Kode Dokter BPJS', 'default' => ''],
                    ['name' => 'jadwal', 'type' => 'json', 'placeholder' => 'Array jadwal', 'default' => ''],
                ],
            ],
            [
                'key'         => 'tambah_antrean',
                'label'       => 'Tambah Antrean',
                'method'      => 'POST',
                'path'        => '/antrean/add',
                'description' => 'Menambah Antrean RS',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking Unik', 'default' => ''],
                    ['name' => 'jenispasien', 'type' => 'text', 'placeholder' => 'JKN / NON JKN', 'default' => 'JKN'],
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu BPJS (kosongkan jika NON JKN)', 'default' => ''],
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'NIK Pasien', 'default' => ''],
                    ['name' => 'nohp', 'type' => 'text', 'placeholder' => 'No HP Pasien', 'default' => ''],
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Subspesialis BPJS', 'default' => ''],
                    ['name' => 'namapoli', 'type' => 'text', 'placeholder' => 'Nama Poli', 'default' => ''],
                    ['name' => 'pasienbaru', 'type' => 'number', 'placeholder' => '1 (Ya) / 0 (Tidak)', 'default' => '1'],
                    ['name' => 'norm', 'type' => 'text', 'placeholder' => 'No Rekam Medis', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal Periksa (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                    ['name' => 'kodedokter', 'type' => 'number', 'placeholder' => 'Kode Dokter BPJS', 'default' => ''],
                    ['name' => 'namadokter', 'type' => 'text', 'placeholder' => 'Nama Dokter', 'default' => ''],
                    ['name' => 'jampraktek', 'type' => 'text', 'placeholder' => 'Jam Praktek Dokter', 'default' => ''],
                    ['name' => 'jeniskunjungan', 'type' => 'number', 'placeholder' => '1 (Rujukan FKTP), 2 (Rujukan Internal), 3 (Kontrol), 4 (Rujukan Antar RS)', 'default' => '1'],
                    ['name' => 'nomorreferensi', 'type' => 'text', 'placeholder' => 'No Rujukan/Kontrol JKN', 'default' => ''],
                    ['name' => 'nomorantrean', 'type' => 'text', 'placeholder' => 'Nomor Antrean', 'default' => ''],
                    ['name' => 'angkaantrean', 'type' => 'number', 'placeholder' => 'Angka Antrean', 'default' => '1'],
                    ['name' => 'estimasidilayani', 'type' => 'number', 'placeholder' => 'Estimasi Dilayani (milliseconds)', 'default' => ''],
                    ['name' => 'sisakuotajkn', 'type' => 'number', 'placeholder' => 'Sisa Kuota JKN', 'default' => ''],
                    ['name' => 'kuotajkn', 'type' => 'number', 'placeholder' => 'Kuota JKN', 'default' => ''],
                    ['name' => 'sisakuotanonjkn', 'type' => 'number', 'placeholder' => 'Sisa Kuota Non JKN', 'default' => ''],
                    ['name' => 'kuotanonjkn', 'type' => 'number', 'placeholder' => 'Kuota Non JKN', 'default' => ''],
                    ['name' => 'keterangan', 'type' => 'text', 'placeholder' => 'Informasi untuk Pasien', 'default' => ''],
                ],
            ],
            [
                'key'         => 'tambah_antrean_farmasi',
                'label'       => 'Tambah Antrean Farmasi',
                'method'      => 'POST',
                'path'        => '/antrean/farmasi/add',
                'description' => 'Menambah Antrean Farmasi RS',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                    ['name' => 'jenisresep', 'type' => 'text', 'placeholder' => 'racikan / non racikan', 'default' => 'racikan'],
                    ['name' => 'nomorantrean', 'type' => 'number', 'placeholder' => 'Nomor Antrean', 'default' => '1'],
                    ['name' => 'keterangan', 'type' => 'text', 'placeholder' => 'Keterangan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'update_waktu_antrean',
                'label'       => 'Update Waktu Antrean',
                'method'      => 'POST',
                'path'        => '/antrean/updatewaktu',
                'description' => 'Mengirimkan waktu tunggu/waktu layan',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                    ['name' => 'taskid', 'type' => 'number', 'placeholder' => '1-99 (lihat catatan di dokumentasi)', 'default' => '1'],
                    ['name' => 'waktu', 'type' => 'number', 'placeholder' => 'Waktu dalam milliseconds', 'default' => ''],
                ],
            ],
            [
                'key'         => 'batal_antrean',
                'label'       => 'Batal Antrean',
                'method'      => 'POST',
                'path'        => '/antrean/batal',
                'description' => 'Membatalkan antrean pasien',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                    ['name' => 'keterangan', 'type' => 'text', 'placeholder' => 'Alasan Pembatalan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'list_waktu_task',
                'label'       => 'List Waktu Task Id',
                'method'      => 'POST',
                'path'        => '/antrean/getlisttask',
                'description' => 'Meliat waktu task id yang telah dikirim ke BPJS',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                ],
            ],
            [
                'key'         => 'dashboard_per_tanggal',
                'label'       => 'Dashboard Per Tanggal',
                'method'      => 'GET',
                'path'        => '/dashboard/waktutunggu/tanggal/{tanggal}/waktu/{waktu}',
                'description' => 'Dashboard waktu per tanggal',
                'params'      => [
                    ['name' => 'tanggal', 'type' => 'text', 'placeholder' => 'Tanggal (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                    ['name' => 'waktu', 'type' => 'text', 'placeholder' => 'rs atau server', 'default' => 'rs'],
                ],
            ],
            [
                'key'         => 'dashboard_per_bulan',
                'label'       => 'Dashboard Per Bulan',
                'method'      => 'GET',
                'path'        => '/dashboard/waktutunggu/bulan/{bulan}/tahun/{tahun}/waktu/{waktu}',
                'description' => 'Dashboard waktu per bulan',
                'params'      => [
                    ['name' => 'bulan', 'type' => 'text', 'placeholder' => 'Bulan (01-12)', 'default' => ''],
                    ['name' => 'tahun', 'type' => 'text', 'placeholder' => 'Tahun (YYYY)', 'default' => ''],
                    ['name' => 'waktu', 'type' => 'text', 'placeholder' => 'rs atau server', 'default' => 'rs'],
                ],
            ],
            [
                'key'         => 'antrean_per_tanggal',
                'label'       => 'Antrean Per Tanggal',
                'method'      => 'GET',
                'path'        => '/antrean/pendaftaran/tanggal/{tanggal}',
                'description' => 'Meliat pendaftaran antrean per tanggal',
                'params'      => [
                    ['name' => 'tanggal', 'type' => 'text', 'placeholder' => 'Tanggal (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                ],
            ],
            [
                'key'         => 'antrean_per_kode_booking',
                'label'       => 'Antrean Per Kode Booking',
                'method'      => 'GET',
                'path'        => '/antrean/pendaftaran/kodebooking/{kodebooking}',
                'description' => 'Meliat pendaftaran antrean per kode booking',
                'params'      => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                ],
            ],
            [
                'key'         => 'antrean_belum_dilayani',
                'label'       => 'Antrean Belum Dilayani',
                'method'      => 'GET',
                'path'        => '/antrean/pendaftaran/aktif',
                'description' => 'Meliat pendaftaran antrean belum dilayani',
                'params'      => [],
            ],
            [
                'key'         => 'antrean_belum_dilayani_detail',
                'label'       => 'Antrean Belum Dilayani Detail',
                'method'      => 'GET',
                'path'        => '/antrean/pendaftaran/kodepoli/{kodepoli}/kodedokter/{kodedokter}/hari/{hari}/jampraktek/{jampraktek}',
                'description' => 'Meliat pendaftaran antrean belum dilayani per poli per dokter per hari per jam praktek',
                'params'      => [
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                    ['name' => 'kodedokter', 'type' => 'number', 'placeholder' => 'Kode Dokter', 'default' => ''],
                    ['name' => 'hari', 'type' => 'number', 'placeholder' => '1-8 (1=Mon, 2=Tue, ..., 8=National Holiday)', 'default' => ''],
                    ['name' => 'jampraktek', 'type' => 'text', 'placeholder' => 'Jam Praktek', 'default' => ''],
                ],
            ],
            // WS RS Endpoints (diakses oleh Mobile JKN)
            [
                'key'         => 'token_rs',
                'label'       => 'Token',
                'method'      => 'GET',
                'path'        => '/rs/token',
                'description' => 'Membuat token',
                'headers'     => ['x-username', 'x-password'],
            ],
            [
                'key'         => 'status_antrean_rs',
                'label'       => 'Status Antrean',
                'method'      => 'POST',
                'path'        => '/rs/status',
                'description' => 'Menampilkan status antrean per poli (untuk perencanaan kedatangan pasien)',
                'body'        => [
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Subspesialis BPJS', 'default' => ''],
                    ['name' => 'kodedokter', 'type' => 'number', 'placeholder' => 'Kode Dokter BPJS', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal Rencana Berobat (YYYY-MM-DD)', 'default' => ''],
                    ['name' => 'jampraktek', 'type' => 'text', 'placeholder' => 'Jam Praktek Dokter', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ambil_antrean_rs',
                'label'       => 'Ambil Antrean',
                'method'      => 'POST',
                'path'        => '/rs/antrean',
                'description' => 'Mengambil antrean',
                'body'        => [
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu BPJS (kosongkan jika NON JKN)', 'default' => ''],
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'NIK Pasien', 'default' => ''],
                    ['name' => 'nohp', 'type' => 'text', 'placeholder' => 'No HP Pasien', 'default' => ''],
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Subspesialis BPJS', 'default' => ''],
                    ['name' => 'norm', 'type' => 'text', 'placeholder' => 'No Rekam Medis', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal Periksa (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                    ['name' => 'kodedokter', 'type' => 'number', 'placeholder' => 'Kode Dokter BPJS', 'default' => ''],
                    ['name' => 'jampraktek', 'type' => 'text', 'placeholder' => 'Jam Praktek', 'default' => ''],
                    ['name' => 'jeniskunjungan', 'type' => 'number', 'placeholder' => '1-4', 'default' => '1'],
                    ['name' => 'nomorreferensi', 'type' => 'text', 'placeholder' => 'No Rujukan (kosongkan jika NON JKN)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sisa_antrean_rs',
                'label'       => 'Sisa Antrean',
                'method'      => 'POST',
                'path'        => '/rs/sisaantrean',
                'description' => 'Meliat sisa antrean di hari H pelayanan',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                ],
            ],
            [
                'key'         => 'batal_antrean_rs',
                'label'       => 'Batal Antrean',
                'method'      => 'POST',
                'path'        => '/rs/batal',
                'description' => 'Membatalkan antrean pasien',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                    ['name' => 'keterangan', 'type' => 'text', 'placeholder' => 'Alasan Pembatalan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'checkin_antrean_rs',
                'label'       => 'Check In',
                'method'      => 'POST',
                'path'        => '/rs/checkin',
                'description' => 'Memastikan pasien sudah datang di RS',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                    ['name' => 'waktu', 'type' => 'number', 'placeholder' => 'Waktu Check-in (milliseconds)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'info_pasien_baru_rs',
                'label'       => 'Info Pasien Baru',
                'method'      => 'POST',
                'path'        => '/rs/pasienbaru',
                'description' => 'Informasi identitas pasien baru yang belum punya rekam medis',
                'body'        => [
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu Pasien JKN', 'default' => ''],
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'NIK Pasien', 'default' => ''],
                    ['name' => 'nomorkk', 'type' => 'text', 'placeholder' => 'No KK Pasien', 'default' => ''],
                    ['name' => 'nama', 'type' => 'text', 'placeholder' => 'Nama Pasien', 'default' => ''],
                    ['name' => 'jeniskelamin', 'type' => 'text', 'placeholder' => 'Jenis Kelamin', 'default' => ''],
                    ['name' => 'tanggallahir', 'type' => 'text', 'placeholder' => 'Tanggal Lahir (YYYY-MM-DD)', 'default' => ''],
                    ['name' => 'nohp', 'type' => 'text', 'placeholder' => 'No HP Pasien', 'default' => ''],
                    ['name' => 'alamat', 'type' => 'text', 'placeholder' => 'Alamat Pasien', 'default' => ''],
                    ['name' => 'kodeprop', 'type' => 'text', 'placeholder' => 'Kode Propinsi BPJS', 'default' => ''],
                    ['name' => 'namaprop', 'type' => 'text', 'placeholder' => 'Nama Propinsi', 'default' => ''],
                    ['name' => 'kodedati2', 'type' => 'text', 'placeholder' => 'Kode Kota/Kab BPJS', 'default' => ''],
                    ['name' => 'namadati2', 'type' => 'text', 'placeholder' => 'Nama Kota/Kab', 'default' => ''],
                    ['name' => 'kodekec', 'type' => 'text', 'placeholder' => 'Kode Kecamatan BPJS', 'default' => ''],
                    ['name' => 'namakec', 'type' => 'text', 'placeholder' => 'Nama Kecamatan', 'default' => ''],
                    ['name' => 'kodekel', 'type' => 'text', 'placeholder' => 'Kode Kelurahan BPJS', 'default' => ''],
                    ['name' => 'namakel', 'type' => 'text', 'placeholder' => 'Nama Kelurahan', 'default' => ''],
                    ['name' => 'rw', 'type' => 'text', 'placeholder' => 'No RT', 'default' => ''],
                    ['name' => 'rt', 'type' => 'text', 'placeholder' => 'No RW', 'default' => ''],
                ],
            ],
            [
                'key'         => 'jadwal_operasi_rs',
                'label'       => 'Jadwal Operasi RS',
                'method'      => 'POST',
                'path'        => '/rs/jadwaloperasi',
                'description' => 'Informasi jadwal operasi di rumah sakit',
                'body'        => [
                    ['name' => 'tanggalawal', 'type' => 'text', 'placeholder' => 'Tanggal Awal (YYYY-MM-DD)', 'default' => ''],
                    ['name' => 'tanggalakhir', 'type' => 'text', 'placeholder' => 'Tanggal Akhir (YYYY-MM-DD)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'jadwal_operasi_pasien_rs',
                'label'       => 'Jadwal Operasi Pasien',
                'method'      => 'POST',
                'path'        => '/rs/jadwaloperasipasien',
                'description' => 'Informasi jadwal operasi per pasien',
                'body'        => [
                    ['name' => 'nopeserta', 'type' => 'text', 'placeholder' => 'No Kartu Pasien JKN', 'default' => ''],
                ],
            ],
            [
                'key'         => 'ambil_antrean_farmasi_rs',
                'label'       => 'Ambil Antrean Farmasi',
                'method'      => 'POST',
                'path'        => '/rs/farmasi/ambil',
                'description' => 'Mengambil antrean farmasi',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                ],
            ],
            [
                'key'         => 'status_antrean_farmasi_rs',
                'label'       => 'Status Antrean Farmasi',
                'method'      => 'POST',
                'path'        => '/rs/farmasi/status',
                'description' => 'Mengetahui status antrean farmasi',
                'body'        => [
                    ['name' => 'kodebooking', 'type' => 'text', 'placeholder' => 'Kode Booking', 'default' => ''],
                ],
            ],
        ],
    ],
    'apotek' => [
        'label'       => 'Apotek',
        'icon'        => '💊',
        'description' => 'Modul Apotek – Data Obat & Farmasi',
        'base_url'    => getBaseUrl('apotek', $currentDomain, $isDevMode),
        'sub_modules' => [
            [
                'key'         => 'dpho',
                'label'       => 'DPHO',
                'method'      => 'GET',
                'path'        => '/referensi/dpho',
                'description' => 'Daftar Obat DPHO',
                'params'      => [],
            ],
            [
                'key'         => 'poli_obat',
                'label'       => 'Poli',
                'method'      => 'GET',
                'path'        => '/referensi/poli/{parameter}',
                'description' => 'Daftar Obat Poli - Parameter: Kode atau Nama Poli',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Kode atau Nama Poli', 'default' => ''],
                ],
            ],
            [
                'key'         => 'faskes',
                'label'       => 'Fasilitas Kesehatan',
                'method'      => 'GET',
                'path'        => '/referensi/ppk/{parameter1}/{parameter2}',
                'description' => 'Pencarian data fasilitas kesehatan - Parameter1: Jenis Faskes (1. Faskes 1, 2. Faskes 2/RS), Parameter2: nama faskes',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Jenis Faskes (1 atau 2)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Nama Faskes', 'default' => ''],
                ],
            ],
            [
                'key'         => 'setting_ppk',
                'label'       => 'Setting Apotek',
                'method'      => 'GET',
                'path'        => '/referensi/settingppk/read/{parameter}',
                'description' => 'Pencarian Setting Apotek - Parameter: Kode Apotek',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Kode Apotek', 'default' => ''],
                ],
            ],
            [
                'key'         => 'spesialistik',
                'label'       => 'Spesialistik',
                'method'      => 'GET',
                'path'        => '/referensi/spesialistik',
                'description' => 'Pencarian data spesialistik',
                'params'      => [],
            ],
            [
                'key'         => 'obat',
                'label'       => 'Obat',
                'method'      => 'GET',
                'path'        => '/referensi/obat/{parameter1}/{parameter2}/{parameter3}',
                'description' => 'Pencarian obat - Parameter1: Kode Jenis Obat, Parameter2: Tgl Resep, Parameter3: Filter Pencarian',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Kode Jenis Obat', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tgl Resep (YYYY-MM-DD)', 'default' => ''],
                    ['name' => 'parameter3', 'type' => 'text', 'placeholder' => 'Filter Pencarian', 'default' => ''],
                ],
            ],
            [
                'key'         => 'obat_non_racikan',
                'label'       => 'Penyimpanan Obat Non Racikan',
                'method'      => 'POST',
                'path'        => '/obatnonracikan/v3/insert',
                'description' => 'Simpan Obat Non Racikan',
                'body'        => [
                    ['name' => 'NOSJP', 'type' => 'text', 'placeholder' => 'No SJP', 'default' => ''],
                    ['name' => 'NORESEP', 'type' => 'text', 'placeholder' => 'No Resep', 'default' => ''],
                    ['name' => 'KDOBT', 'type' => 'text', 'placeholder' => 'Kode Obat', 'default' => ''],
                    ['name' => 'NMOBAT', 'type' => 'text', 'placeholder' => 'Nama Obat', 'default' => ''],
                    ['name' => 'SIGNA1OBT', 'type' => 'number', 'placeholder' => 'Signa 1', 'default' => ''],
                    ['name' => 'SIGNA2OBT', 'type' => 'number', 'placeholder' => 'Signa 2', 'default' => ''],
                    ['name' => 'JMLOBT', 'type' => 'number', 'placeholder' => 'Jumlah Obat', 'default' => ''],
                    ['name' => 'JHO', 'type' => 'number', 'placeholder' => 'Jumlah Hari Obat', 'default' => ''],
                    ['name' => 'CatKhsObt', 'type' => 'text', 'placeholder' => 'Catatan Khusus Obat', 'default' => ''],
                ],
            ],
            [
                'key'         => 'obat_racikan',
                'label'       => 'Penyimpanan Obat Racikan',
                'method'      => 'POST',
                'path'        => '/obatracikan/v3/insert',
                'description' => 'Simpan Obat Racikan',
                'body'        => [
                    ['name' => 'NOSJP', 'type' => 'text', 'placeholder' => 'No SJP', 'default' => ''],
                    ['name' => 'NORESEP', 'type' => 'text', 'placeholder' => 'No Resep', 'default' => ''],
                    ['name' => 'JNSROBT', 'type' => 'text', 'placeholder' => 'Jenis Racikan (R.01)', 'default' => ''],
                    ['name' => 'KDOBT', 'type' => 'text', 'placeholder' => 'Kode Obat', 'default' => ''],
                    ['name' => 'NMOBAT', 'type' => 'text', 'placeholder' => 'Nama Obat', 'default' => ''],
                    ['name' => 'SIGNA1OBT', 'type' => 'number', 'placeholder' => 'Signa 1', 'default' => ''],
                    ['name' => 'SIGNA2OBT', 'type' => 'number', 'placeholder' => 'Signa 2', 'default' => ''],
                    ['name' => 'PERMINTAAN', 'type' => 'number', 'placeholder' => 'Permintaan', 'default' => ''],
                    ['name' => 'JMLOBT', 'type' => 'number', 'placeholder' => 'Jumlah Obat', 'default' => ''],
                    ['name' => 'JHO', 'type' => 'number', 'placeholder' => 'Jumlah Hari Obat', 'default' => ''],
                    ['name' => 'CatKhsObt', 'type' => 'text', 'placeholder' => 'Catatan Khusus Obat', 'default' => ''],
                ],
            ],
            [
                'key'         => 'update_stok_obat',
                'label'       => 'Update Stok Obat',
                'method'      => 'POST',
                'path'        => '/UpdateStokObat/updatestok',
                'description' => 'Menerima data stok obat dari sistem informasi apotek',
                'body'        => [
                    ['name' => 'KDOBAT', 'type' => 'text', 'placeholder' => 'Kode Obat', 'default' => ''],
                    ['name' => 'STOK', 'type' => 'number', 'placeholder' => 'Stok', 'default' => ''],
                ],
            ],
            [
                'key'         => 'hapus_pelayanan_obat',
                'label'       => 'Hapus Pelayanan Obat',
                'method'      => 'DELETE',
                'path'        => '/pelayanan/obat/hapus/',
                'description' => 'Hapus Pelayanan Obat',
                'body'        => [
                    ['name' => 'nosepapotek', 'type' => 'text', 'placeholder' => 'No SEP Apotek', 'default' => ''],
                    ['name' => 'noresep', 'type' => 'text', 'placeholder' => 'No Resep', 'default' => ''],
                    ['name' => 'kodeobat', 'type' => 'text', 'placeholder' => 'Kode Obat', 'default' => ''],
                    ['name' => 'tipeobat', 'type' => 'text', 'placeholder' => 'Tipe Obat (N/P)', 'default' => 'N'],
                ],
            ],
            [
                'key'         => 'daftar_pelayanan_obat',
                'label'       => 'Daftar Pelayanan Obat',
                'method'      => 'GET',
                'path'        => '/obat/daftar/{parameter}',
                'description' => 'Daftar Pelayanan Obat - Parameter: Nomor Kunjungan/SEP',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Kunjungan/SEP', 'default' => ''],
                ],
            ],
            [
                'key'         => 'riwayat_pelayanan_obat',
                'label'       => 'Riwayat Pelayanan Obat',
                'method'      => 'GET',
                'path'        => '/riwayatobat/{parameter1}/{parameter2}/{parameter3}',
                'description' => 'Riwayat Pelayanan Obat - Parameter1: Tgl Awal, Parameter2: Tgl Akhir, Parameter3: NoKartu',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Tgl Awal (YYYY-MM-DD)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tgl Akhir (YYYY-MM-DD)', 'default' => ''],
                    ['name' => 'parameter3', 'type' => 'text', 'placeholder' => 'No Kartu', 'default' => ''],
                ],
            ],
            [
                'key'         => 'simpan_resep',
                'label'       => 'Simpan Resep',
                'method'      => 'POST',
                'path'        => '/sjpresep/v3/insert',
                'description' => 'Simpan Resep',
                'body'        => [
                    ['name' => 'TGLSJP', 'type' => 'text', 'placeholder' => 'Tgl SJP (YYYY-MM-DD HH:MM:SS)', 'default' => ''],
                    ['name' => 'REFASALSJP', 'type' => 'text', 'placeholder' => 'Ref Asal SJP', 'default' => ''],
                    ['name' => 'POLIRSP', 'type' => 'text', 'placeholder' => 'Poli RS', 'default' => ''],
                    ['name' => 'KDJNSOBAT', 'type' => 'text', 'placeholder' => 'Jns Obat (1. PRB, 2. Kronis, 3. Kemoterapi)', 'default' => ''],
                    ['name' => 'NORESEP', 'type' => 'text', 'placeholder' => 'No Resep', 'default' => ''],
                    ['name' => 'IDUSERSJP', 'type' => 'text', 'placeholder' => 'ID User SJP', 'default' => ''],
                    ['name' => 'TGLRSP', 'type' => 'text', 'placeholder' => 'Tgl RSP (YYYY-MM-DD HH:MM:SS)', 'default' => ''],
                    ['name' => 'TGLPELRSP', 'type' => 'text', 'placeholder' => 'Tgl Pelayanan RSP (YYYY-MM-DD HH:MM:SS)', 'default' => ''],
                    ['name' => 'KdDokter', 'type' => 'text', 'placeholder' => 'Kode Dokter', 'default' => ''],
                    ['name' => 'iterasi', 'type' => 'text', 'placeholder' => 'Iterasi (0. Non Iterasi, 1. Iterasi)', 'default' => '0'],
                ],
            ],
            [
                'key'         => 'hapus_resep',
                'label'       => 'Hapus Resep',
                'method'      => 'DELETE',
                'path'        => '/hapusresep',
                'description' => 'Hapus Resep',
                'body'        => [
                    ['name' => 'nosjp', 'type' => 'text', 'placeholder' => 'No SJP', 'default' => ''],
                    ['name' => 'refasalsjp', 'type' => 'text', 'placeholder' => 'Ref Asal SJP', 'default' => ''],
                    ['name' => 'noresep', 'type' => 'text', 'placeholder' => 'No Resep', 'default' => ''],
                ],
            ],
            [
                'key'         => 'daftar_resep',
                'label'       => 'Daftar Resep',
                'method'      => 'POST',
                'path'        => '/daftarresep',
                'description' => 'Daftar Resep',
                'body'        => [
                    ['name' => 'kdppk', 'type' => 'text', 'placeholder' => 'Kode PPK', 'default' => ''],
                    ['name' => 'KdJnsObat', 'type' => 'text', 'placeholder' => 'Kode Jenis Obat', 'default' => ''],
                    ['name' => 'JnsTgl', 'type' => 'text', 'placeholder' => 'Jns Tgl (TGLPELSJP/TGLRSP)', 'default' => ''],
                    ['name' => 'TglMulai', 'type' => 'text', 'placeholder' => 'Tgl Mulai (YYYY-MM-DD HH:MM:SS)', 'default' => ''],
                    ['name' => 'TglAkhir', 'type' => 'text', 'placeholder' => 'Tgl Akhir (YYYY-MM-DD HH:MM:SS)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'sep',
                'label'       => 'Cari No Kunjungan/SEP',
                'method'      => 'GET',
                'path'        => '/sep/{parameter}',
                'description' => 'Data No Kunjungan/SEP - Parameter: Nomor Kunjungan/SEP (19 digit)',
                'params'      => [
                    ['name' => 'parameter', 'type' => 'text', 'placeholder' => 'Nomor Kunjungan/SEP (19 digit)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'monitoring_klaim',
                'label'       => 'Data Klaim',
                'method'      => 'GET',
                'path'        => '/monitoring/klaim/{parameter1}/{parameter2}/{parameter3}/{parameter4}',
                'description' => 'Data Klaim - Parameter1: Bulan, Parameter2: Tahun, Parameter3: Jenis Obat (0. Semua, 1. PRB, 2. Kronis, 3. Kemoterapi), Parameter4: Status (1. Belum diverifikasi, 2. Sudah Verifikasi)',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Bulan (01-12)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Tahun (YYYY)', 'default' => ''],
                    ['name' => 'parameter3', 'type' => 'text', 'placeholder' => 'Jenis Obat (0/1/2/3)', 'default' => ''],
                    ['name' => 'parameter4', 'type' => 'text', 'placeholder' => 'Status (1/2)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'rekap_peserta_prb',
                'label'       => 'Rekap Peserta PRB',
                'method'      => 'GET',
                'path'        => '/Prb/rekappeserta/tahun/{parameter1}/bulan/{parameter2}',
                'description' => 'Rekap peserta PRB - Parameter1: Tahun, Parameter2: Bulan',
                'params'      => [
                    ['name' => 'parameter1', 'type' => 'text', 'placeholder' => 'Tahun (YYYY)', 'default' => ''],
                    ['name' => 'parameter2', 'type' => 'text', 'placeholder' => 'Bulan (01-12)', 'default' => ''],
                ],
            ],
        ],
    ],
    'pcare' => [
        'label'       => 'PCare',
        'icon'        => '🏪',
        'description' => 'Modul PCare – Pelayanan Kesehatan Tingkat Pertama',
        'base_url'    => getBaseUrl('pcare', $currentDomain, $isDevMode),
        'sub_modules' => [
            // Diagnosa
            [
                'key'         => 'diagnosa',
                'label'       => 'Diagnosa',
                'method'      => 'GET',
                'path'        => '/diagnosa/{param1}/{param2}/{param3}',
                'description' => 'Get Data Diagnosa - Parameter 1: Kode atau nama diagnosa, Parameter 2: Row data awal, Parameter 3: Limit data',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Kode atau nama diagnosa', 'default' => ''],
                    ['name' => 'param2', 'type' => 'number', 'placeholder' => 'Row data awal (default: 1)', 'default' => '1'],
                    ['name' => 'param3', 'type' => 'number', 'placeholder' => 'Limit jumlah data', 'default' => '10'],
                ],
            ],
            // Dokter
            [
                'key'         => 'dokter',
                'label'       => 'Dokter',
                'method'      => 'GET',
                'path'        => '/dokter/{param1}/{param2}',
                'description' => 'Get Data Dokter - Parameter 1: Row data awal, Parameter 2: Limit jumlah data',
                'params'      => [
                    ['name' => 'param1', 'type' => 'number', 'placeholder' => 'Row data awal (default: 1)', 'default' => '1'],
                    ['name' => 'param2', 'type' => 'number', 'placeholder' => 'Limit jumlah data (default: 10)', 'default' => '10'],
                ],
            ],
            // Kelompok
            [
                'key'         => 'kelompok_club',
                'label'       => 'Club Prolanis',
                'method'      => 'GET',
                'path'        => '/kelompok/club/{param}',
                'description' => 'Get Data Club Prolanis - Parameter: Kode Jenis Kelompok (01: Diabetes Melitus, 02: Hipertensi)',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Kode Jenis Kelompok (01/02)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'kelompok_kegiatan',
                'label'       => 'Kegiatan Kelompok',
                'method'      => 'GET',
                'path'        => '/kelompok/kegiatan/{param}',
                'description' => 'Get Data Kegiatan Kelompok - Parameter: Bulan (dd-mm-yyyy)',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Bulan (dd-mm-yyyy)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'kelompok_peserta',
                'label'       => 'Peserta Kegiatan Kelompok',
                'method'      => 'GET',
                'path'        => '/kelompok/peserta/{param}',
                'description' => 'Get Data Peserta Kegiatan Kelompok - Parameter: eduId',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'eduId', 'default' => ''],
                ],
            ],
            // Kunjungan
            [
                'key'         => 'kunjungan_rujukan',
                'label'       => 'Rujukan Kunjungan',
                'method'      => 'GET',
                'path'        => '/kunjungan/rujukan/{param}',
                'description' => 'Get Data Rujukan - Parameter: Nomor Kunjungan',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nomor Kunjungan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'kunjungan_riwayat',
                'label'       => 'Riwayat Kunjungan',
                'method'      => 'GET',
                'path'        => '/kunjungan/peserta/{param}',
                'description' => 'Get Data Riwayat Kunjungan - Parameter: Nomor kartu peserta',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nomor kartu peserta', 'default' => ''],
                ],
            ],
            [
                'key'         => 'kunjungan_add',
                'label'       => 'Add Kunjungan',
                'method'      => 'POST',
                'path'        => '/kunjungan',
                'description' => 'Add Data Kunjungan',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body kunjungan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'kunjungan_edit',
                'label'       => 'Edit Kunjungan',
                'method'      => 'PUT',
                'path'        => '/kunjungan',
                'description' => 'Edit Data Kunjungan',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body kunjungan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'kunjungan_delete',
                'label'       => 'Delete Kunjungan',
                'method'      => 'DELETE',
                'path'        => '/kunjungan/{param}',
                'description' => 'Delete Data Kunjungan - Parameter: Nomor Kunjungan',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nomor Kunjungan', 'default' => ''],
                ],
            ],
            // MCU
            [
                'key'         => 'mcu',
                'label'       => 'MCU',
                'method'      => 'GET',
                'path'        => '/MCU/kunjungan/{param}',
                'description' => 'Get Data MCU - Parameter: Nomor Kunjungan',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nomor Kunjungan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'mcu_add',
                'label'       => 'Add MCU',
                'method'      => 'POST',
                'path'        => '/MCU',
                'description' => 'Add Data MCU',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body MCU', 'default' => ''],
                ],
            ],
            [
                'key'         => 'mcu_edit',
                'label'       => 'Edit MCU',
                'method'      => 'PUT',
                'path'        => '/MCU',
                'description' => 'Edit Data MCU',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body MCU', 'default' => ''],
                ],
            ],
            [
                'key'         => 'mcu_delete',
                'label'       => 'Delete MCU',
                'method'      => 'DELETE',
                'path'        => '/MCU/{param1}/kunjungan/{param2}',
                'description' => 'Delete Data MCU - Parameter 1: Kode MCU, Parameter 2: Nomor Kunjungan',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Kode MCU', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Nomor Kunjungan', 'default' => ''],
                ],
            ],
            // Obat
            [
                'key'         => 'obat_dpho',
                'label'       => 'DPHO',
                'method'      => 'GET',
                'path'        => '/obat/dpho/{param1}/{param2}/{param3}',
                'description' => 'Get Data DPHO - Parameter 1: Kode atau nama DPHO, Parameter 2: Row data awal, Parameter 3: Limit data',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Kode atau nama DPHO', 'default' => ''],
                    ['name' => 'param2', 'type' => 'number', 'placeholder' => 'Row data awal (default: 1)', 'default' => '1'],
                    ['name' => 'param3', 'type' => 'number', 'placeholder' => 'Limit jumlah data (default: 10)', 'default' => '10'],
                ],
            ],
            [
                'key'         => 'obat_dpho_new',
                'label'       => 'DPHO by KDPPK',
                'method'      => 'GET',
                'path'        => '/dpho/kdppk/{param1}/{param2}/{param3}/{param4}',
                'description' => 'Get Stock obat by apotek - Parameter 1: Kode PPK, Parameter 2: Nama Obat, Parameter 3: Row awal, Parameter 4: Limit',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Kode PPK Apotek', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Nama Obat', 'default' => ''],
                    ['name' => 'param3', 'type' => 'number', 'placeholder' => 'Row data awal (default: 1)', 'default' => '1'],
                    ['name' => 'param4', 'type' => 'number', 'placeholder' => 'Limit (default: 10)', 'default' => '10'],
                ],
            ],
            [
                'key'         => 'obat_by_kunjungan',
                'label'       => 'Obat by Kunjungan',
                'method'      => 'GET',
                'path'        => '/obat/kunjungan/{param}',
                'description' => 'Get Data Obat by Kunjungan - Parameter: Nomor Kunjungan',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nomor Kunjungan', 'default' => ''],
                ],
            ],
            [
                'key'         => 'obat_add',
                'label'       => 'Add Obat',
                'method'      => 'POST',
                'path'        => '/obat/kunjungan',
                'description' => 'Add Data Obat',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body obat', 'default' => ''],
                ],
            ],
            [
                'key'         => 'obat_delete',
                'label'       => 'Delete Obat',
                'method'      => 'DELETE',
                'path'        => '/obat/{param1}/kunjungan/{param2}',
                'description' => 'Delete Data Obat - Parameter 1: kodeObatSK, Parameter 2: Nomor Kunjungan',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'kodeObatSK', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Nomor Kunjungan', 'default' => ''],
                ],
            ],
            // Pendaftaran
            [
                'key'         => 'pendaftaran_no_urut',
                'label'       => 'Pendaftaran by No Urut',
                'method'      => 'GET',
                'path'        => '/pendaftaran/noUrut/{param1}/tglDaftar/{param2}',
                'description' => 'Get Data Pendaftaran by Nomor Urut - Parameter 1: Nomor Urut, Parameter 2: Tanggal Pendaftaran',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Nomor Urut Pendaftaran', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Tanggal Pendaftaran (dd-mm-yyyy)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'pendaftaran_provider',
                'label'       => 'Pendaftaran Provider',
                'method'      => 'GET',
                'path'        => '/pendaftaran/tglDaftar/{param1}/{param2}/{param3}',
                'description' => 'Get Data Pendaftaran Provider - Parameter 1: Tanggal, Parameter 2: Row awal, Parameter 3: Limit',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Tanggal Pendaftaran (dd-mm-yyyy)', 'default' => ''],
                    ['name' => 'param2', 'type' => 'number', 'placeholder' => 'Row data awal (default: 1)', 'default' => '1'],
                    ['name' => 'param3', 'type' => 'number', 'placeholder' => 'Limit (default: 10)', 'default' => '10'],
                ],
            ],
            [
                'key'         => 'pendaftaran_add',
                'label'       => 'Add Pendaftaran',
                'method'      => 'POST',
                'path'        => '/pendaftaran',
                'description' => 'Add Data Pendaftaran',
                'body'        => [
                    ['name' => 'request', 'type' => 'json', 'placeholder' => 'JSON body pendaftaran', 'default' => ''],
                ],
            ],
            [
                'key'         => 'pendaftaran_delete',
                'label'       => 'Delete Pendaftaran',
                'method'      => 'DELETE',
                'path'        => '/pendaftaran/peserta/{param1}/tglDaftar/{param2}/noUrut/{param3}/kdPoli/{param4}',
                'description' => 'Delete Data Pendaftaran - Parameter 1: No Kartu, 2: Tgl, 3: No Urut, 4: Kd Poli',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Nomor Kartu Peserta', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Tanggal Pendaftaran (dd-mm-yyyy)', 'default' => ''],
                    ['name' => 'param3', 'type' => 'text', 'placeholder' => 'Nomor Urut Pendaftaran', 'default' => ''],
                    ['name' => 'param4', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                ],
            ],
            // Peserta
            [
                'key'         => 'peserta',
                'label'       => 'Peserta',
                'method'      => 'GET',
                'path'        => '/peserta/{param}',
                'description' => 'Get Data Peserta - Parameter: Nomor Kartu Peserta',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Nomor Kartu Peserta', 'default' => ''],
                ],
            ],
            [
                'key'         => 'peserta_jenis',
                'label'       => 'Peserta by Jenis Kartu',
                'method'      => 'GET',
                'path'        => '/peserta/{param1}/{param2}',
                'description' => 'Get Data Peserta - Parameter 1: Jenis Kartu (nik/noka), Parameter 2: Nomor NIK/kartu',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Jenis Kartu (nik/noka)', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Nomor NIK atau kartu peserta', 'default' => ''],
                ],
            ],
            // Poli
            [
                'key'         => 'poli_fktp',
                'label'       => 'Poli FKTP',
                'method'      => 'GET',
                'path'        => '/poli/fktp/{param1}/{param2}',
                'description' => 'Get Data Poli FKTP - Parameter 1: Row awal, Parameter 2: Limit',
                'params'      => [
                    ['name' => 'param1', 'type' => 'number', 'placeholder' => 'Row data awal (default: 1)', 'default' => '1'],
                    ['name' => 'param2', 'type' => 'number', 'placeholder' => 'Limit (default: 10)', 'default' => '10'],
                ],
            ],
            // Provider
            [
                'key'         => 'provider',
                'label'       => 'Provider Rayonisasi',
                'method'      => 'GET',
                'path'        => '/provider/{param1}/{param2}',
                'description' => 'Get Data Provider Rayonisasi - Parameter 1: Row awal, Parameter 2: Limit',
                'params'      => [
                    ['name' => 'param1', 'type' => 'number', 'placeholder' => 'Row data awal (default: 1)', 'default' => '1'],
                    ['name' => 'param2', 'type' => 'number', 'placeholder' => 'Limit (default: 10)', 'default' => '10'],
                ],
            ],
            // Spesialis
            [
                'key'         => 'spesialis',
                'label'       => 'Referensi Spesialis',
                'method'      => 'GET',
                'path'        => '/spesialis',
                'description' => 'Get Data Referensi Spesialis',
                'params'      => [],
            ],
            [
                'key'         => 'spesialis_sub',
                'label'       => 'Referensi Sub Spesialis',
                'method'      => 'GET',
                'path'        => '/spesialis/{param}/subspesialis',
                'description' => 'Get Data Referensi Sub Spesialis - Parameter: Kode Spesialis',
                'params'      => [
                    ['name' => 'param', 'type' => 'text', 'placeholder' => 'Kode Spesialis', 'default' => ''],
                ],
            ],
            [
                'key'         => 'spesialis_sarana',
                'label'       => 'Referensi Sarana',
                'method'      => 'GET',
                'path'        => '/spesialis/sarana',
                'description' => 'Get Data Referensi Sarana',
                'params'      => [],
            ],
            [
                'key'         => 'spesialis_khusus',
                'label'       => 'Referensi Khusus',
                'method'      => 'GET',
                'path'        => '/spesialis/khusus',
                'description' => 'Get Data Referensi Khusus',
                'params'      => [],
            ],
            [
                'key'         => 'spesialis_rujuk_sub',
                'label'       => 'Faskes Rujukan Sub Spesialis',
                'method'      => 'GET',
                'path'        => '/spesialis/rujuk/subspesialis/{param1}/sarana/{param2}/tglEstRujuk/{param3}',
                'description' => 'Get Faskes Rujukan Sub Spesialis - Parameter 1: Kd SubSpesialis, 2: Kd Sarana, 3: Tgl (dd-mm-yyyy)',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Kode Sub Spesialis', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Kode Sarana', 'default' => ''],
                    ['name' => 'param3', 'type' => 'text', 'placeholder' => 'Tanggal (dd-mm-yyyy)', 'default' => ''],
                ],
            ],
            [
                'key'         => 'spesialis_rujuk_khusus',
                'label'       => 'Faskes Rujukan Khusus',
                'method'      => 'GET',
                'path'        => '/spesialis/rujuk/khusus/{param1}/noKartu/{param2}/tglEstRujuk/{param3}',
                'description' => 'Get Faskes Rujukan Khusus - Parameter 1: KdKhusus, 2: NoKartu, 3: Tgl (dd-mm-yyyy)',
                'params'      => [
                    ['name' => 'param1', 'type' => 'text', 'placeholder' => 'Kode Khusus (HDL/THA/HEM/IGD)', 'default' => ''],
                    ['name' => 'param2', 'type' => 'text', 'placeholder' => 'Nomor Kartu Peserta', 'default' => ''],
                    ['name' => 'param3', 'type' => 'text', 'placeholder' => 'Tanggal (dd-mm-yyyy)', 'default' => ''],
                ],
            ],
        ],
    ],
    'antrean_fktp' => [
        'label'       => 'Antrean FKTP',
        'icon'        => '🏥',
        'description' => 'Modul Antrean Fasilitas Kesehatan Tingkat Pertama',
        'base_url'    => getBaseUrl('antrean_fktp', $currentDomain, $isDevMode),
        'sub_modules' => [
            // Token
            [
                'key'         => 'token_fktp',
                'label'       => 'Token',
                'method'      => 'GET',
                'path'        => '/auth',
                'description' => 'Membuat token akses - Header: x-username, x-password',
                'headers'     => ['x-username', 'x-password'],
            ],
            // Status Antrean
            [
                'key'         => 'status_antrean_fktp',
                'label'       => 'Status Antrean',
                'method'      => 'GET',
                'path'        => '/antrean/status/{kode_poli}/{tanggalperiksa}',
                'description' => 'Menampilkan status antrean per poli - Header: x-token, x-username',
                'params'      => [
                    ['name' => 'kode_poli', 'type' => 'text', 'placeholder' => 'Kode Poli (misal: 001)', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                ],
                'headers'     => ['x-token', 'x-username'],
            ],
            // Ambil Antrean
            [
                'key'         => 'ambil_antrean_fktp',
                'label'       => 'Ambil Antrean',
                'method'      => 'POST',
                'path'        => '/antrean',
                'description' => 'Mengambil antrean - Header: x-token, x-username',
                'body'        => [
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu BPJS (kosongkan jika NON JKN)', 'default' => ''],
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'NIK Pasien', 'default' => ''],
                    ['name' => 'nohp', 'type' => 'text', 'placeholder' => 'No HP Pasien', 'default' => ''],
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                    ['name' => 'namapoli', 'type' => 'text', 'placeholder' => 'Nama Poli', 'default' => ''],
                    ['name' => 'norm', 'type' => 'text', 'placeholder' => 'No Rekam Medis', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal Periksa (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                    ['name' => 'kodedokter', 'type' => 'number', 'placeholder' => 'Kode Dokter', 'default' => ''],
                    ['name' => 'namadokter', 'type' => 'text', 'placeholder' => 'Nama Dokter', 'default' => ''],
                    ['name' => 'jampraktek', 'type' => 'text', 'placeholder' => 'Jam Praktek', 'default' => ''],
                    ['name' => 'nomorantrean', 'type' => 'text', 'placeholder' => 'Nomor Antrean', 'default' => ''],
                    ['name' => 'angkaantrean', 'type' => 'number', 'placeholder' => 'Angka Antrean', 'default' => ''],
                    ['name' => 'keterangan', 'type' => 'text', 'placeholder' => 'Keterangan', 'default' => ''],
                ],
                'headers'     => ['x-token', 'x-username'],
            ],
            // Sisa Antrean
            [
                'key'         => 'sisa_antrean_fktp',
                'label'       => 'Sisa Antrean',
                'method'      => 'GET',
                'path'        => '/antrean/sisapeserta/{nomorkartu}/{kode_poli}/{tanggalperiksa}',
                'description' => 'Meliat sisa antrean di hari H pelayanan - Header: x-token, x-username',
                'params'      => [
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'Nomor Kartu', 'default' => ''],
                    ['name' => 'kode_poli', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                ],
                'headers'     => ['x-token', 'x-username'],
            ],
            // Pasien Baru
            [
                'key'         => 'pasien_baru_fktp',
                'label'       => 'Pasien Baru',
                'method'      => 'POST',
                'path'        => '/peserta',
                'description' => 'Kirim informasi identitas peserta sebagai pasien baru - Header: x-token, x-username',
                'body'        => [
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu', 'default' => ''],
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'NIK', 'default' => ''],
                    ['name' => 'nomorkk', 'type' => 'text', 'placeholder' => 'No KK', 'default' => ''],
                    ['name' => 'nama', 'type' => 'text', 'placeholder' => 'Nama', 'default' => ''],
                    ['name' => 'jeniskelamin', 'type' => 'text', 'placeholder' => 'Jenis Kelamin (L/P)', 'default' => ''],
                    ['name' => 'tanggallahir', 'type' => 'text', 'placeholder' => 'Tanggal Lahir (YYYY-MM-DD)', 'default' => ''],
                    ['name' => 'alamat', 'type' => 'text', 'placeholder' => 'Alamat', 'default' => ''],
                    ['name' => 'kodeprop', 'type' => 'text', 'placeholder' => 'Kode Propinsi', 'default' => ''],
                    ['name' => 'namaprop', 'type' => 'text', 'placeholder' => 'Nama Propinsi', 'default' => ''],
                    ['name' => 'kodedati2', 'type' => 'text', 'placeholder' => 'Kode Dati 2', 'default' => ''],
                    ['name' => 'namadati2', 'type' => 'text', 'placeholder' => 'Nama Dati 2', 'default' => ''],
                    ['name' => 'kodekec', 'type' => 'text', 'placeholder' => 'Kode Kecamatan', 'default' => ''],
                    ['name' => 'namakec', 'type' => 'text', 'placeholder' => 'Nama Kecamatan', 'default' => ''],
                    ['name' => 'kodekel', 'type' => 'text', 'placeholder' => 'Kode Kelurahan', 'default' => ''],
                    ['name' => 'namakel', 'type' => 'text', 'placeholder' => 'Nama Kelurahan', 'default' => ''],
                    ['name' => 'rw', 'type' => 'text', 'placeholder' => 'RW', 'default' => ''],
                    ['name' => 'rt', 'type' => 'text', 'placeholder' => 'RT', 'default' => ''],
                ],
                'headers'     => ['x-token', 'x-username'],
            ],
            // Batal Antrean
            [
                'key'         => 'batal_antrean_fktp',
                'label'       => 'Batal Antrean',
                'method'      => 'PUT',
                'path'        => '/antrean/batal',
                'description' => 'Membatalkan antrean pasien - Header: x-token, x-username',
                'body'        => [
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu', 'default' => ''],
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal Periksa (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                    ['name' => 'keterangan', 'type' => 'text', 'placeholder' => 'Keterangan', 'default' => ''],
                ],
                'headers'     => ['x-token', 'x-username'],
            ],
            // Referensi Poli New
            [
                'key'         => 'ref_poli_fktp',
                'label'       => 'Referensi Poli',
                'method'      => 'GET',
                'path'        => '/ref/poli/tanggal/{tanggal}',
                'description' => 'Meliat referensi poli - Header: x-cons-id, x-timestamp, x-signature, user_key',
                'params'      => [
                    ['name' => 'tanggal', 'type' => 'text', 'placeholder' => 'Tanggal (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                ],
                'headers'     => ['x-cons-id', 'x-timestamp', 'x-signature', 'user_key'],
            ],
            // Referensi Dokter New
            [
                'key'         => 'ref_dokter_fktp',
                'label'       => 'Referensi Dokter',
                'method'      => 'GET',
                'path'        => '/ref/dokter/kodepoli/{kodepoli}/tanggal/{tanggal}',
                'description' => 'Meliat list dokter per poli - Header: x-cons-id, x-timestamp, x-signature, user_key',
                'params'      => [
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                    ['name' => 'tanggal', 'type' => 'text', 'placeholder' => 'Tanggal (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                ],
                'headers'     => ['x-cons-id', 'x-timestamp', 'x-signature', 'user_key'],
            ],
            // Tambah Antrean New
            [
                'key'         => 'tambah_antrean_fktp',
                'label'       => 'Tambah Antrean',
                'method'      => 'POST',
                'path'        => '/antrean/add',
                'description' => 'Menambah antrean - Header: x-token, x-username',
                'body'        => [
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu BPJS', 'default' => ''],
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'NIK Pasien', 'default' => ''],
                    ['name' => 'nohp', 'type' => 'text', 'placeholder' => 'No HP Pasien', 'default' => ''],
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                    ['name' => 'namapoli', 'type' => 'text', 'placeholder' => 'Nama Poli', 'default' => ''],
                    ['name' => 'norm', 'type' => 'text', 'placeholder' => 'No Rekam Medis', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal Periksa (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                    ['name' => 'kodedokter', 'type' => 'number', 'placeholder' => 'Kode Dokter', 'default' => ''],
                    ['name' => 'namadokter', 'type' => 'text', 'placeholder' => 'Nama Dokter', 'default' => ''],
                    ['name' => 'jampraktek', 'type' => 'text', 'placeholder' => 'Jam Praktek', 'default' => ''],
                    ['name' => 'nomorantrean', 'type' => 'text', 'placeholder' => 'Nomor Antrean', 'default' => ''],
                    ['name' => 'angkaantrean', 'type' => 'number', 'placeholder' => 'Angka Antrean', 'default' => ''],
                    ['name' => 'keterangan', 'type' => 'text', 'placeholder' => 'Keterangan', 'default' => ''],
                ],
                'headers'     => ['x-token', 'x-username'],
            ],
            // Update Status / Panggil Antrean
            [
                'key'         => 'panggil_antrean_fktp',
                'label'       => 'Panggil Antrean',
                'method'      => 'POST',
                'path'        => '/antrean/panggil',
                'description' => 'Update status antrean hadir/tidak hadir - Header: x-cons-id, x-timestamp, x-signature, user_key',
                'body'        => [
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal Periksa (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu', 'default' => ''],
                    ['name' => 'status', 'type' => 'number', 'placeholder' => 'Status (1=Hadir, 2=Tidak Hadir)', 'default' => '1'],
                    ['name' => 'waktu', 'type' => 'number', 'placeholder' => 'Waktu (timestamp ms)', 'default' => ''],
                ],
                'headers'     => ['x-cons-id', 'x-timestamp', 'x-signature', 'user_key'],
            ],
            // Batal Antrean New
            [
                'key'         => 'batal_antrean_new_fktp',
                'label'       => 'Batal Antrean New',
                'method'      => 'POST',
                'path'        => '/antrean/batal',
                'description' => 'Membatalkan antrean pasien - Header: x-token, x-username',
                'body'        => [
                    ['name' => 'nomorkartu', 'type' => 'text', 'placeholder' => 'No Kartu', 'default' => ''],
                    ['name' => 'kodepoli', 'type' => 'text', 'placeholder' => 'Kode Poli', 'default' => ''],
                    ['name' => 'tanggalperiksa', 'type' => 'text', 'placeholder' => 'Tanggal Periksa (YYYY-MM-DD)', 'default' => date('Y-m-d')],
                ],
                'headers'     => ['x-token', 'x-username'],
            ],
        ],
    ],
    'icare' => [
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
    ],
    'ws_rekam_medis' => [
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
    ],
];

/*
|--------------------------------------------------------------------------
| HANDLE API REQUEST
|--------------------------------------------------------------------------
*/

$apiResponse  = null;
$apiError     = '';
$selectedSub  = null;
$selectedMod  = null;
$debugInfo    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_request') {
    $debugInfo['POST'] = $_POST;

    $moduleKey  = $_POST['module_key']  ?? '';
    $subKey     = $_POST['sub_key']     ?? '';
    $method     = strtoupper($_POST['method'] ?? 'GET');
    $path       = $_POST['path']        ?? '';
    $baseUrl    = $_POST['base_url']    ?? '';
    $rawParams  = $_POST['params']      ?? '{}';
    $rawBody    = $_POST['body']        ?? '{}';
    $decrypt    = isset($_POST['decrypt']) && $_POST['decrypt'] === '1';

    $params = is_array($rawParams) ? $rawParams : (json_decode($rawParams, true) ?: []);
    $body   = is_array($rawBody)   ? $rawBody   : (json_decode($rawBody,   true) ?: []);

    // ── Validate required path params before building URL ──
    $missingParams = [];
    foreach ($params as $p) {
        if (strpos($path, '{' . $p['name'] . '}') !== false && empty(trim($p['value'] ?? ''))) {
            $missingParams[] = $p['name'];
        }
    }
    if (!empty($missingParams)) {
        $apiError  = 'Parameter wajib belum diisi: ' . implode(', ', $missingParams);
        $apiResponse = null;
        $debugInfo['missing_params'] = $missingParams;
    } else {
        $debugInfo['params'] = $params;

        // Build URL with path params
        $url = rtrim($baseUrl, '/') . $path;
        foreach ($params as $p) {
            $url = str_replace('{' . $p['name'] . '}', urlencode($p['value'] ?? ''), $url);
        }

        // Build query string from remaining (non-path) params
        $queryParams = [];
        foreach ($params as $p) {
            if (strpos($path, '{' . $p['name'] . '}') === false && !empty(trim($p['value'] ?? ''))) {
                $queryParams[$p['name']] = $p['value'];
            }
        }
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $debugInfo['url'] = $url;
        $debugInfo['method'] = $method;

        $auth = generateSignature($consId, $secretKey);

        $requestConfig = [
            'url'       => $url,
            'method'    => $method,
            'cons_id'   => $consId,
            'timestamp' => $auth['timestamp'],
            'signature' => $auth['signature'],
            'user_key'  => $userKey,
        ];

        if (!empty($body) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $requestConfig['body'] = $body;
        }

        $response = bpjsRequest($requestConfig);
        $debugInfo['bpjs_response'] = $response;

        if (!$response['status']) {
            $apiError  = $response['message'] ?? 'Unknown error';
            $apiResponse = null;
        } else {
            $apiResponse = $response['data'];
            
            // Check if the response is HTML (error page)
            $rawResponse = $response['raw_response'] ?? null;
            if ($rawResponse && (strpos($rawResponse, '<html>') !== false || strpos($rawResponse, '<!DOCTYPE') !== false)) {
                $apiError = 'API returned HTML error. Possible causes: wrong endpoint URL, invalid credentials, or API not available. Response: ' . htmlspecialchars(substr($rawResponse, 0, 500));
                $apiResponse = null;
            } elseif ($apiResponse && isset($apiResponse['response']) && is_string($apiResponse['response'])) {
                // BPJS API returns encrypted data in the 'response' field
                // Only decrypt if the decrypt checkbox is checked
                if ($decrypt) {
                    try {
                        $apiResponse['response'] = decryptResponse($consId, $secretKey, $auth['timestamp'], $apiResponse['response']);
                    } catch (\Exception $e) {
                        $apiResponse['decrypt_error'] = 'Decrypt failed: ' . $e->getMessage();
                    }
                }
            }
        }
    }

    // Re-find selected module/sub for UI highlight
    foreach ($modules as $mk => $mv) {
        foreach ($mv['sub_modules'] as $sm) {
            if ($sm['key'] === $subKey) {
                $selectedMod = $mk;
                $selectedSub = $subKey;
                break 2;
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| ACTIVE MODULE / SUB (from GET or POST)
|--------------------------------------------------------------------------
*/

if ($selectedMod === null) {
    $selectedMod = $_POST['module'] ?? ($_GET['module']  ?? array_key_first($modules));
}
if ($selectedSub === null) {
    $selectedSub = $_POST['sub']    ?? ($_GET['sub']     ?? null);
}

$activeModule = $modules[$selectedMod] ?? $modules[array_key_first($modules)];
$activeSubs    = $activeModule['sub_modules'] ?? [];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPJS Web Service Catalog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bpjs: {
                            50:  '#e6f0ff',
                            100: '#b3c9ff',
                            200: '#80a2ff',
                            300: '#4d7bff',
                            400: '#1a5cff',
                            500: '#0047e6',
                            600: '#0039b3',
                            700: '#002b80',
                            800: '#001d4d',
                            900: '#000f1a',
                        }
                    }
                }
            }
        }

        // Switch API Version (V1 or V2) - disabled when dev mode is active
        function switchApiVersion(version) {
            // Set cookie for 30 days
            const d = new Date();
            d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();
            document.cookie = "bpjs_api_version=" + version + ";" + expires + ";path=/";
            
            // Reload page to apply new version
            window.location.reload();
        }

        // Switch Mode (DEV/PRODUCTION)
        function switchMode(isDev) {
            // Set cookie for 30 days
            const d = new Date();
            d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();
            document.cookie = "bpjs_dev_mode=" + (isDev ? 'true' : 'false') + ";" + expires + ";path=/";
            
            // Reload page to apply new mode
            window.location.reload();
        }

        // Copy format to clipboard
        function copyFormat(button) {
            const pre = button.closest('.bg-slate-800/60').querySelector('code');
            const text = pre.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '<svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tersalin!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
            });
        }
    </script>
    <style>
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: #1e293b; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
        .light-theme .sidebar-scroll::-webkit-scrollbar-track { background: #e2e8f0; }
        .light-theme .sidebar-scroll::-webkit-scrollbar-thumb { background: #94a3b8; }
        .method-get    { color: #22c55e; }
        .method-post   { color: #3b82f6; }
        .method-put    { color: #f59e0b; }
        .method-delete { color: #ef4444; }
        .method-patch  { color: #a855f7; }
        .badge-get    { background: #dcfce7; color: #166534; }
        .badge-post   { background: #dbeafe; color: #1e40af; }
        .badge-put    { background: #fef3c7; color: #92400e; }
        .badge-delete { background: #fee2e2; color: #991b1b; }
        .badge-patch  { background: #f3e8ff; color: #6b21a8; }
        pre { white-space: pre-wrap; word-break: break-all; }
        
        /* Light theme styles */
        .light-theme {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }
        .light-theme .bg-slate-900 { background-color: #f8fafc !important; }
        .light-theme .bg-slate-800 { background-color: #f1f5f9 !important; }
        .light-theme .bg-slate-700 { background-color: #e2e8f0 !important; }
        .light-theme .text-slate-100 { color: #1e293b !important; }
        .light-theme .text-slate-200 { color: #1e293b !important; }
        .light-theme .text-slate-300 { color: #334155 !important; }
        .light-theme .text-slate-400 { color: #64748b !important; }
        .light-theme .text-slate-500 { color: #64748b !important; }
        .light-theme .text-slate-600 { color: #94a3b8 !important; }
        .light-theme .border-slate-700 { border-color: #e2e8f0 !important; }
        .light-theme .border-slate-600 { border-color: #cbd5e1 !important; }
        .light-theme .bg-bpjs-700 { background-color: #0047e6 !important; }
        .light-theme .text-bpjs-100 { color: #b3c9ff !important; }
        .light-theme .text-bpjs-200 { color: #80a2ff !important; }
        .light-theme .text-bpjs-300 { color: #4d7bff !important; }
        .light-theme .bg-bpjs-600 { background-color: #1a5cff !important; }
        .light-theme .text-white { color: #ffffff !important; }
        .light-theme .text-green-400 { color: #16a34a !important; }
        .light-theme .text-amber-400 { color: #d97706 !important; }
        .light-theme .bg-white\/20 { background-color: #ffffff33 !important; }
        .light-theme .text-bpjs-500\/30 { color: #0047e64d !important; }
        .light-theme .bg-bpjs-500\/30 { background-color: #0047e64d !important; }
        .light-theme .border-bpjs-400\/50 { border-color: #1a5cff80 !important; }
        .light-theme .border-bpjs-400\/40 { border-color: #1a5cff66 !important; }
        .light-theme .bg-bpjs-600\/40 { background-color: #1a5cff66 !important; }
        .light-theme .shadow-bpjs-900\/30 { box-shadow: 0 10px 15px -3px #000f1a4d, 0 4px 6px -2px #000f1a1a !important; }
        .light-theme .shadow-bpjs-900\/40 { box-shadow: 0 10px 15px -3px #000f1a66, 0 4px 6px -2px #000f1a20 !important; }
        .light-theme .bg-slate-800\/50 { background-color: #f1f5f980 !important; }
        .light-theme .bg-slate-700\/60 { background-color: #e2e8f099 !important; }
        .light-theme .bg-slate-700\/30 { background-color: #e2e8f04d !important; }
        .light-theme .bg-slate-700\/50 { background-color: #e2e8f080 !important; }
        .light-theme .bg-red-900\/30 { background-color: #fef2f2 !important; }
        .light-theme .border-red-700 { border-color: #fecaca !important; }
        .light-theme .text-red-400 { color: #dc2626 !important; }
        .light-theme .text-red-300 { color: #dc2626 !important; }
        .light-theme .bg-green-900\/40 { background-color: #f0fdf4 !important; }
        .light-theme .border-green-700 { border-color: #bbf7d0 !important; }
        .light-theme .text-green-400 { color: #16a34a !important; }
        .light-theme .bg-yellow-900\/40 { background-color: #fefce8 !important; }
        .light-theme .border-yellow-700 { border-color: #fef08a !important; }
        .light-theme .text-yellow-400 { color: #ca8a04 !important; }
        .light-theme input, .light-theme select, .light-theme textarea {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
        }
        .light-theme .placeholder-slate-500::placeholder { color: #94a3b8 !important; }
        .light-theme .hover\:bg-slate-700:hover { background-color: #e2e8f0 !important; }
        .light-theme .hover\:text-slate-200:hover { color: #1e293b !important; }
        .light-theme .hover\:text-bpjs-200:hover { color: #80a2ff !important; }
        .light-theme .hover\:text-bpjs-100:hover { color: #b3c9ff !important; }
        .light-theme .hover\:bg-slate-700\/60:hover { background-color: #e2e8f099 !important; }
        .light-theme .hover\:border-slate-600:hover { border-color: #cbd5e1 !important; }
        .light-theme pre { color: #334155 !important; }
        .light-theme code { color: #334155 !important; }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 h-screen overflow-hidden flex flex-col transition-colors duration-300 dark-theme" id="body">

    <!-- ===== TOP HEADER ===== -->
    <header class="bg-bpjs-700 border-b border-bpjs-500 px-6 py-3 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center text-bpjs-700 font-bold text-lg">B</div>
            <div>
                <h1 class="text-white font-bold text-lg leading-tight">BPJS Web Service Catalog</h1>
                <p class="text-bpjs-100 text-xs">API Documentation & Testing Tool</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-xs text-bpjs-100">Cons ID</p>
                <?php
                // Mask Cons ID with asterisks for security
                $maskedConsId = str_repeat('*', strlen($consId));
                ?>
                <p class="text-white text-sm font-mono font-semibold"><?= htmlspecialchars($maskedConsId) ?></p>
            </div>
            <div class="w-px h-8 bg-bpjs-500"></div>
            <div class="text-right">
                <p class="text-xs text-bpjs-100">Mode</p>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" onchange="switchMode(this.checked)" <?= $isDevMode ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-bpjs-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-bpjs-500"></div>
                    <span class="ml-2 text-sm font-semibold <?= $isDevMode ? 'text-orange-400' : 'text-slate-400' ?>"><?= $isDevMode ? 'DEV' : 'PROD' ?></span>
                </label>
            </div>
            <div class="w-px h-8 bg-bpjs-500"></div>
            <div class="text-right">
                <p class="text-xs text-bpjs-100">API Version</p>
                <select onchange="switchApiVersion(this.value)" class="text-white text-sm font-semibold bg-bpjs-600 border border-bpjs-500 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-bpjs-400 <?= $isDevMode ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $isDevMode ? 'disabled' : '' ?>>
                    <option value="v1" <?= $apiDomainVersion === 'v1' ? 'selected' : '' ?> class="bg-slate-900">V1 (apijkn.bpjs-kesehatan.go.id)</option>
                    <option value="v2" <?= $apiDomainVersion === 'v2' ? 'selected' : '' ?> class="bg-slate-900">V2 (new-apijkn.bpjs-kesehatan.go.id)</option>
                </select>
            </div>
            <div class="w-px h-8 bg-bpjs-500"></div>
            <div class="text-right">
                <p class="text-xs text-bpjs-100">Environment</p>
                <p class="text-green-400 text-sm font-semibold">● Production</p>
            </div>
            <div class="w-px h-8 bg-bpjs-500"></div>
            <div class="text-right">
                <p class="text-xs text-bpjs-100">Theme</p>
                <button onclick="toggleTheme()" id="themeToggle" class="flex items-center gap-1.5 text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                    <span id="themeIcon">🌙</span>
                    <span id="themeText">Dark</span>
                </button>
            </div>
        </div>
    </header>

    <!-- ===== MAIN LAYOUT ===== -->
    <div class="flex flex-1 overflow-hidden">

        <!-- ===== SIDEBAR ===== -->
        <aside class="w-72 bg-slate-800 border-r border-slate-700 flex flex-col flex-shrink-0">

            <!-- Search -->
            <div class="p-3 border-b border-slate-700">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">🔍</span>
                    <input
                        type="text"
                        id="sidebarSearch"
                        placeholder="Cari modul / endpoint..."
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-bpjs-400 focus:ring-1 focus:ring-bpjs-400"
                    >
                </div>
            </div>

            <!-- Module List -->
            <nav class="flex-1 overflow-y-auto sidebar-scroll p-2 space-y-1" id="moduleNav">

                <?php foreach ($modules as $modKey => $mod): ?>
                    <?php
                        $isActive   = ($modKey === $selectedMod);
                        $subCount   = count($mod['sub_modules']);
                        $expanded   = $isActive || $selectedSub !== null;
                    ?>
                    <div class="module-group" data-module="<?= $modKey ?>">

                        <!-- Module Header -->
                        <button
                            type="button"
                            onclick="toggleModule('<?= $modKey ?>')"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-colors
                                <?= $isActive ? 'bg-bpjs-600 text-white' : 'text-slate-300 hover:bg-slate-700' ?>"
                        >
                            <span class="text-lg flex-shrink-0"><?= $mod['icon'] ?></span>
                            <span class="flex-1 font-semibold text-sm truncate"><?= $mod['label'] ?></span>
                            <span class="text-xs bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded-full flex-shrink-0"><?= $subCount ?></span>
                            <svg id="arrow-<?= $modKey ?>" class="w-4 h-4 flex-shrink-0 transition-transform <?= $expanded ? 'rotate-90' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <!-- Sub-modules -->
                        <div id="subs-<?= $modKey ?>" class="overflow-hidden transition-all duration-200 <?= $expanded ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0' ?>">
                            <div class="ml-4 mt-1 space-y-0.5 border-l-2 border-slate-700 pl-2 pb-2">

                                <?php foreach ($mod['sub_modules'] as $sub): ?>
                                    <?php
                                        $subActive = ($modKey === $selectedMod && $sub['key'] === $selectedSub);
                                    ?>
                                    <a
                                        href="?module=<?= $modKey ?>&sub=<?= $sub['key'] ?>"
                                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition-colors
                                            <?= $subActive
                                                ? 'bg-bpjs-500/30 text-bpjs-100 border border-bpjs-400/40'
                                                : 'text-slate-400 hover:bg-slate-700/60 hover:text-slate-200' ?>"
                                    >
                                        <span class="method-<?= strtolower($sub['method']) ?> font-mono font-bold text-[10px] w-10 text-center flex-shrink-0">
                                            <?= $sub['method'] ?>
                                        </span>
                                        <span class="truncate"><?= $sub['label'] ?></span>
                                    </a>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </nav>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t border-slate-700 text-[10px] text-slate-500 text-center">
                BPJS Kesehatan API Catalog v1.0
            </div>
        </aside>

        <!-- ===== MAIN CONTENT ===== -->
        <main class="flex-1 overflow-y-auto bg-slate-900">

            <!-- ===== MODULE HEADER ===== -->
            <div class="bg-gradient-to-r from-bpjs-700 to-bpjs-600 px-8 py-6 border-b border-bpjs-500">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-3xl"><?= $activeModule['icon'] ?></span>
                            <h2 class="text-2xl font-bold text-white"><?= $activeModule['label'] ?></h2>
                        </div>
                        <p class="text-bpjs-100 text-sm"><?= $activeModule['description'] ?></p>
                        <p class="text-bpjs-200 text-xs mt-1 font-mono">
                            Base URL: <span class="text-white"><?= $activeModule['base_url'] ?></span>
                            <span class="ml-2 px-2 py-0.5 bg-bpjs-500/30 rounded text-[10px] text-bpjs-200"><?= strtoupper($apiDomainVersion) ?> API</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block bg-white/20 text-white text-xs px-3 py-1 rounded-full">
                            <?= count($activeModule['sub_modules']) ?> Endpoints
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex">

                <!-- ===== ENDPOINT LIST (LEFT) ===== -->
                <div class="w-80 border-r border-slate-700 overflow-y-auto sidebar-scroll flex-shrink-0" style="max-height: calc(100vh - 200px);">
                    <div class="p-4">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Daftar Endpoint</h3>
                        <div class="space-y-1" id="endpointList">

                            <?php foreach ($activeModule['sub_modules'] as $sub): ?>
                                <?php
                                    $subActive = $sub['key'] === $selectedSub;
                                ?>
                                <button
                                    type="button"
                                    onclick="selectEndpoint('<?= $sub['key'] ?>')"
                                    class="w-full text-left px-3 py-2.5 rounded-lg transition-all
                                        <?= $subActive
                                            ? 'bg-bpjs-600/40 border border-bpjs-400/50 shadow-lg shadow-bpjs-900/30'
                                            : 'bg-slate-800/50 border border-transparent hover:bg-slate-700/60 hover:border-slate-600' ?>"
                                >
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="badge-<?= strtolower($sub['method']) ?> text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">
                                            <?= $sub['method'] ?>
                                        </span>
                                        <span class="text-sm font-semibold text-slate-100 truncate"><?= $sub['label'] ?></span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-mono truncate"><?= $sub['path'] ?></p>
                                    <p class="text-[11px] text-slate-600 mt-1 line-clamp-2"><?= $sub['description'] ?></p>
                                </button>
                            <?php endforeach; ?>

                        </div>
                    </div>
                </div>

                <!-- ===== REQUEST / RESPONSE PANEL (RIGHT) ===== -->
                <div class="flex-1 overflow-y-auto p-6 space-y-5" style="max-height: calc(100vh - 200px);">

                    <?php if ($selectedSub === null): ?>
                        <!-- No endpoint selected -->
                        <div class="flex flex-col items-center justify-center py-20 text-slate-500">
                            <div class="text-6xl mb-4">📡</div>
                            <p class="text-lg font-semibold">Pilih endpoint dari daftar di sebelah kiri</p>
                            <p class="text-sm mt-1">Klik salah satu endpoint untuk mulai menguji API</p>
                        </div>

                    <?php else:
                        // Find selected sub-module
                        $selectedSubData = null;
                        foreach ($activeModule['sub_modules'] as $sm) {
                            if ($sm['key'] === $selectedSub) {
                                $selectedSubData = $sm;
                                break;
                            }
                        }
                        if ($selectedSubData):
                    ?>

                        <!-- ===== ENDPOINT INFO ===== -->
                        <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="badge-<?= strtolower($selectedSubData['method']) ?> text-xs font-bold px-2.5 py-1 rounded uppercase tracking-wide">
                                    <?= $selectedSubData['method'] ?>
                                </span>
                                <h3 class="text-lg font-bold text-white"><?= $selectedSubData['label'] ?></h3>
                            </div>
                            <p class="text-sm text-slate-400 mb-3"><?= $selectedSubData['description'] ?></p>
                            <div class="bg-slate-900 rounded-lg px-4 py-2.5 font-mono text-sm text-bpjs-200 border border-slate-700">
                                <span class="text-slate-500"><?= rtrim($activeModule['base_url'], '/') ?></span><?= $selectedSubData['path'] ?>
                            </div>
                        </div>

                        <!-- ===== FORMAT CONTOH ===== -->
                        <?php if (!empty($selectedSubData['format'])): ?>
                            <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-bold text-slate-300 flex items-center gap-2">
                                        <span>📋</span> Format Contoh
                                    </h4>
                                    <button type="button" onclick="copyFormat(this)" class="text-xs text-bpjs-300 hover:text-bpjs-200 flex items-center gap-1 px-2 py-1 bg-slate-700/50 rounded">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        Salin
                                    </button>
                                </div>
                                <?php
                                $formatData = $selectedSubData['format'];
                                $formatJson = isset($formatData['request']) ? $formatData['request'] : (isset($formatData['response']) ? $formatData['response'] : '');
                                ?>
                                <?php if ($formatJson): ?>
                                    <pre class="bg-slate-900 border border-slate-700 rounded-lg p-3 text-xs text-slate-300 font-mono overflow-x-auto"><code class="format-code"><?= htmlspecialchars($formatJson) ?></code></pre>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- ===== REQUEST BUILDER ===== -->
                        <form method="POST" action="" class="space-y-4">

                            <input type="hidden" name="action"        value="send_request">
                            <input type="hidden" name="module_key"     value="<?= htmlspecialchars($selectedMod) ?>">
                            <input type="hidden" name="sub_key"        value="<?= htmlspecialchars($selectedSub) ?>">
                            <input type="hidden" name="method"         value="<?= htmlspecialchars($selectedSubData['method']) ?>">
                            <input type="hidden" name="path"           value="<?= htmlspecialchars($selectedSubData['path']) ?>">
                            <input type="hidden" name="base_url"       value="<?= htmlspecialchars($activeModule['base_url']) ?>">
                            <input type="hidden" name="module"         value="<?= htmlspecialchars($selectedMod) ?>">
                            <input type="hidden" name="sub"            value="<?= htmlspecialchars($selectedSub) ?>">

                            <!-- Decrypt Toggle -->
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="decrypt" value="1" <?= ($decrypt ?? false) ? 'checked' : '' ?> class="w-4 h-4 accent-bpjs-500">
                                    <span class="text-sm text-slate-300">🔓 Decrypt Response (LZString + AES-256)</span>
                                </label>
                            </div>

                            <!-- Path / Query Parameters -->
                            <?php if (!empty($selectedSubData['params'])): ?>
                                <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5">
                                    <h4 class="text-sm font-bold text-slate-300 mb-3 flex items-center gap-2">
                                        <span>🔗</span> Path & Query Parameters
                                    </h4>
                                    <div class="space-y-3">
                                        <?php foreach ($selectedSubData['params'] as $idx => $param): ?>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1 font-mono">
                                                    <?= htmlspecialchars($param['name']) ?>
                                                </label>
                                                <input
                                                    type="text"
                                                    name="params[<?= $idx ?>][name]"
                                                    value="<?= htmlspecialchars($param['name']) ?>"
                                                    class="hidden"
                                                >
                                                <input
                                                    type="text"
                                                    name="params[<?= $idx ?>][value]"
                                                    value="<?= htmlspecialchars($param['default'] ?? '') ?>"
                                                    placeholder="<?= htmlspecialchars($param['placeholder']) ?>"
                                                    class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-bpjs-400 focus:ring-1 focus:ring-bpjs-400"
                                                >
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Request Body -->
                            <?php if (!empty($selectedSubData['body'])): ?>
                                <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5">
                                    <h4 class="text-sm font-bold text-slate-300 mb-3 flex items-center gap-2">
                                        <span>📦</span> Request Body
                                    </h4>
                                    <div class="space-y-3">
                                        <?php foreach ($selectedSubData['body'] as $idx => $field): ?>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1 font-mono">
                                                    <?= htmlspecialchars($field['name']) ?>
                                                </label>
                                                <input
                                                    type="text"
                                                    name="body[<?= $idx ?>][name]"
                                                    value="<?= htmlspecialchars($field['name']) ?>"
                                                    class="hidden"
                                                >
                                                <textarea
                                                    name="body[<?= $idx ?>][value]"
                                                    rows="3"
                                                    placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                                    class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-bpjs-400 focus:ring-1 focus:ring-bpjs-400 font-mono"
                                                ><?= htmlspecialchars($field['default'] ?? '') ?></textarea>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Send Button -->
                            <button
                                type="submit"
                                class="w-full bg-gradient-to-r from-bpjs-500 to-bpjs-400 hover:from-bpjs-400 hover:to-bpjs-300 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-bpjs-900/40 transition-all flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Kirim Request
                            </button>

                        </form>

                    <?php endif; // endif $selectedSubData ?>

                    <!-- ===== API RESPONSE ===== -->
                    <?php if ($apiResponse !== null || $apiError !== ''): ?>
                        <div class="border-t border-slate-700 pt-5 mt-5">
                            <h4 class="text-sm font-bold text-slate-300 mb-3 flex items-center gap-2">
                                <span>📨</span> Response
                            </h4>

                            <?php if ($apiError !== ''): ?>
                                <div class="bg-red-900/30 border border-red-700 rounded-xl p-4 mb-4">
                                    <p class="text-red-400 text-sm font-semibold mb-1">❌ Request Error</p>
                                    <pre class="text-red-300 text-xs"><?= htmlspecialchars($apiError) ?></pre>
                                </div>
                            <?php else: ?>
                                <?php
                                    // Support both 'metadata' (lowercase) and 'metaData' (camelCase) keys
                                    $metaData  = $apiResponse['metadata'] ?? $apiResponse['metaData'] ?? [];
                                    $httpCode  = $metaData['code']    ?? 'N/A';
                                    $httpMsg   = $metaData['message'] ?? '';
                                    $isNumeric = is_numeric($httpCode);
                                    $codeClass = $isNumeric
                                        ? (($httpCode >= 200 && $httpCode < 300) ? 'green'
                                            : (($httpCode >= 400 && $httpCode < 500) ? 'yellow' : 'red'))
                                        : 'slate';
                                ?>
                                <div class="flex items-center gap-3 mb-3 flex-wrap">
                                    <span class="bg-<?= $codeClass === 'green' ? 'green' : ($codeClass === 'yellow' ? 'yellow' : ($codeClass === 'red' ? 'red' : 'slate')) ?>-900/40 border border-<?= $codeClass === 'green' ? 'green' : ($codeClass === 'yellow' ? 'yellow' : ($codeClass === 'red' ? 'red' : 'slate')) ?>-700 text-<?= $codeClass === 'green' ? 'green' : ($codeClass === 'yellow' ? 'yellow' : ($codeClass === 'red' ? 'red' : 'slate')) ?>-400 text-xs font-bold px-2.5 py-1 rounded-lg">
                                        HTTP <?= htmlspecialchars($httpCode) ?>
                                    </span>
                                    <span class="text-slate-400 text-sm"><?= htmlspecialchars($httpMsg) ?></span>
                                    <?php if (!$isNumeric || $httpCode < 200 || $httpCode >= 400): ?>
                                        <span class="text-amber-400 text-xs font-semibold">⚠ API mengembalikan error</span>
                                    <?php endif; ?>
                                </div>

                                <div class="bg-slate-800/60 border border-slate-700 rounded-xl overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-2 bg-slate-800 border-b border-slate-700">
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-warrow">Response Body</span>
                                        <button
                                            type="button"
                                            onclick="copyResponse()"
                                            class="text-xs text-bpjs-300 hover:text-bpjs-100 transition-colors"
                                        >
                                            📋 Copy
                                        </button>
                                    </div>
                                    <?php
                                        $jsonPretty = json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                        if ($jsonPretty === false) {
                                            $jsonPretty = print_r($apiResponse, true);
                                        }
                                    ?>
                                    <pre id="responseBody" class="p-4 text-xs text-slate-300 font-mono overflow-x-auto"><?= htmlspecialchars($jsonPretty) ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif (!empty($debugInfo)): ?>
                        <div class="border-t border-slate-700 pt-5 mt-5">
                            <h4 class="text-sm font-bold text-amber-400 mb-3">🐛 Debug Info</h4>
                            <pre class="text-xs text-slate-400"><?= htmlspecialchars(print_r($debugInfo, true)) ?></pre>
                        </div>
                    <?php endif; ?>

                    <?php endif; // endif $selectedSub === null ?>

                </div><!-- /right panel -->
            </div><!-- /flex -->
        </main>
    </div><!-- /main layout -->

    <!-- ===== JAVASCRIPT ===== -->
    <script>
        // Toggle module accordion
        function toggleModule(key) {
            const subs   = document.getElementById('subs-' + key);
            const arrow  = document.getElementById('arrow-' + key);
            const isOpen = subs.style.maxHeight && subs.style.maxHeight !== '0px';

            if (isOpen) {
                subs.style.maxHeight = '0px';
                subs.style.opacity   = '0';
                arrow.classList.remove('rotate-90');
            } else {
                subs.style.maxHeight = subs.scrollHeight + 'px';
                subs.style.opacity   = '1';
                arrow.classList.add('rotate-90');
            }
        }

        // Select endpoint from left list
        function selectEndpoint(key) {
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '';
            const modInput = document.createElement('input');
            modInput.type  = 'hidden';
            modInput.name  = 'module';
            modInput.value = '<?= htmlspecialchars($selectedMod) ?>';
            const subInput = document.createElement('input');
            subInput.type  = 'hidden';
            subInput.name  = 'sub';
            subInput.value = key;
            form.appendChild(modInput);
            form.appendChild(subInput);
            document.body.appendChild(form);
            form.submit();
        }

        // Copy response to clipboard
        function copyResponse() {
            const text = document.getElementById('responseBody').textContent;
            navigator.clipboard.writeText(text).then(() => {
                alert('Response copied to clipboard!');
            });
        }

        // Sidebar search filter
        document.getElementById('sidebarSearch').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.module-group').forEach(function (grp) {
                const label = grp.querySelector('button span:nth-child(2)').textContent.toLowerCase();
                const subs  = grp.querySelectorAll('[data-sub]');
                let match   = label.includes(q);
                if (!match) {
                    grp.querySelectorAll('.sub-item').forEach(function (s) {
                        s.style.display = s.textContent.toLowerCase().includes(q) ? '' : 'none';
                        if (s.style.display !== 'none') match = true;
                    });
                }
                grp.style.display = match ? '' : 'none';
                if (match && q) {
                    const subsEl = grp.querySelector('[id^="subs-"]');
                    if (subsEl) { subsEl.style.maxHeight = '2000px'; subsEl.style.opacity = '1'; }
                }
            });
        });

        // Persist form data after submission
        function getStorageKey() {
            return 'bpjs_form_' + <?= json_encode($selectedMod) ?> + '_' + <?= json_encode($selectedSub) ?>;
        }

        function saveFormData() {
            const formData = {};
            const inputs = document.querySelectorAll('input[type="text"], input[type="number"], textarea');
            inputs.forEach(function(input) {
                if (input.name) {
                    formData[input.name] = input.value;
                }
            });
            localStorage.setItem(getStorageKey(), JSON.stringify(formData));
        }

        function restoreFormData() {
            const saved = localStorage.getItem(getStorageKey());
            if (!saved) return;
            const formData = JSON.parse(saved);
            Object.keys(formData).forEach(function(name) {
                const input = document.querySelector('[name="' + name + '"]');
                if (input) {
                    input.value = formData[name];
                }
            });
        }

        // Save form data on input/change
        document.addEventListener('input', function(e) {
            if (e.target.closest('form')) {
                saveFormData();
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.closest('form')) {
                saveFormData();
            }
        });

        // Restore form data on page load
        document.addEventListener('DOMContentLoaded', restoreFormData);

        // Theme toggle functionality
        function toggleTheme() {
            const isDark = document.body.classList.contains('dark-theme');
            const newTheme = isDark ? 'light' : 'dark';
            
            // Set cookie for 30 days
            const d = new Date();
            d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();
            document.cookie = "bpjs_theme=" + newTheme + ";" + expires + ";path=/";
            
            applyTheme(newTheme);
        }

        function applyTheme(theme) {
            const body = document.getElementById('body');
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            if (theme === 'light') {
                body.classList.remove('dark-theme');
                body.classList.add('light-theme');
                body.classList.remove('bg-slate-900', 'text-slate-200');
                body.classList.add('bg-slate-100', 'text-slate-800');
                themeIcon.textContent = '☀️';
                themeText.textContent = 'Light';
            } else {
                body.classList.remove('light-theme');
                body.classList.add('dark-theme');
                body.classList.remove('bg-slate-100', 'text-slate-800');
                body.classList.add('bg-slate-900', 'text-slate-200');
                themeIcon.textContent = '🌙';
                themeText.textContent = 'Dark';
            }
        }

        // Check for saved theme preference
        function getSavedTheme() {
            const cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = cookies[i].trim();
                var parts = cookie.split('=');
                if (parts[0] === 'bpjs_theme') {
                    return parts[1];
                }
            }
            return 'dark'; // Default to dark
        }

        // Apply saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = getSavedTheme();
            applyTheme(savedTheme);
        });
    </script>
</body>
</html>
