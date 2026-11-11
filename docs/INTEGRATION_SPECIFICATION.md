
# INTEGRATION_SPECIFICATION.md

# SEUMPAMA POS
## System Integration Specification

| Informasi | Keterangan |
|---|---|
| Nama Proyek | Seumpama POS |
| Jenis Aplikasi | Web-Based POS and Inventory Management System |
| Objek Penelitian | Coffee Shop Seumpama Bunga |
| Lokasi | Tigaraksa, Kabupaten Tangerang, Banten |
| Backend | Laravel 12 |
| Frontend | Laravel Blade |
| Machine Learning Service | Python |
| ANN Framework | TensorFlow / Keras |
| Database | MySQL 8+ atau MariaDB kompatibel |
| Weather Provider | Open-Meteo sebagai provider awal |
| API Version | v1 |
| Timezone | Asia/Jakarta |
| Status Dokumen | Draft - Technical Planning |
| Versi Dokumen | 1.2 |

---

# 1. Purpose

Dokumen ini mendefinisikan rancangan integrasi antar-komponen dalam sistem Seumpama POS.

Integrasi yang dibahas mencakup:

1. Laravel Application dengan Python ANN Service.
2. Laravel Application dengan Weather Service.
3. Laravel Application dengan thermal receipt printer.
4. Laravel Application dengan cash drawer.
5. Laravel Application dengan PDF Generator.
6. Laravel Application dengan database.
7. Laravel Application dengan background processing dan queue.

Dokumen ini bertujuan memastikan setiap komponen memiliki tanggung jawab yang jelas, pertukaran data yang konsisten, serta mekanisme penanganan kegagalan yang tidak mengganggu operasional POS.

Dokumen ini menjadi acuan integrasi teknis dan harus konsisten dengan:

- `PROJECT_MASTER.md`
- `PROJECT_SPEC.md`
- `DATABASE_DESIGN.md`
- `ANN_SPECIFICATION.md`
- `API_CONTRACT.md`
- `DEPLOYMENT_GUIDE.md`
- `TESTING_STRATEGY.md`

Dokumen ini tidak menggantikan business rules atau spesifikasi machine learning pada dokumen terkait.

---

# 2. Integration Principles

Integrasi sistem mengikuti prinsip berikut.

## 2.1 Separation of Concerns

Setiap komponen hanya bertanggung jawab atas fungsi yang telah ditetapkan.

Laravel menangani operasional aplikasi dan business rules.

Python menangani proses machine learning.

Weather Service menyediakan data cuaca.

Printer dan PDF Generator menangani output dokumen.

## 2.2 Loose Coupling

Komponen harus berkomunikasi melalui interface yang jelas.

Perubahan internal pada satu komponen tidak boleh mengharuskan perubahan besar pada komponen lain selama kontrak integrasinya tetap kompatibel.

## 2.3 Failure Isolation

Kegagalan komponen eksternal tidak boleh menghentikan fungsi utama POS.

Contoh:

- Python Service tidak tersedia: transaksi POS tetap berjalan.
- Weather API gagal: pencatatan transaksi tetap berjalan.
- Printer gagal: transaksi yang sudah tersimpan tidak dibatalkan.
- PDF Generator gagal: data transaksi tetap tersimpan.

## 2.4 Replaceable Provider

Komponen eksternal yang berpotensi diganti harus diakses melalui abstraction atau adapter.

Contoh:

- Weather provider dapat diganti tanpa mengubah business logic utama.
- Printer integration tidak boleh terlalu bergantung pada satu merek perangkat.
- Python API harus memiliki kontrak yang jelas agar implementasi internal dapat dikembangkan tanpa mengubah Laravel secara sembarangan.

## 2.5 Data Ownership

Laravel merupakan pemilik data operasional utama.

Python tidak boleh mengubah database operasional Laravel secara langsung.

Setiap perubahan data bisnis harus melalui aplikasi Laravel.

## 2.6 Traceability

Proses integrasi harus dapat ditelusuri melalui identifier, timestamp, status, dan log yang relevan.

Untuk integrasi ANN, informasi yang perlu ditelusuri mencakup:

- Dataset version.
- Dataset checksum.
- Training request ID.
- Training ID.
- Model version.
- Feature schema version.
- Prediction timestamp.

---

# 3. Integration Architecture

## 3.1 High-Level Architecture

```text
                         USER
                           |
                           v
                  WEB BROWSER / TABLET
                           |
                           v
                  LARAVEL APPLICATION
                           |
          +----------------+----------------+
          |                |                |
          v                v                v
      DATABASE         PYTHON ANN       WEATHER SERVICE
      MYSQL /          SERVICE                |
      MARIADB              |                  v
          |                |             WEATHER API
          |                |
          |                v
          |            MODEL ARTIFACTS
          |
          +----------------+----------------+
                           |
                           v
                    APPLICATION LAYER
                           |
             +-------------+-------------+
             |             |             |
             v             v             v
        THERMAL        PDF GENERATOR   BACKGROUND
        PRINTER                         PROCESSING
             |
             v
        CASH DRAWER
```

Diagram tersebut menggambarkan hubungan logis antar-komponen, bukan detail deployment final.

## 3.2 Main Components

| Component | Responsibility |
|---|---|
| Laravel Application | Business logic, POS, inventory, authentication, reporting, orchestration |
| MySQL / MariaDB | Operational database |
| Python ANN Service | Dataset technical validation, preprocessing, training, evaluation, inference |
| ANN Model Storage | Penyimpanan model dan preprocessing artifacts |
| Weather Service | Pengambilan dan pengelolaan data cuaca |
| Weather Provider | Penyedia data cuaca eksternal |
| Thermal Printer | Pencetakan receipt |
| Cash Drawer | Penyimpanan uang tunai dan pembukaan melalui mekanisme printer |
| PDF Generator | Pembuatan dokumen PDF |
| Background Processing | Menjalankan proses asynchronous |

## 3.3 Data Flow Ownership

Laravel bertanggung jawab atas:

- Users dan permissions.
- Products dan categories.
- Ingredients.
- Recipes dan BOM.
- Transactions.
- Payments.
- Inventory.
- Stock movements.
- Purchasing records.
- Business day.
- Weather records.
- Dataset business records.
- Dataset version metadata.
- ANN training metadata.
- Prediction results.
- Stock forecasts.
- Replenishment recommendations.

Python bertanggung jawab atas:

- Machine learning preprocessing.
- Feature engineering.
- Training execution.
- Model evaluation.
- Model artifacts.
- Preprocessing artifacts.
- Inference execution.

Python tidak memiliki akses langsung untuk mengubah data operasional pada database Laravel.

---

# 4. Laravel - Python ANN Service Integration

## 4.1 Integration Purpose

Integrasi Laravel dan Python digunakan untuk mendukung prediksi kebutuhan harian bahan baku menggunakan Artificial Neural Network.

Laravel menyiapkan data berdasarkan kondisi operasional coffee shop.

Python memproses data tersebut menggunakan pipeline machine learning dan mengembalikan hasil training atau prediction.

Integrasi harus memastikan:

1. Data yang digunakan Python sesuai business rules Laravel.
2. Dataset dapat diidentifikasi berdasarkan versinya.
3. Dataset yang divalidasi sama dengan dataset yang digunakan untuk training.
4. Model yang digunakan prediction dapat diidentifikasi.
5. Hasil prediction dapat ditelusuri.
6. Kegagalan Python tidak menghentikan POS.

## 4.2 Responsibility Boundary

### Laravel Responsibilities

Laravel bertanggung jawab atas:

1. Mengambil data transaksi dan inventory.
2. Mengelola data produk, bahan baku, dan recipe.
3. Menghitung theoretical ingredient consumption.
4. Mengelompokkan data berdasarkan operational business day.
5. Menentukan business date dan timezone.
6. Menentukan data yang valid untuk dataset.
7. Menyiapkan dataset business records.
8. Membuat dataset version.
9. Menyimpan immutable dataset snapshot.
10. Menghasilkan dataset checksum berdasarkan aturan serialisasi yang disepakati.
11. Mengirim dataset ke Python melalui API.
12. Mengelola training request.
13. Menghasilkan training request ID.
14. Mengelola status training berdasarkan response Python.
15. Menyimpan training metadata.
16. Mengelola model metadata yang dibutuhkan aplikasi.
17. Menentukan model version yang digunakan untuk prediction berdasarkan model aktif yang tercatat pada Laravel.
18. Menyiapkan prediction payload.
19. Memvalidasi response prediction.
20. Menyimpan prediction results.
21. Menghitung stock forecast.
22. Menghasilkan replenishment recommendation.

