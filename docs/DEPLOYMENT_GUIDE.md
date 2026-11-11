
# DEPLOYMENT_GUIDE.md

# SEUMPAMA POS
**Deployment and Environment Configuration Guide**

| Informasi | Keterangan |
|---|---|
| Project | Seumpama POS |
| Document | Deployment and Environment Configuration Guide |
| Main Application | Laravel |
| Machine Learning Service | Python ANN Service |
| Database | MySQL / compatible MariaDB (final engine TBD) |
| Timezone | Asia/Jakarta |
| Status | Draft - Technical Planning |

---

# 1. DOCUMENT PURPOSE

Dokumen ini menjelaskan standar persiapan environment, konfigurasi, instalasi, deployment, dan pemeliharaan Seumpama POS.

Dokumen mencakup dua komponen utama:

1. Laravel Application sebagai aplikasi utama.
2. Python ANN Service sebagai layanan pemrosesan machine learning.

Dokumen ini menjadi acuan implementasi deployment dan tidak menetapkan provider hosting tertentu sebelum kebutuhan produksi dievaluasi.

Dokumen ini tidak mengubah pembagian tanggung jawab sistem yang telah ditetapkan dalam `PROJECT_MASTER.md`, `PROJECT_SPEC.md`, `ANN_SPECIFICATION.md`, dan `API_CONTRACT.md`.

---

# 2. DEPLOYMENT ARCHITECTURE

## 2.1 System Components

| Component | Responsibility |
|---|---|
| Web Server | Menerima HTTP/HTTPS request dari pengguna |
| Laravel Application | Business logic, POS, inventory, reporting, dan integrasi |
| Database | Penyimpanan data operasional dan metadata aplikasi |
| Laravel Queue Worker | Menjalankan pekerjaan asynchronous |
| Laravel Scheduler | Menjalankan pekerjaan terjadwal |
| Python ANN Service | Dataset processing, training, evaluation, dan prediction |
| Laravel File Storage | Menyimpan file aplikasi dan dokumen |
| Python Artifact Storage | Menyimpan model ANN dan preprocessing artifacts |
| Weather Provider | Menyediakan data cuaca; Open-Meteo merupakan kandidat awal |

## 2.2 Logical Deployment Architecture

```text
                         USER
                           |
                           v
                     WEB BROWSER
                           |
                        HTTPS
                           |
                           v
                      WEB SERVER
                           |
                           v
                  LARAVEL APPLICATION
                     /      |      \
                    /       |       \
                   v        v        v
               DATABASE   QUEUE   LARAVEL STORAGE
                           |
                           v
                    LARAVEL QUEUE
                       WORKER
                           |
                           | Internal HTTP API
                           v
                  PYTHON ANN SERVICE
                     /           \
                    v             v
              ANN PROCESSING   ARTIFACT STORAGE
                    |
                    | API Response
                    v
             LARAVEL APPLICATION
                    |
                    v
             DATABASE / STORAGE


          WEATHER PROVIDER
                 |
                 | External API
                 v
          LARAVEL APPLICATION
```

### Architecture Rules

1. Laravel merupakan pusat pengelolaan aplikasi dan business logic.
2. Laravel menjadi satu-satunya komponen aplikasi yang mengelola database operasional.
3. Python ANN Service tidak mengakses database Laravel secara langsung.
4. Laravel berkomunikasi dengan Python melalui internal HTTP API.
5. Python menyimpan model dan preprocessing artifacts pada storage milik Python Service.
6. Hasil training dan prediction dikembalikan kepada Laravel melalui API.
7. Laravel menyimpan metadata training dan hasil prediction yang diperlukan ke database utama.
8. Laravel Queue Worker menjalankan pekerjaan asynchronous yang membutuhkan Python Service.
9. Laravel Scheduler mengatur pekerjaan terjadwal sesuai kebutuhan modul.
10. Weather Provider diakses melalui Laravel, bukan langsung oleh browser pengguna.

Python tidak boleh melakukan perubahan langsung terhadap tabel transaksi, recipe, atau inventaris Laravel.

