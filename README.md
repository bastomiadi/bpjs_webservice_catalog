# BPJS API Web Service Catalog

A comprehensive web service catalog for BPJS Kesehatan APIs, similar to Swagger UI, built with Tailwind CSS. This application provides documentation and testing capabilities for all BPJS API modules.

## Features

### 1. **8 BPJS API Modules**
- **Aplicares** - Hospital management services
- **VClaim** - Claims and billing services
- **Antrean RS** - Hospital queue management
- **Apotek** - Pharmacy services
- **PCare** - Primary care services
- **Antrean FKTP** - Family planning and primary care queue
- **i-Care** - Integrated care services
- **WS Rekam Medis** - Medical record services

### 2. **Dynamic API Domain Version Selection**
Implemented version switching between:
- **V1**: `apijkn.bpjs-kesehatan.go.id` (old domain)
- **V2**: `new-apijkn.bpjs-kesehatan.go.id` (new domain)

Features:
- Dropdown selector in header
- Cookie-based persistence (30 days)
- Automatic page reload on version change
- Version badge displayed on each module

**Production Base URLs:**
| Module | V1 URL | V2 URL |
|--------|--------|--------|
| VClaim | `https://apijkn.bpjs-kesehatan.go.id/vclaim-rest` | `https://new-apijkn.bpjs-kesehatan.go.id/vclaim-rest` |
| Antrean RS | `https://apijkn.bpjs-kesehatan.go.id/antreanrs` | `https://new-apijkn.bpjs-kesehatan.go.id/antreanrs` |
| Antrean FKTP | `https://apijkn.bpjs-kesehatan.go.id/antreanfktp` | `https://new-apijkn.bpjs-kesehatan.go.id/antreanfktp` |
| Apotek | `https://apijkn.bpjs-kesehatan.go.id/apotek-rest` | `https://new-apijkn.bpjs-kesehatan.go.id/apotek-rest` |
| PCare | `https://apijkn.bpjs-kesehatan.go.id/pcare-rest` | `https://new-apijkn.bpjs-kesehatan.go.id/pcare-rest` |
| i-Care | `https://apijkn.bpjs-kesehatan.go.id/ihs` | `https://new-apijkn.bpjs-kesehatan.go.id/ihs` |
| eRekamMedis | `https://apijkn.bpjs-kesehatan.go.id/erekammedis` | `https://new-apijkn.bpjs-kesehatan.go.id/erekammedis` |
| Aplicares | `https://apijkn.bpjs-kesehatan.go.id/aplicaresws/rest` | `https://new-apijkn.bpjs-kesehatan.go.id/aplicaresws/rest` |

> **Note:** Aplicares uses the same V1/V2 domain switching as other modules (no separate dev domain available).

### 3. **Dev/Production Mode Toggle**
Toggle between development and production environments:

**Production Mode** (default):
- Uses V1 or V2 domain based on selection
- All modules use standard production URLs

**Dev Mode**:
- Uses `apijkn-dev.bpjs-kesehatan.go.id` domain
- Specific dev endpoints for each module:
  - VClaim: `/vclaim-rest-dev`
  - Antrean RS: `/antreanrs_dev`
  - Antrean FKTP: `/antreanfktp_dev`
  - Apotek: `/apotek-rest-dev`
  - PCare: `/pcare-rest-dev`
  - i-Care: `/ihs_dev`
  - eRekamMedis: `/erekammedis_dev`
  - Aplicares: Uses production domain (no dev available)
- API Version selector is disabled when Dev mode is active

## Project Structure

```
bpjs/
├── index.php              # Main application file
├── README.md              # This documentation
├── config/
│   └── env.php           # Configuration settings
├── helpers/
│   ├── bpjs_decrypt.php  # AES-256-CBC decryption
│   ├── bpjs_request.php  # API request handler
│   └── bpjs_signature.php # HMAC-SHA256 signature generator
├── library/
│   └── lz-string/        # String compression library
└── assets/
    └── bpjs-logo.png     # BPJS logo
```

## Setup Instructions

1. **Prerequisites**
   - XAMPP (or similar PHP server)
   - PHP 7.4 or higher
   - Composer (for dependencies)

2. **Installation**
   ```bash
   # Clone or download the repository
   cd /Applications/XAMPP/xamppfiles/htdocs/bpjs
   
   # Install dependencies
   composer install
   ```

3. **Configuration**
   - Copy `.env-demo` to `.env`
   - Configure your BPJS credentials in `.env`:
     ```
     BPJS_CONS_ID=your_consumer_id
     BPJS_CONS_KEY=your_consumer_secret
     BPJS_USER_KEY=your_user_key
     BPJS_PASSWORD=your_password
     ```

4. **Running the Application**
   - Start Apache and MySQL from XAMPP
   - Place the project in `htdocs/bpjs`
   - Access via browser: `http://localhost/bpjs`

## API Authentication

This application uses BPJS API authentication which includes:

1. **Signature Generation**: HMAC-SHA256 hash of the request
2. **Timestamp**: Unix timestamp for request validity
3. **Target Service**: Specific endpoint identifier
4. **Additional Headers**: User-Key and X-Timestamp

## Response Handling

API responses are automatically:
1. **Decompressed** using LZString algorithm
2. **Decrypted** using AES-256-CBC
3. **Formatted** as readable JSON

## Module Details

### Aplicares
Sub-modules:
- Referensi Kamar
- Update Ketersediaan Tempat Tidur
- Ruangan Baru
- Ketersediaan Kamar RS
- Hapus Ruangan

### VClaim
Sub-modules:
- Referensi
- Pulang
- RSC
- BC
- COB
- ASKES

### Antrean RS
Sub-modules:
- Jadwal Dokter
- Jadwal Praktik
- Pendaftaran
- Pemeriksaan
- Pulang
- Batal

### Apotek
Sub-modules:
- Obat
- Aturan Pakai
- Resep
- Faktur

### PCare
Sub-modules:
- Referensi
- Pulang
- RSC
- BC
- COB
- ASKES

### Antrean FKTP
Sub-modules:
- Jadwal Dokter
- Jadwal Praktik
- Pendaftaran
- Pemeriksaan
- Pulang
- Batal

### i-Care
Sub-modules:
- Referensi
- Pulang
- RSC
- BC
- COB
- ASKES

### WS Rekam Medis
Sub-modules:
- Referensi
- Pulang
- RSC
- BC
- COB
- ASKES

## Security Notes

- Store credentials securely in `.env` file
- Never commit `.env` to version control
- Use HTTPS in production
- Keep API keys confidential

## License

This project is for educational and development purposes.

## Contributing

Feel free to submit issues and pull requests.