### Python Responsibilities

Python bertanggung jawab atas:

1. Memvalidasi struktur request.
2. Memverifikasi dataset checksum.
3. Memvalidasi kelengkapan teknis dataset.
4. Melakukan preprocessing.
5. Melakukan feature engineering.
6. Melakukan encoding dan normalization.
7. Melakukan chronological dataset split.
8. Melatih ANN model.
9. Mengevaluasi model.
10. Menghasilkan model artifacts.
11. Mengelola preprocessing artifacts.
12. Mengelola model version pada sisi Python.
13. Menjalankan inference.
14. Mengembalikan prediction result.
15. Mengembalikan training status dan evaluation result.

Python tidak boleh:

- Mengakses database Laravel secara langsung.
- Mengubah transaksi.
- Mengubah stok aktual.
- Mengubah recipe.
- Mengubah data produk.
- Membuat purchase order.
- Menentukan keputusan pembelian otomatis.

## 4.3 Communication Mechanism

Komunikasi menggunakan internal REST API.

| Item | Specification |
|---|---|
| Protocol | HTTP / HTTPS |
| Data Format | JSON |
| Encoding | UTF-8 |
| API Prefix | `/api/v1` |
| Authentication | Bearer Token sebagai rancangan awal |
| Request Initiator | Laravel |
| API Provider | Python ANN Service |

Contoh endpoint:

```text
GET  /api/v1/health
GET  /api/v1/model/status
POST /api/v1/datasets/validate
POST /api/v1/predictions/demand
POST /api/v1/model/train
GET  /api/v1/model/train/{training_id}
```

Kontrak request dan response secara lengkap mengikuti `API_CONTRACT.md`.

Framework Python, mekanisme authentication final, dan konfigurasi jaringan masih perlu ditetapkan.

## 4.4 Dataset Preparation and Validation

### Purpose

Memastikan dataset yang akan digunakan untuk training memenuhi business rules dan persyaratan teknis machine learning.

### Workflow

```text
Laravel
   |
   v
Retrieve Historical Operational Data
   |
   v
Business Data Validation
   |
   v
Calculate Theoretical Consumption
   |
   v
Group by Business Date
   |
   v
Prepare Dataset Snapshot
   |
   v
Generate Dataset Version and Checksum
   |
   v
Store Immutable Dataset Snapshot
   |
   v
POST /api/v1/datasets/validate
   |
   v
Python ANN Service
   |
   v
Verify Dataset Checksum
   |
   v
Technical Dataset Validation
   |
   v
Return Validation Result
   |
   v
Laravel
   |
   v
Store Validation Metadata
```

### Laravel Processing

Laravel melakukan:

1. Mengambil transaksi yang relevan.
2. Menghubungkan transaksi dengan recipe yang berlaku.
3. Menghitung theoretical ingredient consumption.
4. Mengelompokkan data berdasarkan business date.
5. Memisahkan data valid, missing, closed, dan simulated.
6. Menentukan target ingredient.
7. Menyusun dataset snapshot.
8. Menghasilkan dataset version ID.
9. Menghasilkan dataset checksum.
10. Menyimpan snapshot secara immutable.

### Python Processing

Python melakukan:

1. Memeriksa struktur payload.
2. Memeriksa required fields.
3. Memeriksa tipe data.
4. Memeriksa format tanggal.
5. Memeriksa nilai numerik.
6. Memverifikasi checksum.
7. Memeriksa konsistensi unit dan feature.
8. Memeriksa kecukupan data untuk kebutuhan preprocessing dan training.
9. Mengembalikan validation result.

### Dataset Identity

Setiap dataset snapshot harus memiliki:

- `dataset_version_id`
- `dataset_checksum`
- `period.start_date`
- `period.end_date`

`dataset_version_id` mengidentifikasi versi dataset.

`dataset_checksum` digunakan untuk memverifikasi integritas isi dataset.

### Dataset Immutability

Ketentuan:

1. Dataset snapshot yang sudah dibuat tidak boleh diubah.
2. Perubahan pada data sumber yang memengaruhi snapshot harus menghasilkan dataset version baru.
3. Dataset version baru harus menjalani validasi ulang.
4. Training harus menggunakan snapshot yang telah divalidasi.
5. Laravel harus dapat mengambil kembali snapshot yang sama berdasarkan dataset version.
6. Python tidak boleh mengganti atau memodifikasi dataset business records asli.

### Dataset Checksum

Checksum digunakan untuk memastikan dataset yang dikirim saat training identik dengan dataset yang telah divalidasi.

Ketentuan:

1. Laravel menghasilkan checksum dari representasi dataset yang disepakati.
2. Python melakukan verifikasi checksum.
3. Laravel dan Python harus menggunakan aturan canonical serialization yang sama.
4. Field checksum itu sendiri tidak boleh dimasukkan ke dalam perhitungan checksum.
5. Urutan record dan representasi nilai harus konsisten.
6. Perubahan isi dataset harus menghasilkan checksum berbeda.
7. Python menolak request apabila checksum tidak sesuai.

Algoritma checksum dan aturan canonical serialization masih berstatus TBD.

Implementasi checksum belum boleh dilakukan secara terpisah pada Laravel dan Python sebelum aturan tersebut disepakati.

### Important Notes

- Validasi business rules dilakukan Laravel.
- Validasi teknis dataset dilakukan Python.
- Dataset validation tidak menjamin model akan memiliki akurasi yang baik.
- Kriteria kelayakan dataset mengikuti `ANN_SPECIFICATION.md`.
- Data kosong tidak boleh otomatis dianggap sebagai zero consumption.
- Data simulasi harus dibedakan dari data aktual.

## 4.5 ANN Model Training

### Purpose

Melatih ANN model menggunakan dataset snapshot yang telah berhasil divalidasi.

Training berjalan secara asynchronous agar tidak menghambat aktivitas utama aplikasi.

### Workflow

```text
Laravel
   |
   v
Select Validated Dataset Snapshot
   |
   v
Retrieve Same Immutable Snapshot
   |
   v
Create Training Request ID
   |
   v
POST /api/v1/model/train
   |
   v
Python ANN Service
   |
   v
Verify Training Request ID
   |
   v
Verify Dataset Version and Checksum
   |
   v
Check Previous Validation Result
   |
   v
Preprocessing
   |
   v
Chronological Dataset Split
   |
   v
ANN Training
   |
   v
Model Evaluation
   |
   v
Save Model Artifacts
   |
   v
Return Training ID
   |
   v
Laravel
   |
   v
Store Training Metadata
   |
   v
Poll Training Status
   |
   v
Retrieve Evaluation Result
   |
   v
Model Review and Approval
```

### Training Request

Laravel mengirimkan:

- `training_request_id`
- `dataset_version_id`
- `dataset_checksum`
- `target_ingredients`
- `training_config`
- `dataset`

Dataset pada training request harus merupakan snapshot yang sama dengan dataset yang sebelumnya divalidasi.

Laravel tidak boleh membuat ulang dataset dengan isi berbeda hanya karena akan memulai training.

### Training Request Identity

`training_request_id` dibuat oleh Laravel.

Fungsinya sebagai identitas unik permintaan training dan mekanisme pencegahan proses duplikat.

Ketentuan:

1. Setiap permintaan training memiliki `training_request_id`.
2. Jika pengiriman request mengalami timeout, Laravel menggunakan ID yang sama ketika melakukan retry.
3. Python memeriksa apakah request tersebut sudah terdaftar.
4. Request dengan ID sama dan payload identik harus mengembalikan proses training yang sudah ada.
5. Request dengan ID sama tetapi payload berbeda harus ditolak.
6. Python menghasilkan `training_id` untuk mengidentifikasi proses training.
7. Laravel menyimpan kedua ID tersebut.

