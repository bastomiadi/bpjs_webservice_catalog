# Panduan Pembuatan Signature & Autentikasi Web Service BPJS (PCare)

Dokumentasi pembuatan Header Autentikasi dan Signature HMAC-SHA256 untuk mengakses Web Service Primary Care (PCare) BPJS Kesehatan.

---

## HTTP Header Yang Dibutuhkan

Secara umum, hampir setiap pemanggilan web-service harus mencantumkan beberapa variabel pada HTTP Header untuk identifikasi dan validasi:

| No | Nama Header | Contoh Nilai | Keterangan |
|---|---|---|---|
| 1 | `X-cons-id` | `743627386` | Consumer ID dari BPJS Kesehatan |
| 2 | `X-timestamp` | `1616559330` | Generated unix-based timestamp (UTC) |
| 3 | `X-signature` | `DogC5UiQurNcigrBdQ3QN5oYvXeUF5E82I/LHUcI9v0=` | Generated signature dengan pola HMAC-SHA256 |
| 4 | `X-authorization` | `MDkwMzA0MDI6UXdlcnR5MSE6MDk1` | Generated authorization dengan pola Base64 |
| 5 | `user_key` | `d795b04f4a72d74fae727be9da0xxxxx` | User key untuk akses web service |

---

## Penjelasan Header

1. **`X-cons-id`**: Merupakan kode consumer (pengakses web-service). Kode ini diberikan oleh BPJS Kesehatan saat registrasi.
2. **`X-timestamp`**: Merupakan waktu yang di-generate oleh client saat memanggil service. Format waktu berupa angka *unix timestamp* dalam detik berbasis zona waktu UTC.
   - Rumus: `(local time in UTC timezone in seconds) - (1970-01-01 in seconds)`.
3. **`X-signature`**: Merupakan hasil enkripsi HMAC-SHA256 yang dibuat oleh client menggunakan `Consumer Secret` sebagai key.
4. **`X-authorization`**: Merupakan kombinasi dari `username`, `password`, dan `kdAplikasi` PCare yang di-encode menggunakan Base64:
   ```
   username      : usernamePcare
   password      : passwordPcare
   kdAplikasi    : 095
   Authorization : Base64(username + ":" + password + ":" + kdAplikasi)
   ```
5. **`user_key`**: Merupakan key unik untuk otorisasi akses web service. Setiap service consumer memiliki `user_key` masing-masing.

---

## Kredensial Akses

Untuk dapat mengakses web service BPJS Kesehatan, service consumer akan memperoleh:
- **Consumer ID (`cons_id`)**: Dikirimkan melalui header `X-cons-id`.
- **Consumer Secret (`secret_key`)**: **Hanya disimpan oleh service consumer** (tidak dikirim ke server web-service) untuk menjaga keamanan. Secret key ini digunakan untuk men-generate `X-signature`.

---

## Alur Pembentukan Signature HMAC-SHA256

1. Gabungkan `consumerID` dan `timestamp` dengan karakter pemisah `&`:
   ```
   data = consumerID + "&" + timestamp
   ```
2. Hitung HMAC-SHA256 dengan data tersebut menggunakan `consumerSecret` sebagai key (format raw binary).
3. Encode hasil hash biner tersebut ke format **Base64**.

### Contoh Simulasi:
```text
consumerID     : 1234
consumerSecret : pwd
timestamp      : 433223232

data           : 1234&433223232
key            : pwd
Signature      : HMAC-SHA256(data, key) -> Base64
```

---

## Contoh Kode Implementasi (PHP)

```php
<?php 
$consId = "743627386";
$secretKey = "secretkey";
$userKey = "d795b04f4a72d74fae727be9da0xxxxx";
$usernamePcare = "usernamePcare";
$passwordPcare = "passwordPcare";
$kdAplikasi = "095";

// 1. Generate Timestamp (UTC)
date_default_timezone_set('UTC');
$tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));

// 2. Generate HMAC-SHA256 Signature
$signature = hash_hmac('sha256', $consId . "&" . $tStamp, $secretKey, true);
$encodedSignature = base64_encode($signature);

// 3. Generate Authorization Header (PCare)
$authString = $usernamePcare . ":" . $passwordPcare . ":" . $kdAplikasi;
$encodedAuth = base64_encode($authString);

// Output Headers
echo "X-cons-id: " . $consId . "\n";
echo "X-timestamp: " . $tStamp . "\n";
echo "X-signature: " . $encodedSignature . "\n";
echo "X-authorization: Basic " . $encodedAuth . "\n";
echo "user_key: " . $userKey . "\n";
?>
```
