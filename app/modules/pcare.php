<?php
return [
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
    ];