### Dataset Consistency

Sebelum training dimulai, Python harus memverifikasi:

- Dataset version telah tervalidasi.
- Dataset checksum sesuai dengan hasil validasi.
- Snapshot yang diterima konsisten.
- Dataset memenuhi persyaratan teknis training.

Jika salah satu persyaratan tidak terpenuhi, training harus ditolak.

### Asynchronous Execution

Training tidak boleh dijalankan sebagai proses HTTP blocking yang menunggu sampai seluruh training selesai.

Python menerima request, mendaftarkan proses training, lalu mengembalikan response `202 Accepted` beserta `training_id`.

Laravel menyimpan identitas training dan memeriksa statusnya melalui endpoint training status.

Pemeriksaan status dilakukan menggunakan background processing dan polling dengan batas waktu yang ditentukan.

### Training Result

Python mengembalikan informasi:

- Training ID.
- Training request ID.
- Dataset version ID.
- Dataset checksum.
- Training status.
- Model version jika training selesai.
- Evaluation metrics.
- Baseline comparison.

Laravel menyimpan metadata tersebut untuk kebutuhan audit, pelacakan, dan pengelolaan model.

### Important Notes

- Model berstatus completed belum otomatis menjadi active.
- Model baru harus melalui evaluasi dan persetujuan.
- Training configuration mengikuti `ANN_SPECIFICATION.md`.
- Training payload mengikuti schema final yang ditetapkan pada `API_CONTRACT.md`.
- Python tidak boleh mengubah dataset business records asli.

## 4.6 Demand Prediction

### Purpose

Menghasilkan estimasi kebutuhan harian bahan baku berdasarkan model ANN yang telah disetujui dan diaktifkan.

### Workflow

```text
Laravel
   |
   v
Retrieve Historical Data
   |
   v
Prepare Prediction Features
   |
   v
Determine Active Model Version
   |
   v
Determine Feature Schema Version
   |
   v
POST /api/v1/predictions/demand
   |
   v
Python ANN Service
   |
   v
Validate Prediction Payload
   |
   v
Verify Model Version
   |
   v
Verify Feature Schema Compatibility
   |
   v
Load Model and Preprocessing Artifacts
   |
   v
Perform ANN Inference
   |
   v
Return Prediction Result
   |
   v
Laravel
   |
   v
Validate Prediction Response
   |
   v
Store Prediction Result
   |
   v
Calculate Inventory Forecast
   |
   v
Generate Replenishment Recommendation
```

### Laravel Processing

Laravel bertanggung jawab untuk:

1. Mengambil data historis.
2. Menentukan prediction date.
3. Menentukan business timezone.
4. Menyiapkan feature sesuai ANN specification.
5. Menentukan model version yang aktif.
6. Menentukan feature schema version yang sesuai.
7. Mengirim prediction request.
8. Memvalidasi response.
9. Menyimpan prediction result.

### Python Processing

Python bertanggung jawab untuk:

1. Memvalidasi prediction request.
2. Memastikan model version tersedia.
3. Memastikan feature schema kompatibel.
4. Memuat model artifacts.
5. Memuat preprocessing artifacts.
6. Melakukan preprocessing inference.
7. Menjalankan ANN inference.
8. Mengembalikan hasil prediction.

### Model Version Consistency

Laravel menentukan model version yang digunakan berdasarkan metadata model aktif yang dikelola aplikasi.

Laravel mengirimkan:

- `model_version`
- `feature_schema_version`

Python harus memastikan kedua identifier tersebut sesuai dengan model dan preprocessing artifacts yang tersedia.

Python tidak boleh diam-diam menggunakan model version lain ketika model yang diminta tidak tersedia atau tidak kompatibel.

Jika model tidak tersedia atau feature schema tidak cocok, Python harus mengembalikan error yang sesuai.

Response harus mengembalikan model version dan feature schema version yang benar-benar digunakan.

Laravel harus memverifikasi bahwa kedua identifier tersebut sesuai dengan request.

### Prediction Data Integrity

Prediction harus:

1. Menggunakan feature yang sesuai dengan ANN specification.
2. Menggunakan preprocessing yang konsisten dengan training.
3. Tidak menggunakan data masa depan yang belum tersedia pada waktu prediction dibuat.
4. Membedakan actual weather dan weather forecast.
5. Mempertahankan satuan feature dan target.
6. Memiliki prediction timestamp.
7. Memiliki model version.
8. Memiliki feature schema version.

### Prediction Failure

Jika prediction gagal:

- Laravel mencatat kegagalan.
- Prediction invalid tidak boleh disimpan sebagai hasil valid.
- Sistem dapat menampilkan prediksi valid terakhir dengan timestamp yang jelas.
- Sistem tidak boleh menganggap kegagalan prediction sebagai kebutuhan stok nol.
- Operasional POS tetap berjalan.

### Important Notes

Python hanya menghasilkan demand prediction.

Perhitungan stock forecast dan replenishment recommendation tetap menjadi tanggung jawab Laravel.

## 4.7 Data Contract and Version Consistency

### General Rules

Kontrak komunikasi Laravel dan Python mengikuti `API_CONTRACT.md`.

Ketentuan:

1. Request dan response menggunakan JSON.
2. API menggunakan version prefix `/api/v1`.
3. Dataset validation dan training menggunakan dataset snapshot yang sama.
4. Dataset snapshot memiliki `dataset_version_id`.
5. Dataset snapshot memiliki `dataset_checksum`.
6. Training request memiliki `training_request_id`.
7. Python menghasilkan `training_id`.
8. Prediction request mengidentifikasi `model_version`.
9. Prediction request mengidentifikasi `feature_schema_version`.
10. Response harus mengembalikan identifier yang diperlukan untuk verifikasi.
11. Laravel harus memvalidasi response sebelum menyimpan hasil.

### Identifier Ownership

| Identifier | Generated By | Purpose |
|---|---|---|
| `dataset_version_id` | Laravel | Identitas versi dataset |
| `dataset_checksum` | Laravel | Identitas integritas snapshot |
| `training_request_id` | Laravel | Identitas request training dan idempotency |
| `training_id` | Python | Identitas proses training |
| `model_version` | Python / model lifecycle | Identitas model ANN |
| `feature_schema_version` | Ditetapkan melalui ANN schema governance | Identitas struktur feature |

Aturan final pembentukan model version dan feature schema version harus diselaraskan dengan `ANN_SPECIFICATION.md` dan `API_CONTRACT.md`.

### Dataset Transfer

Dataset dikirim melalui JSON request body.

Laravel tetap menyimpan immutable dataset snapshot agar dapat dikirim ulang dan ditelusuri.

Batas ukuran payload dan strategi transfer untuk dataset berukuran besar masih perlu ditetapkan.

Jika nantinya diperlukan mekanisme transfer lain, perubahan harus dilakukan melalui pembaruan API Contract dan Integration Specification.

### Dataset Validation and Training Consistency

Alur yang wajib dipenuhi:

1. Laravel membuat dataset snapshot.
2. Laravel menghasilkan dataset version dan checksum.
3. Laravel menyimpan snapshot.
4. Laravel mengirim snapshot untuk validasi.
5. Python memverifikasi checksum.
6. Python mengembalikan hasil validasi.
7. Laravel menyimpan hasil validasi.
8. Laravel mengambil snapshot yang sama untuk training.
9. Laravel mengirim ulang snapshot beserta version dan checksum.
10. Python memverifikasi kembali identitas dan checksum.
11. Python hanya memulai training jika dataset sesuai dengan hasil validasi.

Dataset baru harus memiliki version baru dan harus menjalani validasi ulang.

### Feature Schema Consistency

Feature schema harus konsisten antara training dan prediction.

Ketentuan:

- Feature name harus konsisten.
- Feature type harus konsisten.
- Feature unit harus konsisten.
- Feature ordering harus konsisten apabila preprocessing mengandalkan urutan.
- Missing value policy harus konsisten.
- Preprocessing artifacts harus sesuai dengan model version.
- Feature schema version harus dapat ditelusuri.

Detail feature mengikuti `ANN_SPECIFICATION.md`.

### Payload Size

Payload dataset harus memiliki batas ukuran.

