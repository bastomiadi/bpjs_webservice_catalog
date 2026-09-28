# Modul i-Care JKN

Dokumentasi Web Service i-Care JKN untuk integrasi riwayat pelayanan pasien (FKRTL dan FKTP).

## Base URL

| Environment | Base URL |
|---|---|
| **Production (V1)** | `https://apijkn.bpjs-kesehatan.go.id/ihs` |
| **Production (V2)** | `https://new-apijkn.bpjs-kesehatan.go.id/ihs` |
| **Development** | `https://apijkn-dev.bpjs-kesehatan.go.id/ihs_dev` |

## HTTP Header

Setiap request ke web service i-Care membutuhkan header autentikasi BPJS standar:

| Header | Tipe | Keterangan |
|---|---|---|
| `x-cons-id` | String | Consumer ID dari BPJS Kesehatan |
| `x-timestamp` | String | Timestamp waktu UTC dalam detik |
| `x-signature` | String | Signature HMAC-SHA256 dari `cons_id + "&" + timestamp` dengan Secret Key |
| `user_key` | String | User Key web service i-Care |
| `Content-Type` | String | `application/json` |

---

## Daftar Endpoint

1. [FKRTL - Validasi & Riwayat Pelayanan](#1-fkrtl---validasi--riwayat-pelayanan)
2. [FKTP - Validasi & Riwayat Pelayanan](#2-fktp---validasi--riwayat-pelayanan)

---

### 1. FKRTL - Validasi & Riwayat Pelayanan

Digunakan oleh Fasilitas Kesehatan Rujukan Tingkat Lanjut (Rumah Sakit) untuk mengakses data riwayat pelayanan pasien.

- **URL:** `{BASE_URL}/api/rs/validate`
- **Method:** `POST`
- **Format:** `JSON`
- **Content-Type:** `application/json`

#### Request Body (Contoh)
```json
{
  "param": "2200009338321",
  "kodedokter": 11111
}
```

#### Request Body (Format Parameter)
```json
{
  "param": "{nomorkartu}",
  "kodedokter": {kodedokter}
}
```

#### Parameter Request
| Field | Tipe | Keterangan |
|---|---|---|
| `param` | string | Nomor Kartu BPJS Peserta |
| `kodedokter` | integer | Kode Dokter yang terdaftar di BPJS |

---

### 2. FKTP - Validasi & Riwayat Pelayanan

Digunakan oleh Fasilitas Kesehatan Tingkat Pertama (Puskesmas / Klinik / Dokter Praktik Perorangan) untuk mengakses data riwayat pelayanan pasien.

- **URL:** `{BASE_URL}/api/pcare/validate`
- **Method:** `POST`
- **Format:** `JSON`
- **Content-Type:** `application/json`

#### Request Body (Contoh)
```json
{
  "param": "2200009338321"
}
```

#### Request Body (Format Parameter)
```json
{
  "param": "{nomorkartu}"
}
```

#### Parameter Request
| Field | Tipe | Keterangan |
|---|---|---|
| `param` | string | Nomor Kartu BPJS Peserta |