## 2.3 Deployment Principles

- Laravel menjadi pusat pengelolaan aplikasi.
- Python berjalan sebagai service terpisah.
- Database menjadi sumber data operasional utama.
- Training ANN tidak boleh menghambat transaksi POS.
- Kegagalan Python tidak boleh menghentikan fungsi POS.
- Secret dan API key tidak boleh disimpan dalam source code.
- Development, testing, dan production harus memiliki konfigurasi terpisah.
- Model dan preprocessing artifacts harus memiliki identitas versi.
- Tidak boleh menggunakan hasil prediksi palsu ketika model atau service tidak tersedia.

---

# 3. ENVIRONMENT CLASSIFICATION

## 3.1 Development Environment

Digunakan untuk pengembangan dan pengujian lokal.

Karakteristik:

- Laravel dijalankan pada environment lokal.
- Database menggunakan instance development.
- Python ANN Service dijalankan secara terpisah.
- Debugging diperbolehkan.
- Dataset pengujian dapat menggunakan data simulasi dengan penanda yang jelas.
- Secret development tidak boleh menggunakan credential production.

WSL2 Ubuntu dapat digunakan sebagai lingkungan pengembangan utama.

Docker tidak diwajibkan pada tahap awal.

## 3.2 Testing Environment

Digunakan untuk memvalidasi integrasi dan perilaku aplikasi sebelum deployment produksi.

Karakteristik:

- Konfigurasi menyerupai production.
- Menggunakan database terpisah.
- Tidak menggunakan data operasional produksi tanpa otorisasi.
- Pengujian komunikasi Laravel dan Python.
- Pengujian queue dan scheduler.
- Pengujian transaksi dan konsistensi inventaris.
- Pengujian backup dan recovery sesuai kebutuhan.

## 3.3 Production Environment

Digunakan untuk operasional Coffee Shop Seumpama Bunga.

Karakteristik:

- HTTPS wajib.
- `APP_DEBUG=false`.
- Database production terpisah.
- Secret disimpan secara aman.
- Backup database terjadwal.
- Queue worker dan scheduler dikelola sebagai proses terpisah.
- Logging dan monitoring diaktifkan.
- Akses Python Service dibatasi pada jaringan internal.
- File dan ANN artifacts memiliki strategi penyimpanan serta backup yang jelas.

---

# 4. SERVER REQUIREMENTS

## 4.1 Laravel Application

Persyaratan akhir mengikuti versi Laravel dan PHP yang dikunci dalam dependency project.

Komponen yang diperlukan:

- PHP versi yang kompatibel dengan Laravel.
- Composer.
- Database engine yang telah dipilih dan diuji.
- PHP extensions sesuai kebutuhan framework dan dependency.
- Web server seperti Nginx atau alternatif yang kompatibel.
- Node.js dan npm untuk proses build frontend.

Jangan mengunci versi PHP berdasarkan perkiraan. Periksa `composer.json` dan `composer.lock` sebelum menentukan versi production.

## 4.2 Python ANN Service

Komponen yang diperlukan:

- Python versi yang kompatibel dengan Keras dan dependency terkait.
- Keras.
- Backend TensorFlow yang kompatibel.
- NumPy.
- Pandas.
- Scikit-learn apabila digunakan untuk preprocessing atau baseline.
- Library API sesuai framework service yang dipilih.

Versi dependency harus dikunci melalui file dependency management seperti `requirements.txt` atau format lock yang dipilih.

GPU tidak diwajibkan.

Training awal harus dirancang agar dapat berjalan menggunakan CPU.

## 4.3 Database

Database menggunakan MySQL atau compatible MariaDB sesuai keputusan final pada `DATABASE_DESIGN.md`.

Ketentuan:

- Database development, testing, dan production harus terpisah.
- Versi database production harus sesuai dengan versi yang telah diuji.
- Database production tidak boleh digunakan untuk eksperimen training secara langsung.
- Database harus memiliki backup berkala.
- Database connection hanya diberikan kepada komponen yang membutuhkannya.
- Laravel mengelola seluruh perubahan data operasional.