Nilai final batas ukuran dan mekanisme transfer dataset besar masih berstatus TBD.

Laravel dan Python harus menghindari request payload yang terlalu besar sehingga menyebabkan timeout atau penggunaan memory berlebihan.

---

## 4.8 Ingredient Consumption Data Integration

### Purpose

Menentukan sumber data konsumsi bahan baku yang digunakan untuk dataset ANN.

Laravel menghitung theoretical ingredient consumption berdasarkan transaksi dan recipe yang berlaku.

### Consumption Formula

```text
Theoretical Ingredient Consumption
=
Quantity Sold × Recipe Quantity
```

Perhitungan dilakukan untuk setiap ingredient yang digunakan pada produk yang terjual.

### Data Source

Data yang digunakan dapat berasal dari:

- Completed sales transactions.
- Transaction details.
- Recipe records.
- Recipe version history.
- Ingredient records.
- Operational business date.

### Business Rules

1. Transaksi yang belum selesai tidak dihitung sebagai konsumsi final.
2. Transaksi yang dibatalkan tidak dihitung sebagai konsumsi.
3. Transaksi yang direversal harus ditangani sesuai business rules.
4. Recipe yang berlaku pada saat transaksi harus digunakan.
5. Perubahan recipe tidak boleh mengubah perhitungan historis secara retroaktif.
6. Perhitungan harus menggunakan operational business date.
7. Data aktual dan data simulasi harus dibedakan.
8. Data konsumsi teoritis tidak boleh dianggap sebagai hasil pengukuran fisik stok.

### Missing and Zero Consumption

Sistem harus membedakan:

| Condition | Treatment |
|---|---|
| Zero consumption | Konsumsi memang bernilai nol |
| Missing consumption | Data tidak tersedia |
| Closed business day | Tidak ada operasional |
| Partial operational data | Data operasional tidak lengkap |
| Invalid transaction data | Tidak digunakan sampai diperbaiki |
| Simulated data | Diberi penanda simulasi |

Python tidak boleh menyimpulkan missing consumption sebagai zero consumption tanpa kebijakan yang jelas.

---

## 4.9 Model Versioning and Activation Integration

### Purpose

Memastikan model yang digunakan dalam prediction dapat ditelusuri dan dikendalikan.

### Model Lifecycle

```text
Training
   |
   v
Candidate Model
   |
   v
Model Evaluation
   |
   v
Model Review
   |
   +--------------------+
   |                    |
   v                    v
Approved              Rejected
   |                    |
   v                    v
Activation            Archive
   |
   v
Active Model
   |
   v
Prediction
   |
   v
Archived when replaced
```

### Responsibilities

Python bertanggung jawab atas:

- Menyimpan model artifacts.
- Mengidentifikasi model version.
- Mengelola model yang tersedia.
- Menyediakan model untuk inference.
- Mengembalikan metadata model.

Laravel bertanggung jawab atas:

- Menyimpan metadata model yang diperlukan aplikasi.
- Mengelola status model dalam konteks aplikasi.
- Mengendalikan alur persetujuan aktivasi.
- Menentukan model version yang diminta untuk prediction.
- Mencatat riwayat penggunaan model.

### Activation Rules

1. Model hasil training berstatus candidate.
2. Model candidate harus dievaluasi.
3. Model tidak otomatis menjadi active setelah training selesai.
4. Aktivasi harus melalui mekanisme persetujuan.
5. Model version yang aktif harus tersedia pada Python Service.
6. Laravel hanya mengirim prediction request untuk model version yang disetujui dan tersedia.
7. Model sebelumnya harus tetap dapat ditelusuri.
8. Prediction history harus menyimpan model version yang digunakan.

Mekanisme aktivasi final dan sinkronisasi status model harus ditetapkan sebelum implementasi.

---

## 4.10 Model Artifact Storage Integration

### Purpose

Mengatur penyimpanan model ANN dan preprocessing artifacts.

### Python Storage Responsibilities

Python menyimpan artifacts yang diperlukan untuk:

- Model inference.
- Model reproduction.
- Preprocessing consistency.
- Model evaluation.
- Model version tracking.

Contoh artifact:

- Model weights.
- Model architecture.
- Scaler.
- Encoder.
- Feature configuration.
- Preprocessing metadata.

### Laravel Metadata Responsibilities

Laravel menyimpan referensi dan metadata yang diperlukan untuk aplikasi.

Contoh metadata:

- Model version.
- Training ID.
- Dataset version.
- Dataset checksum.
- Feature schema version.
- Training timestamp.
- Evaluation metrics.
- Model status.
- Artifact reference.

### Storage Rules

1. Python menyimpan file model pada storage yang dikonfigurasi.
2. Laravel tidak perlu menyimpan model weights secara langsung.
3. Laravel menyimpan metadata model yang diperlukan aplikasi.
4. Setiap model version harus memiliki artifact reference.
5. Model aktif harus dapat ditemukan oleh Python.
6. Model lama tidak boleh dihapus jika masih dibutuhkan untuk audit atau reproduksi.
7. Backup artifact harus menjadi bagian dari deployment dan recovery planning.

Lokasi final storage dan strategi backup masih berstatus TBD.

---

## 4.11 Failure Handling

### Python Service Failure

Jika Python Service tidak tersedia:

- POS tetap berjalan.
- Transaksi tetap dapat diproses.
- Inventory tetap dapat diakses.
- Prediction dapat ditunda.
- Training dapat ditunda.
- Laravel mencatat kegagalan integrasi.
- Background job dapat dijadwalkan ulang sesuai retry policy.

### Invalid Dataset

Jika dataset tidak valid:

- Python mengembalikan validation error.
- Laravel menyimpan hasil validasi.
- Training tidak boleh dimulai.
- Dataset harus diperbaiki atau dibuat ulang.
- Dataset baru harus memiliki version baru jika isi snapshot berubah.

### Dataset Checksum Mismatch

Jika checksum tidak cocok:

- Python menolak request.
- Training tidak boleh dimulai.
- Laravel mencatat kegagalan integritas dataset.
- Dataset harus diperiksa.
- Dataset tidak boleh dipaksa masuk ke proses training.

### Training Failure

Jika training gagal:

- Python mengembalikan status failed.
- Laravel menyimpan status training.
- Error dicatat untuk kebutuhan debugging.
- Model gagal tidak boleh menjadi active.
- Retry dilakukan hanya setelah penyebab kegagalan dievaluasi.

### Prediction Failure

Jika prediction gagal:

- Laravel tidak menyimpan response sebagai prediction valid.
- Error dicatat.
- Sistem dapat menampilkan prediction terakhir yang valid dengan timestamp.
- Operasional POS tetap berjalan.

### Weather Failure

Jika Weather API gagal:

- Sistem tetap dapat menjalankan transaksi.
- Data cuaca yang tidak tersedia harus ditandai.
- Data cuaca tidak boleh dibuat-buat.
- Proses ANN harus mengikuti missing weather policy.

---

## 4.12 Background Processing and Queue

### Purpose

Memisahkan proses yang membutuhkan waktu atau bergantung pada service eksternal dari request utama pengguna.

### Candidate Background Jobs

- Dataset preparation.
- Dataset validation request.
- ANN training request.
- Training status polling.
- Demand prediction.
- Weather data synchronization.
- PDF report generation.
- Scheduled maintenance tasks.

### Queue Responsibilities

Laravel Queue digunakan untuk menjalankan pekerjaan asynchronous.

Contoh alur:

```text
User / Scheduler
       |
       v
Laravel Application
       |
       v
Dispatch Job
       |
       v
Laravel Queue
       |
       v
Queue Worker
       |
       v
External Service / Internal Processing
       |
       v
Save Result
       |
       v
Update Job Status
```

### Queue Rules

1. Transaksi POS tidak boleh menunggu training ANN.
2. Training harus asynchronous.
3. Prediction dapat dijalankan melalui background processing sesuai kebutuhan.
4. Job harus memiliki batas retry.
5. Job yang gagal harus dapat ditelusuri.
6. Job yang diulang tidak boleh menimbulkan proses bisnis duplikat.
7. Training request harus menggunakan `training_request_id` yang sama saat retry.
8. Polling training status tidak boleh menggunakan loop blocking yang panjang.
9. Queue Worker harus memiliki konfigurasi timeout yang sesuai.
10. Queue monitoring harus tersedia untuk mengetahui job gagal atau tertunda.

