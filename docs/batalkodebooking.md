# Tools: Batal Antrean BPJS (Batch Batal Kode Booking)

Skrip utilitas untuk melakukan pembatalan antrean BPJS secara massal (*batch cancellation*) berdasarkan daftar kode booking yang diekstrak dari format teks peringatan/notifikasi HFIS.

---

## Deskripsi & Fungsi

Tools ini berguna ketika Rumah Sakit atau Faskes perlu membatalkan banyak antrean sekaligus (misalnya dokter berhalangan hadir atau terjadi perubahan jadwal praktek massal).

- **Target Endpoint BPJS:** `{BASE_URL}/antreanrs/antrean/batal`
- **Method:** `POST`
- **Header:**
  - `Content-Type: application/json`
  - `x-cons-id: {CONS_ID}`
  - `x-timestamp: {TIMESTAMP}`
  - `x-signature: {SIGNATURE}`
  - `user_key: {USER_KEY}`

### Format Input Kode Booking
Skrip ini mendukung input teks langsung yang berisi pola:
```text
202512190047 (2026-01-12 18:00-21:00)
202512190048 (2026-01-12 18:00-21:00)
```
Skrip menggunakan Regular Expression `'/(\d{12})\s+\(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}-\d{2}:\d{2}\)/'` untuk mengekstrak 12 digit kode booking secara otomatis.

---

## Konfigurasi Kredensial (.env)

Pastikan file `.env` di direktori aplikasi memiliki variabel berikut:

```env
CONS_ID=your_cons_id
USER_KEY=your_user_key
SECRET_KEY=your_secret_key
```

---

## Kode Implementasi (PHP & HTML)

```php
<?php
// =====================================
// BATAL ANTREAN KE BPJS DENGAN SIGNATURE HMAC-SHA256
// =====================================

$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ===============================
    // LOAD ENV
    // ===============================
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        die(json_encode(['error'=>'.env file not found']));
    }

    $env = [];
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$key, $val] = explode('=', $line, 2);
        $env[trim($key)] = trim($val);
    }

    $CONS_ID    = $env['CONS_ID']    ?? 'isi_cons_id';
    $USER_KEY   = $env['USER_KEY']   ?? 'isi_user_key';
    $SECRET_KEY = $env['SECRET_KEY'] ?? 'isi_secret_key';

    // ===============================
    // URL API BPJS
    // ===============================
    $baseUrl = 'https://apijkn.bpjs-kesehatan.go.id';
    $service = 'antreanrs/antrean/batal';
    $endpoint = rtrim($baseUrl,'/') . '/' . ltrim($service,'/');

    // ===============================
    // AMBIL INPUT DARI FORM
    // ===============================
    $inputBatal = $_POST['inputBatal'] ?? '';
    $alasanBatal = $_POST['alasanBatal'] ?? 'Terjadi perubahan jadwal dokter, silahkan daftar kembali';

    // Ambil semua kodebooking dari format string
    preg_match_all('/(\d{12})\s+\(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}-\d{2}:\d{2}\)/', $inputBatal, $matches);
    $kodebookings = $matches[1]; // ambil group pertama

    if(!empty($kodebookings)){
        // ===============================
        // FUNGSI GENERATE SIGNATURE HMAC-SHA256
        // ===============================
        function generateSignature($consId, $secretKey){
            date_default_timezone_set('UTC');
            $tStamp = strval(time());
            $sig = hash_hmac('sha256', $consId . "&" . $tStamp, $secretKey, true);
            $encodedSig = base64_encode($sig);
            return [$encodedSig, $tStamp];
        }

        // ===============================
        // FUNGSI KIRIM POST KE BPJS
        // ===============================
        function batalkanAntreanBPJS($endpoint, $kodebooking, $keterangan, $consId, $userKey, $secretKey){
            list($signature, $tStamp) = generateSignature($consId, $secretKey);

            $payload = json_encode([
                'kodebooking' => $kodebooking,
                'keterangan' => $keterangan
            ]);

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-cons-id: '.$consId,
                'x-timestamp: '.$tStamp,
                'x-signature: '.$signature,
                'user_key: '.$userKey
            ]);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if($err){
                return ['kodebooking'=>$kodebooking,'status'=>'ERROR','message'=>$err];
            }
            if($httpCode !== 200){
                return ['kodebooking'=>$kodebooking,'status'=>'ERROR','http_code'=>$httpCode,'response'=>$response];
            }

            return json_decode($response,true);
        }

        // ===============================
        // KIRIM SEMUA ANTREAN
        // ===============================
        foreach($kodebookings as $kode){
            $results[] = batalkanAntreanBPJS($endpoint, $kode, $alasanBatal, $CONS_ID, $USER_KEY, $SECRET_KEY);
        }
    } else {
        $results[] = ['status'=>'ERROR','message'=>'Tidak ada kodebooking ditemukan'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Batal Antrean BPJS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Batal Antrean BPJS</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="inputBatal" class="form-label">Masukkan Kodebooking (format panjang)</label>
            <textarea name="inputBatal" id="inputBatal" rows="6" class="form-control" placeholder="202512190047 (2026-01-12 18:00-21:00), ..."><?= htmlspecialchars($_POST['inputBatal'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="alasanBatal" class="form-label">Alasan Pembatalan</label>
            <textarea name="alasanBatal" id="alasanBatal" rows="3" class="form-control"><?= htmlspecialchars($_POST['alasanBatal'] ?? 'Terjadi perubahan jadwal dokter, silahkan daftar kembali') ?></textarea>
        </div>

        <button type="submit" class="btn btn-danger">Batalkan Semua Antrean</button>
    </form>

    <?php if(!empty($results)): ?>
        <div class="mt-5">
            <h3>Hasil Pembatalan</h3>
            <table class="table table-bordered table-striped table-responsive">
                <thead class="table-dark">
                    <tr>
                        <th>Kodebooking</th>
                        <th>Status</th>
                        <th>Detail Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $res): ?>
                        <tr>
                            <td><?= htmlspecialchars($res['kodebooking'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($res['status'] ?? '-') ?></td>
                            <td><pre><?= htmlspecialchars(json_encode($res, JSON_PRETTY_PRINT)) ?></pre></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```
