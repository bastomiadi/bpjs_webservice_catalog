# Modul PCare (Primary Care)

Dokumentasi Web Service BPJS Kesehatan – Modul PCare (Primary Care).

## Base URL

| Environment | Base URL |
|---|---|
| **Production (V1)** | `https://apijkn.bpjs-kesehatan.go.id/pcare-rest` |
| **Production (V2)** | `https://new-apijkn.bpjs-kesehatan.go.id/pcare-rest` |
| **Development** | `https://apijkn-dev.bpjs-kesehatan.go.id/pcare-rest-dev` |

## HTTP Header Standar

| Header | Tipe | Keterangan |
|---|---|---|
| `X-cons-id` | String | Consumer ID dari BPJS Kesehatan |
| `X-timestamp` | String | Timestamp waktu UTC dalam detik |
| `X-signature` | String | Signature HMAC-SHA256 dari cons_id & timestamp dengan secret key |
| `X-authorization` | String | Basic Base64(username:password:kdAplikasi) untuk PCare |
| `user_key` | String | User key akses PCare BPJS |

---

## Daftar Endpoint

- **Diagnosa**
  - [Get Diagnosa](#get-diagnosa)
- **Dokter**
  - [Get Dokter](#get-dokter)
- **Kelompok**
  - [Get Club Prolanis](#get-club-prolanis)
  - [Get Kegiatan Kelompok](#get-kegiatan-kelompok)
  - [Get Peserta Kegiatan Kelompok](#get-peserta-kegiatan-kelompok)
  - [Add Kegiatan Kelompok](#add-kegiatan-kelompok)
  - [Add Peserta Kegiatan Kelompok](#add-peserta-kegiatan-kelompok)
  - [Delete Kegiatan Kelompok](#delete-kegiatan-kelompok)
  - [Delete Peserta Kegiatan Kelompok](#delete-peserta-kegiatan-kelompok)
- **Kesadaran**
  - [Get Kesadaran](#get-kesadaran)
- **Kunjungan**
  - [Get Rujukan](#get-rujukan)
  - [Get Riwayat Kunjungan](#get-riwayat-kunjungan)
  - [Add Kunjungan Update](#add-kunjungan-update)
  - [Edit Kunjungan Update](#edit-kunjungan-update)
  - [Delete Kunjungan](#delete-kunjungan)
- **MCU**
  - [Get MCU](#get-mcu)
  - [Add MCU](#add-mcu)
  - [Edit MCU](#edit-mcu)
  - [Delete MCU](#delete-mcu)
- **Obat**
  - [Get DPHO](#get-dpho)
  - [Get DPHO by KDPPK New](#get-dpho-by-kdppk-new)
  - [Get Obat by Kunjungan](#get-obat-by-kunjungan)
  - [Add Obat](#add-obat)
  - [Delete Obat](#delete-obat)
- **Pendaftaran**
  - [Get Pendaftaran by Nomor Urut](#get-pendaftaran-by-nomor-urut)
  - [Get Pendaftaran Provider](#get-pendaftaran-provider)
  - [Add Pendaftaran](#add-pendaftaran)
  - [Delete Pendaftaran](#delete-pendaftaran)
- **Peserta**
  - [Get Peserta](#get-peserta)
  - [Get Peserta by Jenis Kartu](#get-peserta-by-jenis-kartu)
- **Poli**
  - [Get Poli FKTP](#get-poli-fktp)
- **Provider**
  - [Get Provider Rayonisasi](#get-provider-rayonisasi)
- **Spesialis**
  - [Get Referensi Spesialis](#get-referensi-spesialis)
  - [Get Referensi Sub Spesialis](#get-referensi-sub-spesialis)
  - [Get Referensi Sarana](#get-referensi-sarana)
  - [Get Referensi Khusus](#get-referensi-khusus)
  - [Get Faskes Rujukan Sub Spesialis](#get-faskes-rujukan-sub-spesialis)
  - [Get Faskes Rujukan Khusus THALASEMIA dan HEMOFILI](#get-faskes-rujukan-khusus-thalasemia-dan-hemofili)
- **Status Pulang**
  - [Get Status Pulang](#get-status-pulang)
- **Tindakan**
  - [Get Tindakan by Kunjungan](#get-tindakan-by-kunjungan)
  - [Get Referensi Tindakan](#get-referensi-tindakan)
  - [Add Tindakan](#add-tindakan)
  - [Edit Tindakan](#edit-tindakan)
  - [Delete Tindakan](#delete-tindakan)
- **Alergi**
  - [Get Alergi New](#get-alergi-new)
- **Prognosa**
  - [Get Prognosa New](#get-prognosa-new)
- **Skrining**
  - [Get Skrining Riwayat Kesehatan by Penyakit New](#get-skrining-riwayat-kesehatan-by-penyakit-new)
  - [Get Detail Peserta Skrining Riwayat Kesehatan New](#get-detail-peserta-skrining-riwayat-kesehatan-new)
  - [Get Data Prolanis Diabetes Mellitus New](#get-data-prolanis-diabetes-mellitus-new)
  - [Get Data Prolanis Hipertensi New](#get-data-prolanis-hipertensi-new)

---


## Diagnosa


---

### Get Diagnosa

- **URL:** `{BASE URL}/diagnosa/{Parameter 1}/{Parameter 2}/{Parameter 3}`

**Fungsi:** Get Data Diagnosa

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode atau nama diagnosa
- **Parameter 2:** Row data awal yang akan ditampilkan
- **Parameter 3:** Limit jumlah data yang akan ditampilkan
```json
{
  "response": {
    "count": 33,
    "list": [
      {
        "kdDiag": "A001",
        "nmDiag": "Cholera due to vibrio cholerae 01, biovar eltor",
        "nonSpesialis": false
      },
      {
        "kdDiag": "B001",
        "nmDiag": "Herpesviral vesicular dermatitis",
        "nonSpesialis": true
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Dokter


---

### Get Dokter

- **URL:** `{BASE URL}/dokter/{Parameter 1}/{Parameter 2}`

**Fungsi:** Get Data Dokter

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Row data awal yang akan ditampilkan
- **Parameter 2:** Limit jumlah data yang akan ditampilkan
```json
{
  "response": {
    "count": 19,
    "list": [
      {
        "kdDokter": "001",
        "nmDokter": "dr..."
      },
      {
        "kdDokter": "002",
        "nmDokter": "dr. Muna Hasnita Harahap"
      },
      {
        "kdDokter": "003",
        "nmDokter": "dr. fauzi"
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Kelompok


---

### Get Club Prolanis

- **URL:** `{BASE URL}/kelompok/club/{Parameter 1}`

**Fungsi:** Get Data Club Prolanis

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode Jenis Kelompok => 01 : Diabetes Melitus, 02 : Hipertensi
```json
{
  "response": {
    "count": 2,
    "list": [
      {
        "clubId": 33,
        "jnsKelompok": {
          "kdProgram": "01",
          "nmProgram": "Diabetes Melitus"
        },
        "tglMulai": "23-08-2015",
        "tglAkhir": null,
        "alamat": "alamat club 1",
        "nama": "Club DM satu",
        "ketua_noHP": "085720127801",
        "ketua_nama": "Bambang"
      },
      {
        "clubId": 34,
        "jnsKelompok": {
          "kdProgram": "01",
          "nmProgram": "Diabetes Melitus"
        },
        "tglMulai": "23-08-2015",
        "tglAkhir": null,
        "alamat": "alamat club dm 2",
        "nama": "club DM dua",
        "ketua_noHP": "085720293132",
        "ketua_nama": "Susi"
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Get Kegiatan Kelompok

- **URL:** `{BASE URL}/kelompok/kegiatan/{Parameter 1}`

**Fungsi:** Get Data Kegiatan Kelompok

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Bulan, format => dd-mm-yyyy
```json
{
  "response": {
    "count": 2,
    "list": [
      {
        "eduId": "16010000003",
        "clubProl": {
          "clubId": 36,
          "jnsKelompok": {
            "kdProgram": "03",
            "nmProgram": "Asthma"
          },
          "tglMulai": "30-01-2016",
          "tglAkhir": null,
          "alamat": "Alamat asma satu",
          "nama": "Asthma satu",
          "ketua_noHP": "083",
          "ketua_nama": "ketua"
        },
        "tglPelayanan": "30-01-2016",
        "kegiatan": {
          "nama": "Senam",
          "kode": "01"
        },
        "kelompok": {
          "nama": "Asthma",
          "kode": "03"
        },
        "materi": "senam asthma satu",
        "pembicara": "pembicara asthma satu",
        "lokasi": "lokasi",
        "keterangan": "ket asma",
        "biaya": 30000
      },
      {
        "eduId": "16010000001",
        "clubProl": {
          "clubId": 34,
          "jnsKelompok": {
            "kdProgram": "01",
            "nmProgram": "Diabetes Melitus"
          },
          "tglMulai": "23-08-2015",
          "tglAkhir": null,
          "alamat": "alamat club dm 2",
          "nama": "club DM dua",
          "ketua_noHP": "085720293132",
          "ketua_nama": "erna"
        },
        "tglPelayanan": "29-01-2016",
        "kegiatan": {
          "nama": "Penyuluhan",
          "kode": "10"
        },
        "kelompok": {
          "nama": "Diabetes Melitus",
          "kode": "01"
        },
        "materi": "d",
        "pembicara": "d",
        "lokasi": "d",
        "keterangan": "",
        "biaya": 0
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Get Peserta Kegiatan Kelompok

- **URL:** `{BASE URL}/kelompok/peserta/{Parameter 1}`

**Fungsi:** Get Data Peserta Kegiatan Kelompok

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** eduId
```json
{
  "response": {
    "count": 2,
    "list": [
      {
        "eduId": "16020000001",
        "peserta": {
          "noKartu": "0001101521158",
          "nama": "MULIANA",
          "hubunganKeluarga": "Peserta",
          "sex": "L",
          "tglLahir": "10-05-1975",
          "tglMulaiAktif": "27-01-1995",
          "tglAkhirBerlaku": "10-05-2075",
          "kdProviderPst": {
            "kdProvider": "0114U163",
            "nmProvider": "cemput jst"
          },
          "kdProviderGigi": {
            "kdProvider": null,
            "nmProvider": null
          },
          "jnsKelas": {
            "nama": "KELAS II",
            "kode": "2"
          },
          "jnsPeserta": {
            "nama": "TNI ANGKATAN DARAT",
            "kode": "5"
          },
          "golDarah": "0",
          "noHP": null,
          "noKTP": " ",
          "pstProl": "",
          "pstPrb": "",
          "aktif": true,
          "ketAktif": "AKTIF",
          "asuransi": {
            "kdAsuransi": null,
            "nmAsuransi": null,
            "noAsuransi": null,
            "cob": false
          },
          "tunggakan": 0
        }
      },
      {
        "eduId": "16020000001",
        "peserta": {
          "noKartu": "0001101615759",
          "nama": "TOGU ELIOT FERNANDO",
          "hubunganKeluarga": "Peserta",
          "sex": "L",
          "tglLahir": "11-11-1983",
          "tglMulaiAktif": "08-11-2003",
          "tglAkhirBerlaku": "11-11-2083",
          "kdProviderPst": {
            "kdProvider": "0114U163",
            "nmProvider": "cemput jst"
          },
          "kdProviderGigi": {
            "kdProvider": null,
            "nmProvider": null
          },
          "jnsKelas": {
            "nama": "KELAS II",
            "kode": "2"
          },
          "jnsPeserta": {
            "nama": "TNI ANGKATAN DARAT",
            "kode": "5"
          },
          "golDarah": "0",
          "noHP": null,
          "noKTP": " ",
          "pstProl": "",
          "pstPrb": "",
          "aktif": true,
          "ketAktif": "AKTIF",
          "asuransi": {
            "kdAsuransi": null,
            "nmAsuransi": null,
            "noAsuransi": null,
            "cob": false
          },
          "tunggakan": 0
        }
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Add Kegiatan Kelompok

- **URL:** `{BASE URL}/kelompok/kegiatan`

**Fungsi:** Add Data Kegiatan Kelompok

- **Method:** `POST`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
  "eduId": null,
  "clubId": 36,
  "tglPelayanan": "27-03-2016",
  "kdKegiatan": "01",
  "kdKelompok": "03",
  "materi": "materi",
  "pembicara": "pembicara",
  "lokasi": "lokasi",
  "keterangan": "keterangan",
  "biaya": 20000
}
```

> **Keterangan:**
> "kdKelompok":[{"kdProgram":"01", "nmProgram":"Diabetes Melitus"}, {"kdProgram":"02", "nmProgram":"Hipertensi"}]
> "kdKegiatan":[{"kdProgram":"01", "nmProgram":"Senam"}, {"kdProgram":"10", "nmProgram":"Penyuluhan"}, {"kdProgram":"11", "nmProgram":"Penyuluhan dan Senam"}]

---

### Add Peserta Kegiatan Kelompok

- **URL:** `{BASE URL}/kelompok/peserta`

**Fungsi:** Add Data Peserta Kegiatan Kelompok

- **Method:** `POST`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
  "eduId": "16030000009",
  "noKartu": "0001101615759"
}
```

> **Keterangan:**
> "kdKelompok":[{"kdProgram":"01", "nmProgram":"Diabetes Melitus"}, {"kdProgram":"02", "nmProgram":"Hipertensi"}]
> "kdKegiatan":[{"kdProgram":"01", "nmProgram":"Senam"}, {"kdProgram":"10", "nmProgram":"Penyuluhan"}, {"kdProgram":"11", "nmProgram":"Penyuluhan dan Senam"}]

---

### Delete Kegiatan Kelompok

- **URL:** `{BASE URL}/kelompok/kegiatan/{Parameter 1}`

**Fungsi:** Delete Data Kegiatan Kelompok

- **Method:** `DELETE`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** eduId
```json
{
  "response": null,
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Delete Peserta Kegiatan Kelompok

- **URL:** `{BASE URL}/kelompok/peserta/{Parameter 1}/{Parameter 2}`

**Fungsi:** Delete Data Peserta Kegiatan Kelompok

- **Method:** `DELETE`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** eduId
- **Parameter 2:** Nomor Kartu Peserta
```json
{
  "response": null,
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Kesadaran


---

### Get Kesadaran

- **URL:** `{BASE URL}/kesadaran`

**Fungsi:** Get Data Kesadaran

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

```json
{
  "response": {
    "count": 4,
    "list": [
      {
        "kdSadar": "01",
        "nmSadar": "Compos mentis"
      },
      {
        "kdSadar": "02",
        "nmSadar": "Somnolence"
      },
      {
        "kdSadar": "03",
        "nmSadar": "Sopor"
      },
      {
        "kdSadar": "04",
        "nmSadar": "Coma"
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Kunjungan


---

### Get Rujukan

- **URL:** `{BASE URL}/kunjungan/rujukan/{Parameter 1}`

**Fungsi:** Get Data Rujukan

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor Kunjungan
```json
{
  "response": {
    "noRujukan": "0114U1630316Y000003",
    "ppk": {
      "kdPPK": "0114U163",
      "nmPPK": "Klinik Cempaka Putih (JST)",
      "alamat": null,
      "kc": {
        "kdKC": "0901",
        "nmKC": "JAKARTA PUSAT",
        "alamat": null,
        "telp": null,
        "fax": null,
        "dati": {
          "kdProp": null,
          "kdDati": "0114",
          "nmDati": "KOTA JAKARTA PUSAT"
        },
        "kdKR": {
          "kdKR": "04",
          "nmKR": "REGIONAL   IV - JAKARTA",
          "alamat": null,
          "telp": null,
          "fax": null
        }
      }
    },
    "tglKunjungan": "31-07-2016",
    "poli": {
      "kdPoli": "UGD",
      "nmPoli": "Unit Gawat Darurat"
    },
    "nokaPst": "0001149957551",
    "nmPst": "HANY TJOA",
    "tglLahir": "16-06-1970",
    "pisa": "1",
    "ketPisa": "Peserta",
    "sex": "L",
    "diag1": {
      "kdDiag": "A01.1",
      "nmDiag": "Paratyphoid fever A"
    },
    "diag2": null,
    "diag3": null,
    "catatan": "",
    "dokter": {
      "kdDokter": "019",
      "nmDokter": "dr henny"
    },
    "tacc": {
      "nmTacc": null,
      "alasanTacc": null
    },
    "infoDenda": "Anda memasuki masa denda 45 hari jika melakukan rawat inap di FKRTL mulai tanggal : 01-08-2016 sampai tanggal : 15-09-2016"
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Get Riwayat Kunjungan

- **URL:** `{BASE URL}/kunjungan/peserta/{Parameter 1}`

**Fungsi:** Get Data Riwayat Kunjungan

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor kartu peserta
```json
{
  "response": {
    "count": 1,
    "list": [
      {
        "noKunjungan": "0114A0260815Y000003",
        "tglKunjungan": "25-08-2015",
        "providerPelayanan": {
          "kdProvider": "0114A026",
          "nmProvider": "IF RS THAMRIN"
        },
        "peserta": {
          "noKartu": "0000029247423",
          "nama": null,
          "hubunganKeluarga": "Peserta",
          "sex": null,
          "tglLahir": null,
          "tglMulaiAktif": null,
          "tglAkhirBerlaku": null,
          "kdPpkPst": null,
          "kdPpkGigi": null,
          "jnsKelas": null,
          "jnsPeserta": null,
          "golDarah": null,
          "noHP": null,
          "noKTP": null,
          "asuransi": null
        },
        "poli": {
          "kdPoli": "002",
          "nmPoli": "Gigi",
          "poliSakit": true
        },
        "progProlanis": {
          "kdProgram": "",
          "nmProgram": ""
        },
        "keluhan": "keluhan",
        "diagnosa1": {
          "kdDiag": "K296",
          "nmDiag": "Other gastritis",
          "nonSpesialis": false
        },
        "diagnosa2": {
          "kdDiag": null,
          "nmDiag": null,
          "nonSpesialis": false
        },
        "diagnosa3": {
          "kdDiag": null,
          "nmDiag": null,
          "nonSpesialis": false
        },
        "kesadaran": {
          "kdSadar": "01",
          "nmSadar": "Compos mentis"
        },
        "sistole": 0,
        "diastole": 0,
        "beratBadan": 0,
        "tinggiBadan": 0,
        "respRate": 0,
        "heartRate": 0,
        "catatan": "catatan",
        "rujukBalik": 0,
        "providerAsalRujuk": {
          "kdProvider": "",
          "nmProvider": null
        },
        "providerRujukLanjut": {
          "kdProvider": "00010003",
          "nmProvider": "POLIKLINIK POLRES LANGSA"
        },
        "pemFisikLain": "",
        "dokter": {
          "kdDokter": "002",
          "nmDokter": "Asmaul Husna"
        },
        "statusPulang": {
          "kdStatusPulang": "4",
          "nmStatusPulang": "Rujuk Lanjut"
        },
        "tkp": {
          "kdTkp": "10",
          "nmTkp": "RJTP"
        },
        "poliRujukInternal": {
          "kdPoli": null,
          "nmPoli": null,
          "poliSakit": true
        },
        "poliRujukLanjut": {
          "kdPoli": "TAK",
          "nmPoli": "TAK",
          "poliSakit": true
        },
        "tglPulang": "25-08-2015"
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Add Kunjungan Update

- **URL:** `{BASE URL}/kunjungan`

**Fungsi:** Add Data Kunjungan

- **Method:** `POST`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

Contoh Parameter Rujukan HEMODIALISA
```json
{
  "noKunjungan": null,
  "noKartu": "0000043678034",
  "tglDaftar": "13-08-2018",
  "kdPoli": null,
  "keluhan": "keluhan",
  "kdSadar": "01",
  "sistole": 0,
  "diastole": 0,
  "beratBadan": 0,
  "tinggiBadan": 0,
  "respRate": 0,
  "heartRate": 0,
  "lingkarPerut": 36,
  "kdStatusPulang": "4",
  "tglPulang": "19-05-2016",
  "kdDokter": "73229",
  "kdDiag1": "A01.0",
  "kdDiag2": null,
  "kdDiag3": null,
  "kdPoliRujukInternal": null,
  "rujukLanjut": {	
  	"tglEstRujuk":"02-10-2018",
    "kdppk": "0116R028",
    "subSpesialis": null,
    "khusus": {
      "kdKhusus": "HDL",
      "kdSubSpesialis": null,
      "catatan": "peserta sudah biasa hemodialisa"
    }
  },
  "kdTacc": 0,
  "alasanTacc": null,
  "anamnesa": "test anamnesa",
  "alergiMakan": "00",
  "alergiUdara": "00",
  "alergiObat": "00",
  "kdPrognosa": "01",
  "terapiObat": "test terapi obat",
  "terapiNonObat": "test terapi nonobat",
  "bmhp": "bmhp",
  "suhu": "36,4"
}
```

Contoh Parameter Rujukan Spesialis
```json
{
    "noKunjungan": null,
    "noKartu": "0000000114759",
    "tglDaftar": "26-03-2022",
    "kdPoli": "002",
    "keluhan": "keluhan",
    "kdSadar": "04",
    "sistole": 80,
    "diastole": 80,
    "beratBadan": 50,
    "tinggiBadan": 170,
    "respRate": 80,
    "heartRate": 80,
    "lingkarPerut": 36,
    "kdStatusPulang": "4",
    "tglPulang": "27-03-2022",
    "kdDokter": "179007",
    "kdDiag1": "O82",
    "kdDiag2": null,
    "kdDiag3": null,
    "kdPoliRujukInternal": null,
    "rujukLanjut": {
        "kdppk": "0112R016",
        "tglEstRujuk": "27-03-2022",
        "subSpesialis": {
            "kdSubSpesialis1": "16",
            "kdSarana": null
        },
        "khusus": null
    },
    "kdTacc": -1,
    "alasanTacc": null,
    "anamnesa": "test anamnesa",
    "alergiMakan": "00",
    "alergiUdara": "00",
    "alergiObat": "00",
    "kdPrognosa": "01",
    "terapiObat": "test terapi obat",
    "terapiNonObat": "test terapi nonobat",
    "bmhp": "bmhp",
    "suhu": "36,4"
}
```

> **Referensi TACC:**
```json
[
  { "kdTacc": "-1", "nmTacc": "Tanpa TACC", alasanTacc:[] },
  { "kdTacc": "1", "nmTacc": "Time", alasanTacc:["< 3 Hari", ">= 3 - 7 Hari", ">= 7 Hari"] },
  { "kdTacc": "2", "nmTacc": "Age", alasanTacc:["< 1 Bulan", ">= 1 Bulan s/d < 12 Bulan", ">= 1 Tahun s/d < 5 Tahun",">= 5 Tahun s/d < 12 Tahun", ">= 12 Tahun s/d < 55 Tahun", ">= 55 Tahun"]   },
  { "kdTacc": "3", "nmTacc": "Complication", alasanTacc:(format : kdDiagnosa + " - " + NamaDiagnosa, contoh : "A09 - Diarrhoea and gastroenteritis of presumed infectious origin")  },
  { "kdTacc": "4", "nmTacc": "Comorbidity", alasanTacc:["< 3 Hari", ">= 3 - 7 Hari", ">= 7 Hari"]  }
]
```

---

### Edit Kunjungan Update

- **URL:** `{BASE URL}/kunjungan`

**Fungsi:** Edit Data Kunjungan

- **Method:** `PUT`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
    "noKunjungan": "011200210322Y000012",
    "noKartu": "0000000114759",
    "keluhan": "keluhan",
    "kdSadar": "03",
    "sistole": 90,
    "diastole": 90,
    "beratBadan": 50,
    "tinggiBadan": 170,
    "respRate": 90,
    "heartRate": 90,
    "lingkarPerut": 36,
    
    "kdStatusPulang": "4",
    "tglPulang": "27-03-2022",
    "kdDokter": "179007",
    "kdDiag1": "B54",
    "kdDiag2": null,
    "kdDiag3": null,
    "kdPoliRujukInternal": null,
    "rujukLanjut": {
        "tglEstRujuk": "27-03-2022",
        "kdppk": "0112R016",
        "subSpesialis": null,
        "khusus": {
            "kdKhusus": "HDL",
            "kdSubSpesialis": null,
            "catatan": "peserta sudah biasa hemodialisa"
        }
    },
    "kdTacc": 1,
    "alasanTacc": "< 3 Hari",
    "anamnesa": "test anamnesa",
    "alergiMakan": "00",
    "alergiUdara": "00",
    "alergiObat": "00",
    "kdPrognosa": "01",
    "terapiObat": "test terapi obat",
    "terapiNonObat": "test terapi nonobat",
    "bmhp": "bmhp",
    "suhu": "36,4"
}
```

> **Referensi TACC:**
```json
[
  { "kdTacc": "-1", "nmTacc": "Tanpa TACC", alasanTacc:[] },
  { "kdTacc": "1", "nmTacc": "Time", alasanTacc:["< 3 Hari", ">= 3 - 7 Hari", ">= 7 Hari"] },
  { "kdTacc": "2", "nmTacc": "Age", alasanTacc:["< 1 Bulan", ">= 1 Bulan s/d < 12 Bulan", ">= 1 Tahun s/d < 5 Tahun",">= 5 Tahun s/d < 12 Tahun", ">= 12 Tahun s/d < 55 Tahun", ">= 55 Tahun"]   },
  { "kdTacc": "3", "nmTacc": "Complication", alasanTacc:(format : kdDiagnosa + " - " + NamaDiagnosa, contoh : "A09 - Diarrhoea and gastroenteritis of presumed infectious origin")  },
  { "kdTacc": "4", "nmTacc": "Comorbidity", alasanTacc:["< 3 Hari", ">= 3 - 7 Hari", ">= 7 Hari"]  }
]
```

---

### Delete Kunjungan

- **URL:** `{BASE URL}/kunjungan/{Parameter 1}`

**Fungsi:** Delete Data Kunjungan

- **Method:** `DELETE`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor Kunjungan
```json
{
  "response": null,
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## MCU


---

### Get MCU

- **URL:** `{BASE URL}/MCU/kunjungan/{Parameter 1}`

**Fungsi:** Get Data MCU

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor Kunjungan
```json
{
  "response": {
    "count": 1,
    "list": [
      {
        "kdMCU": 43,
        "noKunjungan": "0114U1630316Y000003",
        "kdProvider": "0114u163",
        "tglPelayanan": "22-03-2016",
        "tekananDarahSistole": 0,
        "tekananDarahDiastole": 0,
        "radiologiFoto": null,
        "darahRutinHemo": 0,
        "darahRutinLeu": 0,
        "darahRutinErit": 7,
        "darahRutinLaju": 0,
        "darahRutinHema": 0,
        "darahRutinTrom": 0,
        "lemakDarahHDL": 0,
        "lemakDarahLDL": 0,
        "lemakDarahChol": 0,
        "lemakDarahTrigli": 0,
        "gulaDarahSewaktu": 0,
        "gulaDarahPuasa": 0,
        "gulaDarahPostPrandial": 0,
        "gulaDarahHbA1c": 0,
        "fungsiHatiSGOT": 0,
        "fungsiHatiSGPT": 0,
        "fungsiHatiGamma": 0,
        "fungsiHatiProtKual": 0,
        "fungsiHatiAlbumin": 0,
        "fungsiGinjalCrea": 0,
        "fungsiGinjalUreum": 0,
        "fungsiGinjalAsam": 0,
        "fungsiJantungABI": 0,
        "fungsiJantungEKG": null,
        "fungsiJantungEcho": null,
        "funduskopi": null,
        "pemeriksaanLain": null,
        "keterangan": null
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Add MCU

- **URL:** `{BASE URL}/MCU`

**Fungsi:** Add Data MCU

- **Method:** `POST`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
  "kdMCU": 0,
  "noKunjungan": "0114U1630316Y000003",
  "kdProvider": "0114u163",
  "tglPelayanan": "22-03-2016",
  "tekananDarahSistole": 0,
  "tekananDarahDiastole": 0,
  "radiologiFoto": null,
  "darahRutinHemo": 0,
  "darahRutinLeu": 0,
  "darahRutinErit": 7,
  "darahRutinLaju": 0,
  "darahRutinHema": 0,
  "darahRutinTrom": 0,
  "lemakDarahHDL": 0,
  "lemakDarahLDL": 0,
  "lemakDarahChol": 0,
  "lemakDarahTrigli": 0,
  "gulaDarahSewaktu": 0,
  "gulaDarahPuasa": 0,
  "gulaDarahPostPrandial": 0,
  "gulaDarahHbA1c": 0,
  "fungsiHatiSGOT": 0,
  "fungsiHatiSGPT": 0,
  "fungsiHatiGamma": 0,
  "fungsiHatiProtKual": 0,
  "fungsiHatiAlbumin": 0,
  "fungsiGinjalCrea": 0,
  "fungsiGinjalUreum": 0,
  "fungsiGinjalAsam": 0,
  "fungsiJantungABI": 0,
  "fungsiJantungEKG": null,
  "fungsiJantungEcho": null,
  "funduskopi": null,
  "pemeriksaanLain": null,
  "keterangan": null
}
```


---

### Edit MCU

- **URL:** `{BASE URL}/MCU`

**Fungsi:** Edit Data MCU

- **Method:** `PUT`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
  "kdMCU": 44,
  "noKunjungan": "0114U1630316Y000003",
  "kdProvider": "1301U007",
  "tglPelayanan": "22-03-2016",
  "tekananDarahSistole": 20,
  "tekananDarahDiastole": 0,
  "radiologiFoto": null,
  "darahRutinHemo": 0,
  "darahRutinLeu": 0,
  "darahRutinErit": 0,
  "darahRutinLaju": 0,
  "darahRutinHema": 0,
  "darahRutinTrom": 0,
  "lemakDarahHDL": 0,
  "lemakDarahLDL": 0,
  "lemakDarahChol": 0,
  "lemakDarahTrigli": 0,
  "gulaDarahSewaktu": 0,
  "gulaDarahPuasa": 0,
  "gulaDarahPostPrandial": 0,
  "gulaDarahHbA1c": 0,
  "fungsiHatiSGOT": 0,
  "fungsiHatiSGPT": 0,
  "fungsiHatiGamma": 0,
  "fungsiHatiProtKual": 0,
  "fungsiHatiAlbumin": 0,
  "fungsiGinjalCrea": 0,
  "fungsiGinjalUreum": 0,
  "fungsiGinjalAsam": 0,
  "fungsiJantungABI": 0,
  "fungsiJantungEKG": null,
  "fungsiJantungEcho": null,
  "funduskopi": null,
  "pemeriksaanLain": null,
  "keterangan": null
}
```


---

### Delete MCU

- **URL:** `{BASE URL}/MCU/{Parameter 1}/kunjungan/{Parameter 2}`

**Fungsi:** Delete Data MCU

- **Method:** `DELETE`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode MCU
- **Parameter 2:** Nomor Kunjungan
```json
{
  "response": null,
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Obat


---

### Get DPHO

- **URL:** `{BASE URL}/obat/dpho/{Parameter 1}/{Parameter 2}/{Parameter 3}`

**Fungsi:** Get Data DPHO

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode atau nama DPHO
- **Parameter 2:** Row data awal yang akan ditampilkan
- **Parameter 3:** Limit jumlah data yang akan ditampilkan
```json
{
  "response": {
    "count": 100,
    "list": [
      {
        "kdObat": "130199999",
        "nmObat": "Kapsul Kosong Kapsul Apt kaps",
        "sedia": 1
      },
      {
        "kdObat": "130103595",
        "nmObat": "Canderin 16 mg Dexa tab",
        "sedia": 16
      },
      {
        "kdObat": "130103594",
        "nmObat": "Quinin 25 %, 2 ml Kifa amp",
        "sedia": 1
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Get DPHO by KDPPK New

- **URL:** `{BASE URL}/dpho/kdppk/{parameter1}/{parameter2}/{parameter3}/{parameter4}`

**Fungsi:** Untuk melihat stock obat berdasarkan apotek tujuan yang dipilih

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode PPK Apotek Tujuan
- **Parameter 2:** Nama Obat
- **Parameter 3:** Row data awal yang akan ditampilkan
- **Parameter 4:** Limit jumlah data yang akan ditampilkan
```json
{
    "response": {
        "count": 4,
        "list": [
            {
                "kdObat": "12250804924",
                "nmObat": "Akarbose",
                "sediaan": "tab 100 mg",
                "stok": 10
            },
            {
                "kdObat": "12250804925",
                "nmObat": "Akarbose",
                "sediaan": "tab 50 mg",
                "stok": 0
            },
            {
                "kdObat": "12250805037",
                "nmObat": "Indakaterol",
                "sediaan": "serb ih 150 mcg",
                "stok": 0
            },
            {
                "kdObat": "12250805038",
                "nmObat": "Indakaterol",
                "sediaan": "serb ih 300 mcg",
                "stok": 0
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


---

### Get Obat by Kunjungan

- **URL:** `{BASE URL}/obat/kunjungan/{Parameter 1}`

**Fungsi:** Get Data Obat by Kunjungan

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor Kunjungan
```json
{
  "response": {
    "count": 1,
    "list": [
      {
        "kdObatSK": 38,
        "kdRacikan": "R.01",
        "obat": {
          "kdObat": "130199999",
          "nmObat": "Kapsul Kosong Apt Kapsul",
          "sedia": 0
        },
        "signa1": 3,
        "signa2": 1,
        "jmlObat": 10,
        "jmlHari": 3,
        "kekuatan": 2,
        "jmlPermintaan": 0,
        "jmlObatRacikan": 20
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Add Obat

- **URL:** `{BASE URL}/obat/kunjungan`

**Fungsi:** Add Data Obat

- **Method:** `POST`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
  "kdObatSK": 0,
  "noKunjungan": "1301U0070815Y000004",
  "racikan": true,
  "kdRacikan": null,
  "obatDPHO": true,
  "kdObat": "130199999",
  "signa1": 3,
  "signa2": 1,
  "jmlObat": 10,
  "jmlPermintaan": 2,
  "nmObatNonDPHO": "racikan 1 obat 1"
}
```


---

### Delete Obat

- **URL:** `{BASE URL}/obat/{Parameter 1}/kunjungan/{Parameter 2}`

**Fungsi:** Delete Data Obat

- **Method:** `DELETE`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** kodeObatSK
- **Parameter 2:** Nomor Kunjungan
```json
{
  "response": null,
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Pendaftaran


---

### Get Pendaftaran by Nomor Urut

- **URL:** `{BASE URL}/pendaftaran/noUrut/{Parameter 1}/tglDaftar/{Parameter 2}`

**Fungsi:** Get Data Pendaftaran by Nomor Urut

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor Urut Pendaftaran
- **Parameter 2:** Tanggal Pendaftaran
```json
{
  "response": {
    "noUrut": "A1",
    "tgldaftar": "13-08-2015",
    "providerPelayanan": {
      "kdProvider": "1301U007",
      "nmProvider": "MAULIDA JUNIAR, dr"
    },
    "peserta": {
      "noKartu": "0000029247423",
      "nama": "ANDIKA TRI UTARI",
      "hubunganKeluarga": "Peserta",
      "sex": "P",
      "tglLahir": "01-11-1992",
      "tglMulaiAktif": "12-09-2002",
      "tglAkhirBerlaku": "31-12-2050",
      "kdProviderPst": {
        "kdProvider": "0611U006",
        "nmProvider": "dr. HESTI WIDYANINGSIH"
      },
      "kdProviderGigi": {
        "kdProvider": "0091G002",
        "nmProvider": "drg. SRIWIDIASTUTI"
      },
      "jnsKelas": {
        "kode": "2",
        "nama": "KELAS II"
      },
      "jnsPeserta": {
        "kode": "13",
        "nama": "PEGAWAI SWASTA"
      },
      "golDarah": "4",
      "noHP": "",
      "noKTP": "1674010000",
      "aktif": true,
      "ketAktif": "AKTIF",
      "asuransi": {
        "kdAsuransi": "05",
        "nmAsuransi": "PT ASURANSI SINAR MAS",
        "noAsuransi": "056M000045274004792a"
      }
    },
    "poli": {
      "kdPoli": "001",
      "nmPoli": "Umum"
    },
    "keluhan": "",
    "status": "Sudah dilayani",
    "sistole": 0,
    "diastole": 0,
    "beratBadan": 0,
    "tinggiBadan": 0,
    "respRate": 0,
    "heartRate": 0,
    "tkp": {
      "kdTkp": "10",
      "nmTkp": "RJTP"
    }
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Get Pendaftaran Provider

- **URL:** `{BASE URL}/pendaftaran/tglDaftar/{Parameter 1}/{Parameter 2}/{Parameter 3}`

**Fungsi:** Get Data Pendaftaran Provider

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Tanggal Pendaftaran
- **Parameter 2:** Row data awal yang akan ditampilkan
- **Parameter 3:** Limit jumlah data yang akan ditampilkan
```json
{
  "response": {
    "count": 1,
    "list": [
      	{
               "noUrut": "A1",
               "tgldaftar": "15-05-2018",
               "providerPelayanan": null,
               "peserta": {
                   "noKartu": "0000043678034",
                   "nama": "FERRY APRIADI",
                   "hubunganKeluarga": null,
                   "sex": "L",
                   "tglLahir": "05-04-1965",
                   "tglMulaiAktif": null,
                   "tglAkhirBerlaku": null,
                   "kdProviderPst": null,
                   "kdProviderGigi": null,
                   "jnsKelas": null,
                   "jnsPeserta": null,
                   "golDarah": null,
                   "noHP": null,
                   "noKTP": null,
                   "aktif": false,
                   "ketAktif": null,
                   "asuransi": null
               },
               "poli": {
                   "kdPoli": "001",
                   "nmPoli": "Umum",
                   "poliSakit": true
               },
               "keluhan": "",
               "kunjSakit": true,
               "status": "Baru",
               "sistole": 0,
               "diastole": 0,
               "beratBadan": 0,
               "tinggiBadan": 0,
               "respRate": 0,
               "heartRate": 0,
               "tkp": {
                   "kdTkp": "10",
                   "nmTkp": "RJTP"
               }
           }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Add Pendaftaran

- **URL:** `{BASE URL}/pendaftaran`

**Fungsi:** Add Data Pendaftaran

- **Method:** `POST`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
  "kdProviderPeserta": "0114A026",
  "tglDaftar": "12-08-2015",
  "noKartu": "0001113569638",
  "kdPoli": "001",
  "keluhan": null,
  "kunjSakit": true,
  "sistole": 0,
  "diastole": 0,
  "beratBadan": 0,
  "tinggiBadan": 0,
  "respRate": 0,
  "lingkarPerut": 0,
  "heartRate": 0,
  "rujukBalik": 0,
  "kdTkp": "10"
}
```

ref tkp:
"tkp": [{ "kdTkp": "10", "nmTkp": "RJTP" }, { "kdTkp": "20", "nmTkp": "RITP" }, { "kdTkp": "50", "nmTkp": "Promotif" }]

---

### Delete Pendaftaran

- **URL:** `{BASE URL}/pendaftaran/peserta/{Parameter 1}/tglDaftar/{Parameter 2}/noUrut/{Parameter 3}/kdPoli/{Parameter 4}`

**Fungsi:** Delete Data Obat

- **Method:** `DELETE`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor Kartu Peserta
- **Parameter 2:** Tanggal Pendaftaran
- **Parameter 3:** Nomor Urut Pendaftaran
- **Parameter 4:** Kode Poli
```json
{
    "response": "",
    "metaData": {
      "message": "OK",
   	  "code": 200
    }
}
```


## Peserta


---

### Get Peserta

- **URL:** `{BASE URL}/peserta/{Parameter 1}`

**Fungsi:** Get Data Peserta

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor Kartu Peserta
```json
{
    "response": {
        "noKartu": "0000039043765",
        "nama": "SUSIAMINI IMAM SOERADI",
        "hubunganKeluarga": "Peserta",
        "sex": "P",
        "tglLahir": "25-10-1939",
        "tglMulaiAktif": "12-11-2014",
        "tglAkhirBerlaku": "01-01-2050",
        "kdProviderPst": {
            "kdProvider": "0114U163",
            "nmProvider": "Klinik Cempaka Putih"
        },
        "kdProviderGigi": {
            "kdProvider": null,
            "nmProvider": null
        },
        "jnsKelas": {
            "nama": "KELAS I",
            "kode": "1"
        },
        "jnsPeserta": {
            "nama": "PENERIMA PENSIUN PNS",
            "kode": "15"
        },
        "golDarah": "0",
        "noHP": "",
        "noKTP": "",
        "pstProl": "HT",
        "pstPrb": "HT",
        "aktif": true,
        "ketAktif": "AKTIF",
        "asuransi": {
            "kdAsuransi": null,
            "nmAsuransi": null,
            "noAsuransi": null,
            "cob": false
        },
        "tunggakan": 0
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


---

### Get Peserta by Jenis Kartu

- **URL:** `{BASE URL}/peserta/{Parameter 1}/{Parameter 2}`

**Fungsi:** Get Data Peserta

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Jenis Kartu: nik / noka
- **Parameter 2:** Nomor NIK atau kartu peserta sesuai dengan Parameter 1
```json
{
  "response": {
    "noKartu": "0001261832477",
    "nama": "TRI ARNI",
    "hubunganKeluarga": "Istri",
    "sex": "P",
    "tglLahir": "29-09-1965",
    "tglMulaiAktif": "09-01-2014",
    "tglAkhirBerlaku": "31-12-2050",
    "kdProviderPst": {
      "kdProvider": "09020107",
      "nmProvider": "KEL. MANGGARAI SELATAN"
    },
    "kdProviderGigi": {
      "kdProvider": null,
      "nmProvider": null
    },
    "jnsKelas": {
      "kode": "3",
      "nama": "KELAS III"
    },
    "jnsPeserta": {
      "kode": "22",
      "nama": "PBI (APBD)"
    },
    "golDarah": "0",
    "noHP": "083876592594",
    "noKTP": "3174016909650001",
    "aktif": true,
    "ketAktif": "AKTIF",
    "asuransi": {
      "kdAsuransi": null,
      "nmAsuransi": null,
      "noAsuransi": null
    },
				"tunggakan": 0
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Poli


---

### Get Poli FKTP

- **URL:** `{BASE URL}/poli/fktp/{Parameter 1}/{Parameter 2}`

**Fungsi:** Get Data Poli FKTP

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Row data awal yang akan ditampilkan
- **Parameter 2:** Limit jumlah data yang akan ditampilkan
```json
{
  "response": {
    "count": 2,
    "list": [
      {
        "kdPoli": "001",
        "nmPoli": "Umum",
        "poliSakit":true
      },
      {
        "kdPoli": "003",
        "nmPoli": "K I A",
        "poliSakit":true
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Provider


---

### Get Provider Rayonisasi

- **URL:** `{BASE URL}/provider/{Parameter 1}/{Parameter 2}`

**Fungsi:** Get Data Provider Rayonisasi

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Row data awal yang akan ditampilkan
- **Parameter 2:** Limit jumlah data yang akan ditampilkan
```json
{
  "response": {
    "count": 47,
    "list": [
      {
        "kdProvider": "0195R001",
        "nmProvider": "RUMKITBAN SIDOARJO"
      },
      {
        "kdProvider": "0195R027",
        "nmProvider": "RSU AL-Islam HM Mawardi"
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Spesialis


---

### Get Referensi Spesialis

- **URL:** `{BASE URL}/spesialis`

**Fungsi:** Get Data Referensi Spesialis

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

```json
{
  "response": {
    "count": 2,
    "list": [
      {
        "kdSpesialis": "ANA",
        "nmSpesialis": "ANAK"
      },
      {
        "kdSpesialis": "AND",
        "nmSpesialis": "ANDROLOGI"
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Get Referensi Sub Spesialis

- **URL:** `{BASE URL}/spesialis/{Parameter 1}/subspesialis`

**Fungsi:** Get Data Referensi Sub Spesialis

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode Spesialis
```json
{
    "response": {
        "count": 2,
        "list": [
            {
                "kdSubSpesialis": "26",
                "nmSubSpesialis": "Anak",
                "kdPoliRujuk": "ANA"
            },
            {
                "kdSubSpesialis": "27",
                "nmSubSpesialis": "Anak Alergi Imunologi",
                "kdPoliRujuk": "027"
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


---

### Get Referensi Sarana

- **URL:** `{BASE URL}/spesialis/sarana`

**Fungsi:** Get Data Referensi Sarana

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

```json
{
    "response": {
        "count": 2,
        "list": [
            {
                "kdSarana": "1",
                "nmSarana": "Rekam Medik"
            },
            {
                "kdSarana": "2",
                "nmSarana": "Laboratorium"
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


---

### Get Referensi Khusus

- **URL:** `{BASE URL}/spesialis/khusus`

**Fungsi:** Get Data Referensi Khusus

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

```json
{
    "response": {
        "count": 10,
        "list": [
            {
                "kdKhusus": "HDL",
                "nmKhusus": "HEMODIALISA"
            },
            {
                "kdKhusus": "THA",
                "nmKhusus": "THALASEMIA"
            },
            {
                "kdKhusus": "HEM",
                "nmKhusus": "HEMOFILI"
            },
            {
                "kdKhusus": "IGD",
                "nmKhusus": "ALIH RAWAT"
            },
            {
                "kdKhusus": "JIW",
                "nmKhusus": "JIWA"
            },
            {
                "kdKhusus": "KLT",
                "nmKhusus": "KUSTA"
            },
            {
                "kdKhusus": "PAR",
                "nmKhusus": "TB-MDR"
            },
            {
                "kdKhusus": "KEM",
                "nmKhusus": "SARANA KEMOTERAPI"
            },
            {
                "kdKhusus": "RAT",
                "nmKhusus": "SARANA RADIOTERAPI"
            },
            {
                "kdKhusus": "HIV",
                "nmKhusus": "HIV-ODHA"
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


---

### Get Faskes Rujukan Sub Spesialis

- **URL:** `{BASE URL}/spesialis/rujuk/subspesialis/{Parameter 1}/sarana/{Parameter 2}/tglEstRujuk/{Parameter 3}`

**Fungsi:** Get Data Faskes Rujukan Sub Spesialis

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode Sub Spesialis
- **Parameter 2:** Kode Sarana
- **Parameter 3:** Tanggal Estimasi Rujuk, format: dd-mm-yyyy
```json
{
  "response": {
    "count": 36,
    "list": [
      {
        "kdppk": "0114R049",
        "nmppk": "RSUD CEMPAKA PUTIH",
        "alamatPpk": "JALAN RAWASARI SELATAN NO. 1",
        "telpPpk": "021-4224243",
        "kelas": "D",
        "nmkc": "JAKARTA PUSAT",
        "distance": 951.0729,
        "jadwal": "Senin : 07:30 - 14:00, 08:00 - 17:00;14:00 - 19:00",
        "jmlRujuk": 0,
        "kapasitas": 0,
      	"persentase": 0
      },
      {
        "kdppk": "0114R052",
        "nmppk": "RSUD KEMAYORAN",
        "alamatPpk": "Jl. Serdang Baru I",
        "telpPpk": "(021) 4251005",
        "kelas": "D",
        "nmkc": "JAKARTA PUSAT",
        "distance": 2505.416,
        "jadwal": "Senin : 07:30 - 14:00, 08:00 - 17:00;14:00 - 19:00",
        "jmlRujuk": 0,
        "kapasitas": 0,
      	"persentase": 0
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```

Get Faskes Rujukan Khusus ALIH RAWAT, HEMODIALISA, JIWA, KUSTA, TB-MDR, SARANA KEMOTERAPI, SARANA RADIOTERAPI, HIV-ODHA
- **URL:** `{BASE URL}/spesialis/rujuk/khusus/{Parameter 1}/noKartu/{Parameter 2}/tglEstRujuk/{Parameter 3}`

**Fungsi:** Get Data Faskes Rujukan Khusus ALIH RAWAT, HEMODIALISA, JIWA, KUSTA, TB-MDR, SARANA KEMOTERAPI, SARANA RADIOTERAPI, HIV-ODHA

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode Khusus. kdKhusus = ["IGD","HDL","JIW","KLT","PAR","KEM","RAT","HIV"]
- **Parameter 2:** Nomor Kartu Peserta
- **Parameter 3:** Tanggal Estimasi Rujuk, format: dd-mm-yyyy
```json
{
  "response": {
    "count": 36,
    "list": [
      {
        "kdppk": "0114R049",
        "nmppk": "RSUD CEMPAKA PUTIH",
        "alamatPpk": "JALAN RAWASARI SELATAN NO. 1",
        "telpPpk": "021-4224243",
        "kelas": "D",
        "nmkc": "JAKARTA PUSAT",
        "distance": 951.0729,
        "jadwal": "Senin : 07:30 - 14:00, 08:00 - 17:00;14:00 - 19:00",
        "jmlRujuk": 0,
        "kapasitas": 0,
      	"persentase": 0
      },
      {
        "kdppk": "0114R052",
        "nmppk": "RSUD KEMAYORAN",
        "alamatPpk": "Jl. Serdang Baru I",
        "telpPpk": "(021) 4251005",
        "kelas": "D",
        "nmkc": "JAKARTA PUSAT",
        "distance": 2505.416,
        "jadwal": "Senin : 07:30 - 14:00, 08:00 - 17:00;14:00 - 19:00",
        "jmlRujuk": 0,
        "kapasitas": 0,
      	"persentase": 0
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Get Faskes Rujukan Khusus THALASEMIA dan HEMOFILI

- **URL:** `{BASE URL}/spesialis/rujuk/khusus/{Parameter 1}/subspesialis/{Parameter 2}/noKartu/{Parameter 3}/tglEstRujuk/{Parameter 4}`

**Fungsi:** Get Data Faskes Rujukan Khusus THALASEMIA dan HEMOFILI

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Kode Khusus. kdKhusus = ["THA","HEM"]
- **Parameter 2:** Kode Sub Spesialis. subspesialis = [{kdSubSpesialis:"3", nmSubSpesialis:"PENYAKIT DALAM"}, {kdSubSpesialis:"8", nmSubSpesialis:"HEMATOLOGI - ONKOLOGI MEDIK"}, {kdSubSpesialis:"26", nmSubSpesialis:"ANAK"}, {kdSubSpesialis:"30", nmSubSpesialis:"ANAK HEMATOLOGI ONKOLOGI"}]
- **Parameter 3:** Nomor Kartu Peserta
- **Parameter 4:** Tanggal Estimasi Rujuk, format: dd-mm-yyyy
```json
{
  "response": {
    "count": 2,
    "list": [
      {
        "kdppk": "0114R049",
        "nmppk": "RSUD CEMPAKA PUTIH",
        "alamatPpk": "JALAN RAWASARI SELATAN NO. 1",
        "telpPpk": "021-4224243",
        "kelas": "D",
        "nmkc": "JAKARTA PUSAT",
        "distance": 951.0729,
        "jadwal": "Senin : 07:30 - 14:00, 08:00 - 17:00;14:00 - 19:00",
        "jmlRujuk": 0,
        "kapasitas": 0,
      								"persentase": 0
      },
      {
        "kdppk": "0113R052",
        "nmppk": "KLINIK HD CIPTA HUSADA 2",
        "alamatPpk": "Jalan Langit-Langit No.12",
        "telpPpk": "021-4896807",
        "kelas": "D",
        "nmkc": "JAKARTA TIMUR",
        "distance": 2012.9943,
        "jadwal": "Senin : 07:30 - 14:00, 08:00 - 17:00;14:00 - 19:00",
        "jmlRujuk": 0,
        "kapasitas": 0,
      								"persentase": 0
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Status Pulang


---

### Get Status Pulang

- **URL:** `{BASE URL}/statuspulang/rawatInap/{Parameter 1}`

**Fungsi:** Get Status Pulang

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Jika rawat inap maka diisi true, sebaliknya diisi false
```json
{
  "response": {
    "count": 3,
    "list": [
      {
        "kdStatusPulang": "3",
        "nmStatusPulang": "Berobat Jalan"
      },
      {
        "kdStatusPulang": "4",
        "nmStatusPulang": "Rujuk Lanjut"
      },
      {
        "kdStatusPulang": "5",
        "nmStatusPulang": "Rujuk Internal"
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Tindakan


---

### Get Tindakan by Kunjungan

- **URL:** `{BASE URL}/tindakan/kunjungan/{Parameter 1}`

**Fungsi:** Get Data Tindakan by Kunjungan

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor Kunjungan
```json
{
  "response": {
    "count": 1,
    "list": [
      {
        "kdTindakanSK": 199,
        "noKunjungan": "1301U0070815Y000005",
        "kdTindakan": "01006",
        "nmTindakan": "Pelayanan KB : Suntik\r\n",
        "biaya": 15000,
        "keterangan": "2000",
        "hasil": 0
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Get Referensi Tindakan

- **URL:** `{BASE URL}/tindakan/kdTkp/{Parameter 1}/{Parameter 2}/{Parameter 3}`

**Fungsi:** Get Data Referensi Tindakan

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** kdTkp => 10 : RJTP, 20 : RITP, 50 : Promotif
- **Parameter 2:** Row data awal yang akan ditampilkan
- **Parameter 3:** Limit jumlah data yang akan ditampilkan
```json
{
  "response": {
    "count": 212,
    "list": [
      {
        "kdTindakan": "02007",
        "nmTindakan": "Penanganan pendarahan pasca keguguran\r\n",
        "maxTarif": 750000,
        "withValue": false
      },
      {
        "kdTindakan": "02005",
        "nmTindakan": "Paket persalinan per vaginam normal",
        "maxTarif": 600000,
        "withValue": false
      },
      {
        "kdTindakan": "02003",
        "nmTindakan": "Pelayanan tindakan pasca persalinan",
        "maxTarif": 175000,
        "withValue": false
      }
    ]
  },
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


---

### Add Tindakan

- **URL:** `{BASE URL}/tindakan`

**Fungsi:** Add Data Tindakan

- **Method:** `POST`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
  "kdTindakanSK": 0,
  "noKunjungan": "1301U0070815Y000004",
  "kdTindakan": "01007",
  "biaya": 0,
  "keterangan": null,
  "hasil": 0
}
```


---

### Edit Tindakan

- **URL:** `{BASE URL}/tindakan`

**Fungsi:** Edit Data Tindakan

- **Method:** `PUT`
- **Format:** `Json`
- **Content-Type:** `text/plain`


#### Request

```json
{
  "kdTindakanSK": 218,
  "noKunjungan": "1301U0070815Y000004",
  "kdTindakan": "01007",
  "biaya": 0,
  "keterangan": null,
  "hasil": 0
}
```


---

### Delete Tindakan

- **URL:** `{BASE URL}/tindakan/{Parameter 1}/kunjungan/{Parameter 2}`

**Fungsi:** Delete Data Tindakan

- **Method:** `DELETE`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** kdTindakanSK
- **Parameter 2:** Nomor Kunjungan
```json
{
  "response": null,
  "metaData": {
    "message": "OK",
    "code": 200
  }
}
```


## Alergi


---

### Get Alergi New

- **URL:** `{BASE URL}/alergi/jenis/{parameter 1}`

**Fungsi:** Get Data Alergi

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** 01:Makanan, 02:Udara, 03:Obat
```json
{
  "response": {
      "list": [
          {
              "kdAlergi": "00",
              "nmAlergi": "Tidak Ada"
          },
          {
              "kdAlergi": "01",
              "nmAlergi": "Seafood"
          },
          {
              "kdAlergi": "02",
              "nmAlergi": "Gandum"
          },
          {
              "kdAlergi": "03",
              "nmAlergi": "Susu Sapi"
          },
          {
              "kdAlergi": "04",
              "nmAlergi": "Kacang-Kacangan"
          },
          {
              "kdAlergi": "05",
              "nmAlergi": "Makanan Lain"
          }
      ]
  },
  "metaData": {
      "message": "OK",
      "code": 200
  }
}
```


## Prognosa


---

### Get Prognosa New

- **URL:** `{BASE URL}/prognosa`

**Fungsi:** Get Data Alergi

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

```json
{
    "response": {
        "list": [
            {
                "kdPrognosa": "01",
                "nmPrognosa": "Sanam (Sembuh)"
            },
            {
                "kdPrognosa": "02",
                "nmPrognosa": "Bonam (Baik)"
            },
            {
                "kdPrognosa": "03",
                "nmPrognosa": "Malam (Buruk/Jelek)"
            },
            {
                "kdPrognosa": "04",
                "nmPrognosa": "Dubia Ad Sanam/Bolam (Tidak tentu/Ragu-ragu, Cenderung Sembuh/Baik)"
            },
            {
                "kdPrognosa": "05",
                "nmPrognosa": "Dubia Ad Malam (Tidak tentu/Ragu-ragu, Cenderung Sembuh/Baik)"
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


## Skrining


---

### Get Skrining Riwayat Kesehatan by Penyakit New

- **URL:** `{BASE URL}/skrinning/rekap`

**Fungsi:** Mengambil data rekapitulasi hasil Skrining Riwayat Kesehatan per penyakit

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

```json
{
    "response": {
        "list": [
            {
                "nama_penyakit": "penyakit_anemia",
                "beresiko": 131,
                "tidak_beresiko": 8881
            },
            {
                "nama_penyakit": "penyakit_diabetes_mellitus",
                "beresiko": 4842,
                "tidak_beresiko": 9239
            },
            {
                "nama_penyakit": "penyakit_hepatitis_b",
                "beresiko": 746,
                "tidak_beresiko": 14596
            },
            {
                "nama_penyakit": "penyakit_hepatitis_c",
                "beresiko": 389,
                "tidak_beresiko": 14953
            },
            {
                "nama_penyakit": "penyakit_hipertensi_stroke_ischemic_heart_disease",
                "beresiko": 5942,
                "tidak_beresiko": 7591
            },
            {
                "nama_penyakit": "penyakit_kanker_paru",
                "beresiko": 352,
                "tidak_beresiko": 6792
            },
            {
                "nama_penyakit": "penyakit_kanker_payudara",
                "beresiko": 440,
                "tidak_beresiko": 9084
            },
            {
                "nama_penyakit": "penyakit_kanker_serviks",
                "beresiko": 5749,
                "tidak_beresiko": 3779
            },
            {
                "nama_penyakit": "penyakit_kolorektal",
                "beresiko": 43,
                "tidak_beresiko": 5741
            },
            {
                "nama_penyakit": "penyakit_paru_obstruktif_kronis",
                "beresiko": 826,
                "tidak_beresiko": 7559
            },
            {
                "nama_penyakit": "penyakit_thalasemia",
                "beresiko": 90,
                "tidak_beresiko": 15211
            },
            {
                "nama_penyakit": "penyakit_tuberkulosis",
                "beresiko": 961,
                "tidak_beresiko": 14381
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


---

### Get Detail Peserta Skrining Riwayat Kesehatan New

- **URL:** `{BASE URL}/skrinning/peserta/{Parameter 1}/{Parameter 2}/{Parameter 3}`

**Fungsi:** Mengambil data rekapitulasi Skrining Riwayat Kesehatan per peserta

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor atau Nama Peserta
- **Parameter 2:** Row data awal yang akan ditampilkan
- **Parameter 3:** Limit jumlah data yang akan ditampilkan
```json
{
    "response": {
        "count": 1,
        "list": [
            {
                "nomor_peserta": "0000127207697",
                "nama": "A SITI RAHMA SYAHIDAH PUTRI",
                "usia": 17,
                "no_hp": "08111789265",
                "email": "",
                "status_penyakit": {
                    "anemia": "Tidak Berisiko",
                    "hepatitis_b": "Tidak Berisiko",
                    "hepatitis_c": "Tidak Berisiko",
                    "hipertensi_stroke_ischemic_heart_disease": "Tidak Berisiko",
                    "kanker_paru": "Tidak Mengisi",
                    "kanker_payudara": "Tidak Berisiko",
                    "kanker_serviks": "Berisiko",
                    "kolorektal": "Tidak Mengisi",
                    "paru_obstruktif_kronis": "Tidak Mengisi",
                    "penyakit_diabetes_mellitus": "Tidak Berisiko",
                    "thalasemia": "Tidak Berisiko",
                    "tuberkulosis": "Tidak Berisiko"
                }
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


---

### Get Data Prolanis Diabetes Mellitus New

- **URL:** `{BASE URL}/skrinning/prolanis/dm/{Parameter 1}/{Parameter 2}/{Parameter 3}`

**Fungsi:** Mengambil data peserta prolanis DM sejak tahun 2017

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor atau Nama Peserta
- **Parameter 2:** Row data awal yang akan ditampilkan
- **Parameter 3:** Limit jumlah data yang akan ditampilkan
```json
{
    "response": {
        "count": 1,
        "list": [
            {
                "nomor_peserta": "0001222114487",
                "nama": "ABDUL BASIT",
                "usia": 52,
                "jenis_kelamin": "Laki-laki",
                "diagnosa_terakhir": "E119;Non-insulin-dependent diabetes mellitus without complications;",
                "status_prolanis": "Sudah"
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```


---

### Get Data Prolanis Hipertensi New

- **URL:** `{BASE URL}/skrinning/prolanis/ht/{Parameter 1}/{Parameter 2}/{Parameter 3}`

**Fungsi:** Mengambil data peserta prolanis HT sejak tahun 2017

- **Method:** `GET`
- **Format:** `Json`
- **Content-Type:** `application/json; charset=utf-8`

- **Parameter 1:** Nomor atau Nama Peserta
- **Parameter 2:** Row data awal yang akan ditampilkan
- **Parameter 3:** Limit jumlah data yang akan ditampilkan
```json
{
    "response": {
        "count": 1,
        "list": [
            {
                "nomor_peserta": "0001222114487",
                "nama": "ABDUL BASIT",
                "usia": 52,
                "jenis_kelamin": "Laki-laki",
                "diagnosa_terakhir": "I10;Essential (primary) hypertension;",
                "status_prolanis": "Sudah"
            }
        ]
    },
    "metaData": {
        "message": "OK",
        "code": 200
    }
}
```