Driver queue dan mekanisme deployment final mengikuti `DEPLOYMENT_GUIDE.md`.

---

# 5. Laravel - Weather Service Integration

## 5.1 Purpose

Integrasi Weather Service digunakan untuk memperoleh data cuaca yang dapat digunakan sebagai feature dalam dataset ANN.

Data cuaca yang direncanakan:

- Average temperature.
- Total rainfall.

Provider awal yang direncanakan adalah Open-Meteo.

Provider dapat diganti melalui weather adapter tanpa mengubah business logic utama.

## 5.2 Architecture

```text
Laravel Application
       |
       v
Weather Service
       |
       v
Weather Provider Adapter
       |
       v
External Weather API
       |
       v
Weather Response
       |
       v
Data Validation
       |
       v
Normalize Weather Data
       |
       v
Store Weather Records
       |
       v
ANN Dataset Preparation
```

## 5.3 Responsibility

### Laravel

Laravel bertanggung jawab atas:

- Menjadwalkan pengambilan data cuaca.
- Mengirim request kepada weather provider.
- Memvalidasi response.
- Menormalisasi data.
- Menyimpan weather records.
- Menghubungkan weather data dengan business date.
- Menentukan data cuaca yang tersedia untuk dataset.
- Menangani kegagalan provider.

### Weather Provider

Provider bertanggung jawab menyediakan data cuaca berdasarkan parameter request.

Provider tidak mengubah data operasional Laravel.

## 5.4 Initial Provider

Provider awal:

`Open-Meteo`

Provider ini merupakan pilihan awal dan bukan keputusan bahwa sistem tidak dapat menggunakan provider lain.

Implementasi harus menggunakan abstraction agar pergantian provider tidak mengubah business logic.

## 5.5 Weather Data Requirements

Data cuaca yang direncanakan:

| Feature | Unit | Description |
|---|---|---|
| Average Temperature | °C | Rata-rata suhu untuk periode yang ditentukan |
| Total Rainfall | mm | Total curah hujan untuk periode yang ditentukan |

Detail agregasi mengikuti `ANN_SPECIFICATION.md`.

## 5.6 Actual and Forecast Weather

Sistem harus membedakan:

1. Actual weather: kondisi cuaca yang telah diamati.
2. Forecast weather: estimasi cuaca untuk waktu mendatang.

Keduanya tidak boleh diperlakukan sebagai data yang sama.

Untuk kebutuhan training historis, penggunaan actual weather harus dibedakan dari penggunaan forecast weather.

Untuk prediction masa depan, sistem harus menggunakan informasi cuaca yang memang tersedia pada waktu prediction dibuat.

Forecast yang belum tersedia tidak boleh diganti dengan actual weather dari masa depan.

## 5.7 Business Date Mapping

Weather data harus dikaitkan dengan operational business date.

Jam operasional:

| Hari | Jam Operasional |
|---|---|
| Minggu - Kamis | 15:00 - 23:00 |
| Jumat - Sabtu | 15:00 - 24:00 |

Timezone:

`Asia/Jakarta`

Agregasi weather data harus mempertimbangkan jam operasional dan aturan business date, termasuk batas hari yang melewati tengah malam.

Aturan agregasi final mengikuti `ANN_SPECIFICATION.md`.

## 5.8 Weather Data Validation

Laravel harus memvalidasi:

- Tanggal dan waktu.
- Timezone.
- Temperature unit.
- Rainfall unit.
- Nilai numerik.
- Kelengkapan data.
- Source provider.
- Jenis data actual atau forecast.

Data yang tidak lengkap harus ditandai.

Data yang tidak tersedia tidak boleh otomatis diisi dengan nilai buatan.

## 5.9 Weather Failure Handling

Jika provider tidak tersedia:

1. Laravel mencatat kegagalan.
2. Weather synchronization dapat dijadwalkan ulang.
3. Data cuaca yang belum tersedia ditandai sebagai missing.
4. Transaksi POS tetap berjalan.
5. Dataset ANN mengikuti missing weather policy.
6. Tidak boleh mengarang nilai cuaca untuk melengkapi dataset.

## 5.10 Configuration

Konfigurasi berikut masih perlu ditetapkan:

- Provider API configuration.
- API authentication jika diperlukan.
- Coordinates coffee shop.
- Fetching schedule.
- Historical weather retrieval policy.
- Forecast retrieval policy.
- Request timeout.
- Retry policy.
- Weather storage retention.

---

# 6. Laravel - Thermal Printer Integration

## 6.1 Purpose

Integrasi thermal printer digunakan untuk mencetak receipt transaksi POS.

Printer harus mendukung kebutuhan pencetakan transaksi secara konsisten.

## 6.2 General Architecture

```text
User
 |
 v
Laravel POS
 |
 v
Transaction Saved
 |
 v
Print Request
 |
 v
Printing Mechanism
 |
 v
Thermal Printer
 |
 v
Receipt Output
```

Mekanisme komunikasi printer masih perlu ditetapkan berdasarkan perangkat yang digunakan.

## 6.3 Responsibility

Laravel bertanggung jawab atas:

- Menyusun receipt data.
- Menghasilkan print content.
- Mengirim perintah cetak melalui mekanisme yang ditetapkan.
- Mencatat print request.
- Menampilkan print status.
- Menyediakan fitur reprint.

Printer bertanggung jawab menjalankan perintah cetak.

## 6.4 Transaction Integrity

Ketentuan:

1. Transaksi harus tersimpan sebelum proses cetak dimulai.
2. Kegagalan printer tidak boleh membatalkan transaksi.
3. Kegagalan cetak tidak boleh mengurangi stok untuk kedua kalinya.
4. Reprint tidak boleh membuat transaksi baru.
5. Reprint tidak boleh mengurangi inventory kembali.
6. Status transaksi dan status pencetakan harus dipisahkan.

## 6.5 Print Status

Status pencetakan yang direncanakan:

| Status | Description |
|---|---|
| pending | Menunggu proses cetak |
| processing | Sedang dikirim ke printer |
| completed | Proses cetak dilaporkan selesai |
| failed | Proses cetak gagal |
| unknown | Hasil cetak tidak dapat dipastikan |

Status `completed` menunjukkan keberhasilan yang dilaporkan oleh mekanisme printing, bukan jaminan fisik bahwa kertas benar-benar keluar dengan sempurna.

## 6.6 Reprint

Reprint digunakan ketika receipt perlu dicetak kembali.

Ketentuan:

- Reprint menggunakan data transaksi yang sudah tersimpan.
- Reprint tidak membuat transaksi baru.
- Reprint tidak mengubah pembayaran.
- Reprint tidak mengubah stok.
- Reprint dapat dicatat untuk audit.
- Receipt reprint sebaiknya dapat dibedakan dari cetakan pertama.

## 6.7 Printer Configuration

Detail berikut masih TBD:

- Printer brand dan model.
- Communication interface.
- USB / LAN / Bluetooth.
- Printer command language.
- Paper width.
- Print driver.
- Client-side atau server-side printing.
- Print library.
- Device compatibility.

Pemilihan mekanisme harus mempertimbangkan perangkat aktual dan lingkungan deployment.

---

# 7. Laravel - Cash Drawer Integration

## 7.1 Purpose

Cash drawer digunakan untuk penyimpanan uang tunai dan dibuka melalui mekanisme yang kompatibel dengan thermal printer.

## 7.2 General Architecture

```text
Authorized Cashier Action
          |
          v
Laravel Application
          |
          v
Cash Drawer Command
          |
          v
Compatible Printer Interface
          |
          v
Cash Drawer Kick Signal
          |
          v
Cash Drawer Opens
```

## 7.3 Responsibility

Laravel bertanggung jawab atas:

- Mengatur permission.
- Memastikan tindakan dilakukan oleh pengguna berwenang.
- Mencatat aktivitas pembukaan drawer.
- Mengirim perintah melalui mekanisme yang kompatibel.

Printer bertanggung jawab meneruskan drawer kick signal.