Python tidak boleh melakukan perubahan langsung terhadap tabel transaksi atau inventaris Laravel.

---

# 5. PROJECT DIRECTORY

Struktur konseptual:

```text
seumpama-pos/
│
├── laravel-app/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── .env.example
│   └── composer.json
│
├── python-ann-service/
│   ├── app/
│   ├── models/
│   ├── artifacts/
│   ├── tests/
│   ├── requirements.txt
│   └── main.py
│
├── docs/
│
└── README.md
```

Struktur tersebut merupakan gambaran pemisahan aplikasi, bukan kewajiban menggunakan nama direktori atau struktur package tertentu.

Ketentuan penyimpanan:

- Laravel mengelola file aplikasi melalui Laravel Storage.
- Python ANN Service mengelola model dan preprocessing artifacts melalui artifact storage miliknya.
- Model hasil training, scaler, dan artifact terkait tidak otomatis dimasukkan ke Git.
- Metadata model dan referensi artifact yang diperlukan aplikasi dicatat oleh Laravel.
- Lokasi penyimpanan production dapat menggunakan local storage atau solusi lain setelah kebutuhan deployment dievaluasi.

---

# 6. LARAVEL INSTALLATION

## 6.1 Dependency Installation

```bash
composer install
npm install
```

Untuk production, gunakan dependency yang sudah dikunci dalam repository.

## 6.2 Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Konfigurasi database disesuaikan dengan environment masing-masing.

Contoh:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seumpama_pos
DB_USERNAME=root
DB_PASSWORD=
```

Nilai tersebut hanya contoh development.

Driver database harus disesuaikan dengan engine yang dipilih dan telah diuji.

## 6.3 Database Migration

```bash
php artisan migrate
```

Seeder hanya dijalankan sesuai kebutuhan environment.

Jangan menjalankan seeder data demonstrasi pada database production tanpa pemeriksaan.

## 6.4 Frontend Build

Development:

```bash
npm run dev
```

Production:

```bash
npm run build
```

## 6.5 Local Application

```bash
php artisan serve
```

Perintah tersebut hanya digunakan untuk development, bukan sebagai web server production.

---

# 7. PYTHON ANN SERVICE INSTALLATION

## 7.1 Virtual Environment

```bash
cd python-ann-service

python -m venv .venv
```

Aktivasi Linux/WSL2:

```bash
source .venv/bin/activate
```

Aktivasi Windows:

```powershell
.venv\Scripts\Activate.ps1
```

## 7.2 Dependency Installation

```bash
pip install -r requirements.txt
```

Dependency final ditentukan setelah pengujian kompatibilitas Keras dan TensorFlow.

## 7.3 Service Configuration

Python Service menggunakan konfigurasi environment tersendiri.

Contoh:

```dotenv
APP_ENV=development
SERVICE_HOST=127.0.0.1
SERVICE_PORT=8001

