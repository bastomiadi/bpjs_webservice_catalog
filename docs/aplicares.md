# Modul Aplicares

Dokumentasi Web Service Aplicares BPJS Kesehatan untuk integrasi data ketersediaan tempat tidur rumah sakit.

## Base URL

| Environment | Base URL |
|---|---|
| **Production (V1)** | `https://apijkn.bpjs-kesehatan.go.id/aplicaresws/rest` |
| **Production (V2)** | `https://new-apijkn.bpjs-kesehatan.go.id/aplicaresws/rest` |
| **Development** | *Menggunakan URL Production (tidak tersedia environment dev khusus)* |

## HTTP Header

| Header | Tipe | Keterangan |
|---|---|---|
| `x-cons-id` | String | Consumer ID dari BPJS Kesehatan |
| `x-timestamp` | String | Timestamp waktu UTC dalam detik |
| `x-signature` | String | Signature HMAC-SHA256 dari `cons_id + "&" + timestamp` dengan Secret Key |
| `user_key` | String | User Key web service Aplicares |
| `Content-Type` | String | `application/json` |

---

## Daftar Endpoint

1. [Referensi Kamar](#1-referensi-kamar)
2. [Update Ketersediaan Tempat Tidur](#2-update-ketersediaan-tempat-tidur)
3. [Ruangan Baru (Tambah Ruangan)](#3-ruangan-baru-tambah-ruangan)
4. [Ketersediaan Kamar RS (Lihat Data)](#4-ketersediaan-kamar-rs-lihat-data)
5. [Hapus Ruangan](#5-hapus-ruangan)

---

### 1. Referensi Kamar

Melihat daftar referensi kelas kamar rawat inap yang terdaftar di BPJS Kesehatan.

- **URL:** `{BASE_URL}/ref/kelas`
- **Method:** `GET`
- **Format:** `JSON`
- **Content-Type:** `application/json`

#### Response Body
```json
{
  "metadata": {
    "code": 1,
    "message": "OK",
    "totalitems": 16
  },
  "response": {
    "list": [
      {
        "kodekelas": "NON",
        "namakelas": "-"
      },
      {
        "kodekelas": "VVP",
        "namakelas": "VVIP"
      }
    ]
  }
}
```

---

### 2. Update Ketersediaan Tempat Tidur

Melakukan pembaruan jumlah ketersediaan tempat tidur pada ruangan yang sudah terdaftar.

- **URL:** `{BASE_URL}/bed/update/{kodeppk}`
- **Method:** `POST`
- **Format:** `JSON`
- **Content-Type:** `application/json`

#### Parameter Path
| Parameter | Keterangan |
|---|---|
| `kodeppk` | Kode PPK / Faskes Rumah Sakit (contoh: 0116R001) |

#### Parameter Body
| Field | Tipe | Keterangan |
|---|---|---|
| `kodekelas` | string | Kode kelas ruang rawat sesuai mapping BPJS Kesehatan |
| `koderuang` | string | Kode ruangan Rumah Sakit |
| `namaruang` | string | Nama ruang rawat Rumah Sakit |
| `kapasitas` | string/int | Kapasitas total tempat tidur di ruangan |
| `tersedia` | string/int | Jumlah tempat tidur yang kosong / dapat ditempati pasien baru |
| `tersediapria` | string/int | *Opsional*: Jumlah tempat tidur kosong untuk pasien laki-laki |
| `tersediawanita` | string/int | *Opsional*: Jumlah tempat tidur kosong untuk pasien perempuan |
| `tersediapriawanita` | string/int | *Opsional*: Jumlah tempat tidur kosong untuk laki-laki atau perempuan |

#### Request Body
```json
{
  "kodekelas": "VIP",
  "koderuang": "RG01",
  "namaruang": "Ruang Anggrek VIP",
  "kapasitas": "20",
  "tersedia": "10",
  "tersediapria": "0",
  "tersediawanita": "0",
  "tersediapriawanita": "0"
}
```

---

### 3. Ruangan Baru (Tambah Ruangan)

Menambahkan data ruangan baru ke dalam sistem Aplicares BPJS Kesehatan.

- **URL:** `{BASE_URL}/bed/create/{kodeppk}`
- **Method:** `POST`
- **Format:** `JSON`
- **Content-Type:** `application/json`

#### Parameter Path
| Parameter | Keterangan |
|---|---|
| `kodeppk` | Kode PPK / Faskes Rumah Sakit |

#### Parameter Body
| Field | Tipe | Keterangan |
|---|---|---|
| `kodekelas` | string | Kode kelas ruang rawat sesuai mapping BPJS Kesehatan |
| `koderuang` | string | Kode ruangan Rumah Sakit |
| `namaruang` | string | Nama ruang rawat Rumah Sakit |
| `kapasitas` | string/int | Kapasitas total tempat tidur di ruangan |
| `tersedia` | string/int | Jumlah tempat tidur yang kosong / dapat ditempati pasien baru |
| `tersediapria` | string/int | *Opsional*: Jumlah tempat tidur kosong untuk pasien laki-laki |
| `tersediawanita` | string/int | *Opsional*: Jumlah tempat tidur kosong untuk pasien perempuan |
| `tersediapriawanita` | string/int | *Opsional*: Jumlah tempat tidur kosong untuk laki-laki atau perempuan |

#### Request Body
```json
{
  "kodekelas": "VIP",
  "koderuang": "RG01",
  "namaruang": "Ruang Anggrek VIP",
  "kapasitas": "20",
  "tersedia": "10",
  "tersediapria": "0",
  "tersediawanita": "0",
  "tersediapriawanita": "0"
}
```

---

### 4. Ketersediaan Kamar RS (Lihat Data)

Melihat data ketersediaan kamar yang telah di-update oleh Rumah Sakit dengan dukungan paging.

- **URL:** `{BASE_URL}/bed/read/{kodeppk}/{start}/{limit}`
- **Method:** `GET`
- **Format:** `JSON`
- **Accept:** `application/json`

#### Parameter Path
| Parameter | Keterangan |
|---|---|
| `kodeppk` | Kode PPK / Faskes Rumah Sakit |
| `start` | Posisi awal baris data (dimulai dari `1`) |
| `limit` | Jumlah baris data yang ditampilkan per halaman |

*Contoh:* Untuk menampilkan data dari baris ke-1 sampai ke-10, isi `start = 1` dan `limit = 10`.

---

### 5. Hapus Ruangan

Menghapus data ruangan tempat tidur dari sistem Aplicares BPJS Kesehatan.

- **URL:** `{BASE_URL}/bed/delete/{kodeppk}`
- **Method:** `POST`
- **Format:** `JSON`
- **Content-Type:** `application/json`

#### Parameter Path
| Parameter | Keterangan |
|---|---|
| `kodeppk` | Kode PPK / Faskes Rumah Sakit |

#### Parameter Body
| Field | Tipe | Keterangan |
|---|---|---|
| `kodekelas` | string | Kode kelas ruang rawat sesuai mapping BPJS Kesehatan |
| `koderuang` | string | Kode ruangan Rumah Sakit |

#### Request Body
```json
{
  "kodekelas": "VIP",
  "koderuang": "RG01"
}
```