Cash drawer menjalankan mekanisme pembukaan fisik.

## 7.4 Security Rules

1. Cash drawer tidak boleh terbuka secara otomatis tanpa alasan operasional yang jelas.
2. Pembukaan drawer harus melalui tindakan yang diizinkan.
3. Aktivitas pembukaan harus dapat dicatat.
4. Pengguna yang tidak berwenang tidak boleh mengakses fungsi ini.
5. Perintah tidak boleh diulang secara otomatis ketika hasil pembukaan tidak dapat dipastikan.

## 7.5 Failure Handling

Jika perintah pembukaan gagal atau hasilnya tidak diketahui:

- Laravel mencatat status.
- Sistem tidak mengirim perintah berulang tanpa pemeriksaan.
- Pengguna dapat melakukan pemeriksaan perangkat.
- POS tetap berjalan.

Detail command dan kompatibilitas perangkat masih TBD.

---

# 8. Laravel - PDF Generator Integration

## 8.1 Purpose

PDF Generator digunakan untuk menghasilkan dokumen digital dari data yang dikelola Laravel.

Contoh penggunaan:

- Laporan transaksi.
- Laporan penjualan.
- Laporan inventory.
- Laporan stok.
- Laporan pembelian.
- Dokumen operasional lain yang masuk dalam scope.

## 8.2 General Architecture

```text
User Request
     |
     v
Laravel Application
     |
     v
Retrieve Database Records
     |
     v
Prepare Document Data
     |
     v
PDF Generator
     |
     v
Generate PDF
     |
     v
Download / Store / Print
```

## 8.3 Responsibility

Laravel bertanggung jawab atas:

- Mengambil data.
- Memvalidasi permission.
- Menentukan periode laporan.
- Menyiapkan data dokumen.
- Menentukan format dokumen.
- Mengirim data ke PDF Generator.
- Menyediakan hasil kepada pengguna.

PDF Generator bertanggung jawab mengubah data yang diberikan menjadi dokumen PDF.

## 8.4 Data Integrity

1. PDF harus menggunakan data yang berasal dari Laravel.
2. PDF Generator tidak boleh mengubah database operasional.
3. Pembuatan PDF tidak boleh mengubah transaksi.
4. Pembuatan PDF tidak boleh mengubah stok.
5. Dokumen harus mengikuti filter dan permission pengguna.
6. Periode laporan harus ditampilkan secara jelas.

## 8.5 PDF Configuration

Library dan konfigurasi final masih TBD.

Pemilihan library harus mempertimbangkan:

- Kompatibilitas Laravel.
- Bahasa dan karakter.
- Format tabel.
- Ukuran dokumen.
- Performance.
- Kebutuhan cetak.

---

# 9. Database and Transaction Consistency

## 9.1 Database Ownership

Laravel merupakan satu-satunya komponen yang mengelola database operasional utama.

Python tidak melakukan direct database access.

## 9.2 Transaction Integrity

Operasi yang saling berkaitan harus dikelola menggunakan database transaction.

Contoh proses penyelesaian transaksi:

```text
Begin Database Transaction
        |
        v
Validate Transaction
        |
        v
Save Transaction
        |
        v
Save Transaction Details
        |
        v
Save Payment
        |
        v
Calculate Ingredient Usage
        |
        v
Create Stock Movements
        |
        v
Update Inventory Balance
        |
        v
Commit Transaction
```

Jika salah satu operasi penting gagal, proses harus di-rollback sesuai business rules.

## 9.3 Stock Movement Integrity

Setiap perubahan stok harus memiliki catatan stock movement.

Contoh:

- Sale deduction.
- Purchase receipt.
- Manual adjustment.
- Stock opname adjustment.
- Return.
- Reversal.

Perubahan stok tidak boleh dilakukan tanpa mekanisme pencatatan yang sesuai.

## 9.4 External Service Separation

Operasi eksternal seperti:

- Python API.
- Weather API.
- Printer.
- PDF generation.

Tidak boleh menjadi bagian dari proses database transaction yang menyebabkan transaksi POS harus menunggu service eksternal.

Untuk transaksi yang memerlukan proses lanjutan, gunakan mekanisme asynchronous setelah data operasional berhasil disimpan.

## 9.5 Idempotency

Operasi yang dapat diulang harus memiliki perlindungan terhadap efek samping duplikat.

Contoh:

- Training request.
- Payment processing.
- Stock deduction.
- Purchase receipt.
- Reprint.
- Background job.

Khusus training request, gunakan `training_request_id` sebagai identitas idempotency pada komunikasi Laravel-Python.

Implementasi idempotency untuk masing-masing proses bisnis harus mengikuti business rules dan `DATABASE_DESIGN.md`.

---

# 10. Security Requirements

## 10.1 API Security

Komunikasi Laravel dan Python harus menggunakan authentication.

Ketentuan:

- Secret disimpan pada environment variables.
- Secret tidak ditulis langsung dalam source code.
- Request harus divalidasi.
- Akses Python Service dibatasi.
- Production menggunakan HTTPS atau jaringan internal yang diamankan sesuai deployment architecture.
- Token tidak dicatat pada log.

## 10.2 User Authorization

Setiap tindakan operasional harus mengikuti permission dan role yang ditetapkan aplikasi.

Contoh:

- Mengakses laporan.
- Melakukan stock adjustment.
- Mengelola recipe.
- Mengaktifkan model.
- Melakukan reprint.
- Membuka cash drawer.

## 10.3 Data Protection

Data yang dikirim ke Python hanya boleh mencakup informasi yang diperlukan untuk proses machine learning.

Informasi pengguna atau data sensitif yang tidak relevan tidak boleh dikirimkan.

## 10.4 Input Validation

Semua input dari:

- Browser.
- API.
- Weather provider.
- Python Service.
- Printer integration.

Harus diperiksa sebelum diproses oleh aplikasi.

---

# 11. Logging and Monitoring

## 11.1 Purpose

Logging digunakan untuk membantu:

- Debugging.
- Monitoring.
- Audit.
- Identifikasi kegagalan.
- Pelacakan integrasi.
- Evaluasi reliability sistem.

## 11.2 Integration Logging

Informasi yang perlu dicatat:

| Field | Description |
|---|---|
| request_id | Identitas request |
| endpoint | Endpoint yang digunakan |
| service_name | Nama service tujuan |
| request_timestamp | Waktu request |
| response_timestamp | Waktu response |
| response_status | Status response |
| duration | Durasi proses |
| error_code | Kode error jika ada |
| dataset_version_id | Dataset version jika terkait ANN |
| dataset_checksum | Dataset checksum jika terkait ANN |
| training_request_id | Training request identity |
| training_id | Training process identity |
| model_version | Model version jika terkait ANN |
| feature_schema_version | Feature schema version jika terkait prediction |

## 11.3 Logging Rules

1. Jangan mencatat secret.
2. Jangan mencatat token authentication.
3. Hindari mencatat seluruh payload besar tanpa kebutuhan.
4. Error harus memiliki konteks yang cukup untuk debugging.
5. Request dan response harus dapat dikorelasikan.
6. Data log harus mengikuti kebijakan penyimpanan yang ditentukan.

## 11.4 Monitoring

Monitoring yang direncanakan mencakup:

- Laravel application health.
- Database availability.
- Queue worker status.
- Python Service health.
- ANN training status.
- Prediction status.
- Weather synchronization status.
- Printer integration status.
- Background job failures.

Tools monitoring final masih TBD.

---

# 12. Timeout, Retry, and Failure Policy

## 12.1 Timeout

Setiap integrasi eksternal harus memiliki timeout.

Konfigurasi awal untuk Python API:

| Operation | Initial Timeout |
|---|---:|
| Health Check | 3 seconds |
| Model Status | 5 seconds |
| Prediction | 10 seconds |
| Dataset Validation | 10 seconds |
| Training Request | 10 seconds |

Nilai tersebut merupakan rancangan awal dan harus diuji.

Training execution berjalan asynchronous sehingga durasi training tidak mengikuti HTTP request timeout.

Weather API, printer, dan PDF Generator harus memiliki timeout sesuai karakteristik masing-masing.

## 12.2 Retry