API_PREFIX=/api/v1
API_TOKEN=
MODEL_STORAGE_PATH=./artifacts
LOG_LEVEL=INFO
```

Konfigurasi di atas merupakan contoh nama variabel. Implementasi aktual harus mengikuti konfigurasi service yang dibuat.

## 7.4 Service Execution

Python Service dijalankan menggunakan application server yang sesuai dengan framework API yang dipilih.

Perintah final ditentukan dalam implementasi Python Service.

Service harus mendukung:

- Health check.
- Request validation.
- Authentication.
- Error handling.
- Structured logging.
- Request timeout.
- Model availability checking.
- Artifact loading dan compatibility checking.

---

# 8. LARAVEL–PYTHON CONNECTIVITY

## 8.1 Communication

Laravel berkomunikasi dengan Python melalui internal HTTP API.

Ketentuan:

- URL service disimpan dalam konfigurasi environment.
- Secret tidak boleh ditulis langsung dalam source code.
- Request memiliki timeout.
- Response harus divalidasi.
- Error komunikasi harus dicatat.
- Python tidak boleh menerima request training atau prediction tanpa autentikasi.
- Python tidak boleh mengakses database Laravel secara langsung.
- Laravel harus menyimpan metadata dan hasil yang diperlukan setelah menerima response yang valid.

## 8.2 Service Availability

Python Service harus menyediakan health check untuk memeriksa ketersediaan service.

Health check service tidak otomatis menjamin bahwa model ANN tersedia atau siap melakukan prediction.

Status service dan status model harus dapat diperiksa secara terpisah.

## 8.3 Failure Handling

Jika Python Service tidak tersedia:

- POS tetap dapat digunakan.
- Transaksi tetap dapat diproses.
- Inventory tetap dapat dikelola.
- Prediksi ditandai sebagai tidak tersedia apabila tidak ada hasil valid yang dapat digunakan.
- Pekerjaan prediksi dapat dijadwalkan ulang sesuai mekanisme retry.
- Kegagalan integrasi dicatat dalam log.

Sistem boleh menampilkan prediksi valid terakhir apabila masih relevan, tetapi timestamp dan model version harus ditampilkan.

Tidak boleh membuat hasil prediksi palsu sebagai pengganti kegagalan service.

---

# 9. QUEUE AND SCHEDULER

## 9.1 Queue

Pekerjaan yang berpotensi membutuhkan waktu lama harus dijalankan secara asynchronous.

Contoh:

- Training ANN.
- Evaluasi model.
- Pembentukan dataset.
- Pengumpulan data cuaca.
- Pembuatan laporan berukuran besar.
- Proses integrasi lain yang tidak boleh menghambat request pengguna.

Laravel Queue Worker bertanggung jawab menjalankan pekerjaan asynchronous.

Ketika pekerjaan membutuhkan pemrosesan ANN, Laravel Queue Worker mengirim request ke Python Service melalui internal API.

Training execution berlangsung di Python Service dan tidak boleh menahan request transaksi POS.

Queue driver dan process supervisor ditentukan berdasarkan environment deployment.

## 9.2 Scheduler

Laravel Scheduler digunakan untuk pekerjaan terjadwal.

Contoh pekerjaan:

- Sinkronisasi data cuaca.
- Pembentukan dataset.
- Pemeriksaan kondisi stok.
- Pembuatan laporan terjadwal jika diperlukan.
- Pemicu proses prediction sesuai jadwal yang telah ditetapkan.

Waktu eksekusi harus mengikuti zona waktu Asia/Jakarta dan hari operasional coffee shop.

Jadwal final ditetapkan pada implementasi modul terkait.

Scheduler tidak boleh menganggap pekerjaan berhasil sebelum hasil proses terkait berhasil divalidasi.

---

# 10. STORAGE AND FILE MANAGEMENT

## 10.1 Laravel Storage

Gunakan Laravel Storage untuk mengelola file aplikasi.

Contoh:

- Gambar produk.
- Dokumen laporan.
- PDF struk.
- PDF laporan.
- File ekspor.

File yang bersifat privat tidak boleh disimpan pada direktori publik tanpa mekanisme akses yang sesuai.

## 10.2 Python Artifact Storage

Artifact ANN meliputi:

- Model hasil training.
- Scaler atau preprocessing artifacts.
- Metadata model.
- Evaluation results.
- Konfigurasi preprocessing.
- Referensi dataset version.

Python ANN Service bertanggung jawab menyimpan dan memuat artifact yang digunakan dalam proses machine learning.

Artifact harus memiliki identitas versi agar hasil prediction dapat ditelusuri.

Python mengembalikan metadata dan referensi artifact yang diperlukan kepada Laravel melalui API.

Laravel tidak perlu mengakses direktori internal Python secara langsung.

## 10.3 Backup

Backup harus mencakup:

- Database Laravel.
- File penting aplikasi.
- Model ANN aktif.
- Preprocessing artifacts yang dibutuhkan model aktif.
- Metadata dan referensi artifact.
- Konfigurasi penting yang tidak mengandung secret dalam bentuk terbuka.

Backup harus diuji melalui proses pemulihan secara berkala.

Strategi backup untuk Laravel Storage dan Python Artifact Storage harus ditetapkan berdasarkan lokasi penyimpanan production.

---

# 11. PRODUCTION DEPLOYMENT

## 11.1 Pre-deployment

Sebelum deployment:

1. Pastikan seluruh perubahan sudah masuk repository.
2. Pastikan dependency lock tersedia.
3. Jalankan automated tests.
4. Pastikan environment production sudah disiapkan.
5. Pastikan backup database tersedia.
6. Pastikan konfigurasi Python Service valid.
7. Pastikan koneksi database dan API internal berfungsi.
8. Pastikan Laravel dan Python menggunakan kontrak API yang kompatibel.
9. Pastikan file dan artifact yang diperlukan tersedia.

## 11.2 Laravel Deployment

Urutan umum:

```bash
composer install --no-dev --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

php artisan optimize
```

Jangan menjalankan perintah migrasi production sebelum backup dan pemeriksaan perubahan schema.

## 11.3 Python Deployment

1. Siapkan Python runtime.
2. Buat virtual environment.
3. Install dependency yang sudah dikunci.
4. Siapkan direktori model dan artifact.
5. Konfigurasikan environment production.
6. Jalankan service menggunakan process manager.
7. Periksa health check.
8. Uji komunikasi dari Laravel.
9. Pastikan model dan preprocessing artifacts kompatibel sebelum prediction diaktifkan.

## 11.4 Service Management

Proses yang perlu dikelola:

- Web server.
- PHP application runtime.
- Laravel queue worker.
- Laravel scheduler.
- Python ANN Service.
- Database service.

Proses background harus memiliki mekanisme restart apabila mengalami kegagalan.

Setelah deployment, lakukan pemeriksaan terhadap status seluruh proses yang diperlukan.

---

# 12. SECURITY CONFIGURATION

Ketentuan production:

```dotenv
APP_ENV=production
APP_DEBUG=false
```

Selain itu:

- HTTPS wajib.
- Database tidak boleh terbuka untuk akses publik.
- Python Service hanya dapat diakses melalui jaringan internal.
- Secret harus dikelola secara aman.
- Permission file dan direktori harus dibatasi.
- Endpoint internal harus memiliki autentikasi.
- Error production tidak boleh menampilkan stack trace kepada pengguna.
- Log tidak boleh menyimpan password, token, atau secret.
- Environment development tidak boleh menggunakan credential production.
- Akses ke artifact model harus dibatasi sesuai kebutuhan service.

---

# 13. MONITORING AND LOGGING

Monitoring mencakup:

| Component | Monitoring |
|---|---|
| Laravel | Application error |
| Database | Connection dan availability |
| Queue | Failed jobs dan backlog |
| Scheduler | Status pekerjaan terjadwal |
| Python | Health check dan service errors |
| ANN | Training status dan evaluation metrics |
| Artifact Storage | Ketersediaan artifact dan kegagalan penyimpanan |
| Laravel Storage | Kapasitas dan kegagalan penyimpanan |

Informasi yang berkaitan dengan integrasi Laravel–Python harus memungkinkan penelusuran berdasarkan request ID.

Setiap kegagalan pekerjaan penting harus dapat ditelusuri melalui log.

Jangan mencatat secret, token, atau informasi sensitif yang tidak diperlukan.

---

# 14. ROLLBACK AND RECOVERY

Jika deployment mengalami kegagalan:

1. Identifikasi sumber kegagalan.
2. Hentikan proses deployment lanjutan.
3. Pulihkan versi aplikasi sebelumnya jika diperlukan.
4. Pulihkan konfigurasi yang valid.
5. Periksa kompatibilitas database.
6. Pastikan queue worker dan Python Service berjalan.
7. Pastikan model dan preprocessing artifacts yang dibutuhkan tersedia.
8. Jalankan smoke test.
9. Dokumentasikan penyebab dan tindakan pemulihan.

Database rollback tidak boleh dilakukan secara sembarangan karena dapat menyebabkan kehilangan transaksi.

Migration yang bersifat destruktif harus memiliki strategi pemulihan yang jelas.

Rollback aplikasi tidak otomatis berarti database harus dikembalikan ke kondisi sebelumnya. Strategi rollback harus mempertimbangkan kompatibilitas schema dan data.

---

# 15. DEPLOYMENT ACCEPTANCE CRITERIA

Deployment aplikasi dianggap berhasil apabila:

- Aplikasi Laravel dapat diakses melalui HTTPS.
- Login dan authorization berfungsi.
- Database dapat diakses.
- POS dapat menyelesaikan transaksi.
- Inventory diperbarui dengan konsisten.
- Laravel dapat berkomunikasi dengan Python melalui API internal.
- Kegagalan Python tidak menghentikan POS.
- Queue dan scheduler berjalan sesuai konfigurasi.
- PDF dapat dihasilkan.
- Logging berfungsi.
- Backup dapat dibuat.
- Proses pemulihan backup telah diuji.

Untuk fitur ANN, apabila model yang telah dilatih dan disetujui tersedia:

- Python Service dapat memuat model yang sesuai.
- Preprocessing artifacts tersedia dan kompatibel.
- Prediction dapat dijalankan melalui API.
- Hasil prediction dapat divalidasi dan disimpan Laravel.
- Model version dan timestamp dapat ditelusuri.

Ketersediaan model ANN tidak menjadi prasyarat agar fungsi POS dasar dapat beroperasi.

---

# 16. UNRESOLVED DEPLOYMENT DECISIONS

Hal berikut belum ditetapkan secara final:

| Item | Status |
|---|---|
| Hosting provider | TBD |
| VPS specification | TBD |
| Operating system production | TBD |
| Domain | TBD |
| SSL provider | TBD |
| Database engine and version | TBD |
| Docker usage | Optional, not required |
| Queue driver | TBD |
| Process supervisor | TBD |
| Backup destination | TBD |
| Laravel file storage strategy | TBD |
| Python artifact storage strategy | TBD |
| Python API framework | TBD |
| Production monitoring provider | TBD |
| Weather provider | TBD; Open-Meteo is an initial candidate |

Keputusan tersebut ditentukan berdasarkan kebutuhan aktual dan kemampuan deployment.

AI coding assistant tidak boleh menganggap keputusan TBD sebagai keputusan final.

---

# 17. DOCUMENT REFERENCES

Dokumen terkait:

- `PROJECT_MASTER.md`
- `PROJECT_SPEC.md`
- `DATABASE_DESIGN.md`
- `ANN_SPECIFICATION.md`
- `INTEGRATION_SPECIFICATION.md`
- `API_CONTRACT.md`
- `TESTING_STRATEGY.md`
- `IMPLEMENTATION_PLAN.md`
- `AI_RULES.md`
- `PROGRESS.md`

Setiap perubahan arsitektur deployment yang memengaruhi integrasi, database, atau proses ANN harus diselaraskan dengan dokumen terkait.

---

# 18. DOCUMENT GOVERNANCE

1. Perubahan deployment architecture harus dievaluasi terhadap `PROJECT_MASTER.md`.
2. Perubahan database engine atau konfigurasi database harus diselaraskan dengan `DATABASE_DESIGN.md`.
3. Perubahan komunikasi Laravel–Python harus diselaraskan dengan `INTEGRATION_SPECIFICATION.md` dan `API_CONTRACT.md`.
4. Perubahan proses ANN dan artifact harus diselaraskan dengan `ANN_SPECIFICATION.md`.
5. Perubahan environment dan deployment harus diperiksa terhadap kebutuhan testing.
6. Keputusan TBD tidak boleh diubah menjadi keputusan final tanpa persetujuan.
7. Perubahan yang disetujui harus diperbarui pada dokumen terkait.

---

**END OF DEPLOYMENT_GUIDE.md**