Ketentuan:

1. Retry hanya untuk kegagalan sementara.
2. Retry harus memiliki batas.
3. Validation error tidak perlu diulang tanpa perubahan input.
4. Authentication error tidak boleh diulang tanpa perbaikan konfigurasi.
5. Request yang memiliki efek samping harus memiliki mekanisme idempotency.
6. Retry training harus menggunakan `training_request_id` yang sama.
7. Retry tidak boleh membuat transaksi atau pengurangan stok duplikat.
8. Retry untuk perintah fisik seperti cash drawer harus dilakukan dengan hati-hati.

## 12.3 Failure Isolation

| Failed Component | Expected System Behavior |
|---|---|
| Python ANN Service | POS tetap berjalan |
| Weather Provider | POS tetap berjalan |
| Thermal Printer | Transaksi tetap tersimpan |
| PDF Generator | Data operasional tetap tersimpan |
| Queue Worker | Job dapat diproses kembali sesuai retry policy |
| Database | Operasi yang membutuhkan database tidak dapat dilanjutkan secara aman |

Sistem tidak boleh mengorbankan integritas data demi mempertahankan operasi yang tidak aman.

---

# 13. Integration Testing

## 13.1 General Requirements

Setiap integrasi harus memiliki pengujian sebelum digunakan pada environment operasional.

Testing dilakukan pada level:

- Unit test.
- Integration test.
- Feature test.
- Failure handling test.
- End-to-end test sesuai kebutuhan.

Detail strategi pengujian mengikuti `TESTING_STRATEGY.md`.

## 13.2 Laravel - Python Testing

Test cases:

- Health check berhasil.
- Health check gagal.
- Authentication berhasil.
- Authentication gagal.
- Dataset validation berhasil.
- Dataset validation gagal.
- Dataset checksum sesuai.
- Dataset checksum tidak sesuai.
- Dataset version tidak dikenal.
- Training request berhasil.
- Training request duplikat dengan payload identik.
- Training request duplikat dengan payload berbeda.
- Training status berhasil diambil.
- Training gagal.
- Prediction berhasil.
- Prediction gagal.
- Model version tidak tersedia.
- Feature schema tidak kompatibel.
- Python timeout.
- Python Service unavailable.
- POS tetap berjalan ketika Python gagal.

## 13.3 Dataset Consistency Testing

Pengujian harus memastikan:

1. Snapshot yang divalidasi memiliki dataset version yang benar.
2. Checksum yang dihitung Laravel sesuai dengan hasil verifikasi Python.
3. Snapshot yang dikirim untuk training sama dengan snapshot yang divalidasi.
4. Perubahan record menghasilkan checksum berbeda.
5. Perubahan urutan record ditangani sesuai aturan canonical serialization.
6. Dataset yang belum divalidasi tidak dapat digunakan untuk training.
7. Dataset version baru harus menjalani validasi ulang.

## 13.4 Weather Testing

Test cases:

- Weather API berhasil.
- Weather API gagal.
- Response tidak lengkap.
- Temperature unit tidak sesuai.
- Rainfall unit tidak sesuai.
- Timezone tidak sesuai.
- Actual dan forecast dibedakan.
- Weather record dapat dihubungkan dengan business date.

## 13.5 Printer Testing

Test cases:

- Print berhasil.
- Printer tidak tersedia.
- Printer timeout.
- Print gagal.
- Reprint berhasil.
- Reprint tidak membuat transaksi baru.
- Reprint tidak mengurangi stok kembali.

## 13.6 Cash Drawer Testing

Test cases:

- Authorized user dapat mengirim perintah.
- Unauthorized user ditolak.
- Drawer command berhasil.
- Drawer command gagal.
- Perintah tidak diulang secara otomatis ketika status tidak diketahui.

## 13.7 PDF Testing

Test cases:

- PDF berhasil dibuat.
- PDF berisi data yang benar.
- Filter periode diterapkan.
- Permission diterapkan.
- PDF failure tidak mengubah data operasional.

## 13.8 Transaction Consistency Testing

Test cases:

- Transaksi berhasil disimpan.
- Stock movement berhasil dibuat.
- Kegagalan proses menyebabkan rollback.
- Stock deduction tidak terjadi dua kali.
- Cancellation mengikuti reversal rules.
- External service failure tidak membatalkan transaksi yang sudah berhasil disimpan.

---

# 14. Integration Risk Register

| No. | Risk | Impact | Mitigation |
|---|---|---|---|
| 1 | Python Service unavailable | ANN tidak dapat digunakan | Failure isolation dan retry |
| 2 | Dataset tidak valid | Training gagal | Dataset validation |
| 3 | Dataset snapshot berubah | Training tidak reproducible | Immutable snapshot |
| 4 | Checksum mismatch | Dataset integrity tidak terjamin | Checksum verification |
| 5 | Duplicate training request | Training berjalan ganda | Training request idempotency |
| 6 | Model version mismatch | Prediction tidak konsisten | Explicit model version |
| 7 | Feature schema mismatch | Prediction tidak valid | Feature schema versioning |
| 8 | Data leakage | Evaluasi model tidak valid | Chronological split |
| 9 | Weather API unavailable | Weather feature missing | Missing data policy |
| 10 | Weather data inaccurate | Prediction quality menurun | Data validation |
| 11 | Printer failure | Receipt tidak tercetak | Reprint mechanism |
| 12 | Cash drawer command uncertain | Risiko pembukaan berulang | Manual verification |
| 13 | Queue job duplicated | Efek samping duplikat | Idempotency |
| 14 | Database transaction failure | Data operasional tidak konsisten | Atomic transaction |
| 15 | Model artifact missing | Prediction gagal | Artifact verification and backup |
| 16 | Oversized dataset payload | Timeout atau memory issue | Payload limits and transfer strategy |
| 17 | Incompatible API changes | Integrasi gagal | API versioning and contract tests |

---

# 15. Open Decisions

Hal berikut belum ditetapkan secara final dan harus diputuskan sebelum implementasi terkait.

| No. | Decision | Status |
|---|---|---|
| 1 | Python API framework | TBD |
| 2 | Python Service deployment location | TBD |
| 3 | Final API authentication mechanism | Provisional |
| 4 | Final API request and response schema | Mengacu API Contract; beberapa field masih provisional |
| 5 | Dataset checksum algorithm | TBD |
| 6 | Canonical JSON serialization rules | TBD |
| 7 | Dataset maximum payload size | TBD |
| 8 | Large dataset transfer mechanism | TBD |
| 9 | Training request idempotency storage | TBD |
| 10 | Training status polling interval | TBD |
| 11 | Maximum training duration | TBD |
| 12 | Model activation synchronization | TBD |
| 13 | Feature schema versioning mechanism | TBD |
| 14 | ANN artifact storage location | TBD |
| 15 | ANN artifact backup strategy | TBD |
| 16 | Weather provider final configuration | Provisional |
| 17 | Coffee shop weather coordinates | TBD |
| 18 | Weather fetching schedule | TBD |
| 19 | Weather historical data retrieval policy | TBD |
| 20 | Printer brand and model | TBD |
| 21 | Printer communication interface | TBD |
| 22 | Printer command protocol | TBD |
| 23 | Cash drawer compatibility | TBD |
| 24 | PDF generation library | TBD |
| 25 | Queue driver | TBD |
| 26 | Monitoring tools | TBD |
| 27 | Log retention policy | TBD |
| 28 | Production network architecture | TBD |

Keputusan yang berstatus TBD atau Provisional tidak boleh dianggap sebagai keputusan final oleh AI coding assistant.

---

# 16. Implementation Stages

Integrasi dilaksanakan secara bertahap.

## Stage 1 - Laravel Foundation

Fokus:

- Laravel application setup.
- Database integration.
- Authentication and authorization.
- Core POS.
- Inventory.
- Recipe and ingredient management.
- Transaction integrity.

Output:

- Laravel application dapat menjalankan fungsi utama POS.
- Database operasional tersedia.
- Business rules utama telah diterapkan.

## Stage 2 - Weather Integration

Fokus:

- Weather service abstraction.
- Weather provider adapter.
- Weather API client.
- Weather data validation.
- Weather data storage.
- Weather synchronization.

Output:

- Laravel dapat mengambil dan menyimpan weather records.
- Kegagalan provider tidak mengganggu POS.

## Stage 3 - ANN Service Foundation

Fokus:

- Python ANN Service setup.
- API foundation.
- Authentication.
- Health check.
- Model status endpoint.
- Laravel HTTP client.
- Integration logging.

Output:

- Laravel dapat berkomunikasi dengan Python Service.
- Health check dan model status dapat diuji.

## Stage 4 - Dataset Integration

Fokus:

- Theoretical consumption calculation.
- Business date aggregation.
- Dataset preparation.
- Dataset versioning.
- Immutable dataset snapshot.
- Dataset checksum.
- Dataset validation endpoint.
- Laravel validation metadata storage.

Output:

- Laravel dapat membuat dan menyimpan dataset snapshot.
- Python dapat memvalidasi dataset.
- Identitas dataset dapat ditelusuri.

## Stage 5 - ANN Training Integration

Fokus:

- Training request.
- Training request ID.
- Idempotency.
- Dataset snapshot consistency.
- Asynchronous training.
- Training ID.
- Training status polling.
- Model evaluation.
- Model artifact storage.

Output:

- Laravel dapat mengirim training request.
- Python dapat menjalankan training asynchronous.
- Laravel dapat mengambil hasil training.

## Stage 6 - Prediction Integration

Fokus:

- Prediction feature preparation.
- Model version selection.
- Feature schema versioning.
- Python inference.
- Prediction response validation.
- Prediction storage.

Output:

- Laravel dapat meminta demand prediction.
- Prediction result tersimpan dengan metadata yang benar.

## Stage 7 - Inventory Forecast Integration

Fokus:

- Predicted demand.
- Inventory availability.
- Safety stock.
- Reorder point.
- Replenishment recommendation.

Output:

- Laravel dapat menampilkan rekomendasi kebutuhan stok berdasarkan prediction dan kondisi inventory.

## Stage 8 - Printer and Document Integration

Fokus:

- Thermal printer.
- Receipt generation.
- Reprint.
- Cash drawer.
- PDF report.

Output:

- Receipt dan dokumen dapat dihasilkan.
- Kegagalan output tidak mengubah data transaksi.

## Stage 9 - Integration Testing

Fokus:

- API contract testing.
- Dataset consistency testing.
- Failure handling.
- Transaction consistency.
- End-to-end testing.
- Performance testing sesuai kebutuhan.

Output:

- Integrasi dapat diuji berdasarkan acceptance criteria.
- Kegagalan komponen eksternal dapat ditangani secara aman.

## Stage 10 - Deployment

Fokus:

- Production configuration.
- Service communication.
- Queue worker.
- Scheduler.
- Database backup.
- ANN artifact backup.
- Logging and monitoring.
- Recovery procedure.

Output:

- Sistem dapat dijalankan pada environment deployment yang telah ditetapkan.

Urutan dan pembagian tahap implementasi harus tetap diselaraskan dengan `IMPLEMENTATION_PLAN.md`.

---

# 17. Integration Success Criteria

Integrasi dianggap memenuhi kebutuhan apabila:

## 17.1 Laravel - Python

- Laravel dapat berkomunikasi dengan Python melalui API.
- Authentication berjalan sesuai konfigurasi.
- Dataset dapat divalidasi.
- Dataset version dan checksum dapat ditelusuri.
- Training menggunakan dataset snapshot yang telah divalidasi.
- Training request tidak menghasilkan proses duplikat saat retry.
- Training status dapat diperiksa.
- Model version dapat ditelusuri.
- Feature schema version dapat diverifikasi.
- Prediction dapat dijalankan.
- Prediction result dapat disimpan.
- Python failure tidak menghentikan POS.

## 17.2 Weather Integration

- Weather data dapat diambil.
- Weather data dapat divalidasi.
- Weather data dapat disimpan.
- Actual dan forecast dibedakan.
- Weather data dapat dikaitkan dengan business date.
- Weather failure tidak menghentikan POS.

## 17.3 Printer Integration

- Receipt dapat dicetak.
- Print status dapat dicatat.
- Reprint tidak membuat transaksi baru.
- Printer failure tidak membatalkan transaksi.

## 17.4 Cash Drawer Integration

- Perintah hanya dapat dilakukan oleh pengguna berwenang.
- Aktivitas dapat dicatat.
- Kegagalan tidak menyebabkan perintah berulang secara otomatis.

## 17.5 PDF Integration

- PDF dapat dibuat dari data Laravel.
- Data PDF sesuai dengan filter dan permission.
- Kegagalan PDF tidak mengubah data operasional.

## 17.6 Data Integrity

- Transaksi dan stock movement konsisten.
- Perubahan stok dapat ditelusuri.
- External service tidak merusak transaction integrity.
- Background job tidak menghasilkan efek samping duplikat.

---

# 18. Related Documents

| Document | Relationship |
|---|---|
| `PROJECT_MASTER.md` | Master project decisions and architecture |
| `PROJECT_SPEC.md` | Business rules and functional requirements |
| `DATABASE_DESIGN.md` | Database structure and data ownership |
| `ANN_SPECIFICATION.md` | ANN methodology, features, training, evaluation, and prediction |
| `API_CONTRACT.md` | Detailed Laravel-Python API request and response |
| `DEPLOYMENT_GUIDE.md` | Deployment architecture and configuration |
| `TESTING_STRATEGY.md` | Testing scope and acceptance approach |
| `IMPLEMENTATION_PLAN.md` | Implementation phases and dependencies |
| `AI_RULES.md` | Rules for AI-assisted development |
| `PROGRESS.md` | Current implementation and documentation status |

Jika terjadi perbedaan:

- Business rules mengikuti `PROJECT_SPEC.md`.
- Arsitektur utama mengikuti `PROJECT_MASTER.md`.
- Database design mengikuti `DATABASE_DESIGN.md`.
- ANN methodology mengikuti `ANN_SPECIFICATION.md`.
- API request dan response mengikuti `API_CONTRACT.md`.
- Deployment mengikuti `DEPLOYMENT_GUIDE.md`.
- Testing strategy mengikuti `TESTING_STRATEGY.md`.

Perubahan pada satu dokumen yang memengaruhi dokumen lain harus disinkronkan.

---

# 19. Document Governance

Dokumen ini merupakan spesifikasi integrasi teknis untuk Seumpama POS.

Ketentuan:

1. Semua perubahan integrasi harus dievaluasi terhadap arsitektur utama.
2. Perubahan kontrak API harus memperbarui `API_CONTRACT.md`.
3. Perubahan ANN harus memperbarui `ANN_SPECIFICATION.md`.
4. Perubahan struktur data harus dievaluasi terhadap `DATABASE_DESIGN.md`.
5. Perubahan business rules harus mengikuti `PROJECT_SPEC.md`.
6. Perubahan deployment harus disinkronkan dengan `DEPLOYMENT_GUIDE.md`.
7. Perubahan testing harus disinkronkan dengan `TESTING_STRATEGY.md`.
8. AI coding assistant tidak boleh mengubah arsitektur atau kontrak integrasi secara sepihak.
9. Keputusan yang belum disepakati harus tetap ditandai TBD atau Provisional.
10. Implementasi harus mengikuti versi dokumentasi yang telah disepakati.

---

# 20. Final Notes

Integrasi Seumpama POS dirancang dengan Laravel sebagai pusat pengelolaan operasional dan Python sebagai service khusus machine learning.

Prinsip utama:

- Laravel owns operational data.
- Python owns machine learning execution.
- Communication uses REST API.
- Dataset preparation follows Laravel business rules.
- Dataset validation and training use the same immutable snapshot.
- Dataset integrity is verified through version and checksum.
- Training requests support idempotency.
- Prediction uses an explicit model version and feature schema version.
- Weather data is managed through a replaceable provider adapter.
- Printer and PDF failures do not invalidate saved transactions.
- External service failures must not interrupt core POS operations.
- Model activation requires evaluation and approval.

**Document Status: DRAFT — TECHNICAL PLANNING**

Dokumen ini belum menjadi bukti bahwa integrasi telah diimplementasikan atau diuji.
