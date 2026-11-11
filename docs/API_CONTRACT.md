
# API_CONTRACT.md

# SEUMPAMA POS
## Laravel - Python ANN Service API Contract

| Information | Description |
|---|---|
| Project | Seumpama POS |
| Document | API Contract |
| API Version | v1 |
| Integration Type | REST API |
| Communication Format | JSON |
| Main Application | Laravel |
| Machine Learning Service | Python |
| Status | Draft - Technical Planning |
| Timezone | Asia/Jakarta |

---

# 1. Purpose

Dokumen ini mendefinisikan kontrak komunikasi antara aplikasi utama Seumpama POS berbasis Laravel dengan Python ANN Service.

API digunakan untuk mendukung proses:

- Dataset validation.
- ANN model training.
- Model evaluation.
- Demand prediction.
- Model status retrieval.
- Service health monitoring.

Dokumen ini menjadi acuan implementasi API antara Laravel dan Python.

Implementasi harus mengikuti:

- `PROJECT_MASTER.md`
- `PROJECT_SPEC.md`
- `ANN_SPECIFICATION.md`
- `INTEGRATION_SPECIFICATION.md`
- `DATABASE_DESIGN.md`

Dokumen ini tidak boleh mengubah business rules atau arsitektur utama yang telah ditetapkan dalam dokumentasi proyek.

---

# 2. Integration Architecture

## 2.1 System Responsibilities

### Laravel Responsibilities

Laravel merupakan aplikasi utama dan pemilik seluruh data operasional Seumpama POS.

Laravel bertanggung jawab atas:

1. Mengambil data transaksi dan operasional dari database utama.
2. Mengelola data produk, bahan baku, recipe, inventory, dan business day.
3. Menghitung theoretical ingredient consumption berdasarkan transaksi dan recipe yang berlaku.
4. Melakukan validasi business rules terhadap data operasional.
5. Menentukan data yang layak digunakan untuk dataset ANN.
6. Menyusun dan mengirimkan dataset atau prediction payload kepada Python ANN Service.
7. Mengelola request training dan prediction.
8. Menerima dan memvalidasi response dari Python.
9. Menyimpan metadata training dan hasil prediksi ke database Laravel.
10. Menyediakan hasil prediksi kepada modul forecasting dan inventory recommendation.

Laravel tetap bertanggung jawab atas seluruh keputusan bisnis dan perubahan data operasional.

### Python ANN Service Responsibilities

Python ANN Service bertanggung jawab atas proses machine learning.

Python bertanggung jawab atas:

1. Validasi teknis struktur dan format dataset yang diterima.
2. Pemeriksaan kelengkapan dan konsistensi input untuk kebutuhan machine learning.
3. Data preprocessing.
4. Feature engineering sesuai ANN specification.
5. Encoding dan normalization.
6. Training ANN model.
7. Model evaluation.
8. Model versioning dan pengelolaan model artifacts.
9. Demand prediction.
10. Mengembalikan hasil training, evaluation, dan prediction kepada Laravel melalui API.

Python tidak bertanggung jawab atas:

- Mengelola transaksi POS.
- Mengubah data produk atau bahan baku.
- Mengubah recipe atau BOM.
- Mengurangi atau menambah stok aktual.
- Membuat atau membatalkan transaksi.
- Melakukan pembelian bahan baku.
- Menentukan keputusan pembelian otomatis.
- Mengubah database operasional Laravel secara langsung.

## 2.2 Data Ownership

| Data | Owner |
|---|---|
| Users dan permissions | Laravel |
| Products dan categories | Laravel |
| Ingredients dan recipes | Laravel |
| Transactions dan payments | Laravel |
| Inventory dan stock movements | Laravel |
| Operational business day | Laravel |
| Weather observations | Laravel |
| Dataset business records | Laravel |
| Dataset preprocessing artifacts | Python |
| ANN training configuration | Python, dengan metadata yang dicatat Laravel |
| ANN model artifacts | Python |
| Training evaluation results | Python, dengan metadata yang dicatat Laravel |
| Prediction results | Laravel |
| Stock forecasts | Laravel |
| Replenishment recommendations | Laravel |

Laravel menjadi sumber data operasional utama.

Python hanya mengelola data dan artifacts yang diperlukan untuk proses machine learning.

## 2.3 Integration Principles

1. Laravel merupakan pemilik utama data operasional.
2. Python tidak mengakses database Laravel secara langsung.
3. Komunikasi dilakukan melalui REST API.
4. Seluruh request dan response menggunakan JSON.
5. Python hanya melakukan pemrosesan machine learning.
6. Laravel tetap mengendalikan business rules.
7. Kegagalan Python tidak boleh menghentikan operasional POS.
8. Training dan prediction tidak boleh menghambat transaksi POS.
9. Hasil prediction harus memiliki model version dan timestamp.
10. Model baru tidak otomatis menjadi active model.

---

# 3. API General Specification

## 3.1 Protocol

Komunikasi menggunakan HTTP atau HTTPS.

Production environment wajib menggunakan HTTPS.

## 3.2 Base Path

Seluruh endpoint menggunakan prefix:

`/api/v1`

Contoh:

`http://127.0.0.1:8001/api/v1/health`

Base URL harus dikonfigurasi melalui environment variable Laravel.

## 3.3 HTTP Methods

| Method | Usage |
|---|---|
| GET | Mengambil informasi atau status |
| POST | Mengirim request untuk menjalankan proses |

## 3.4 Request Format

Format request:

`Content-Type: application/json`

`Accept: application/json`

Encoding:

`UTF-8`

## 3.5 Response Format

Seluruh response menggunakan JSON.

Successful response:

```json
{
  "success": true,
  "message": "Request processed successfully",
  "data": {},
  "meta": {
    "request_id": "example-request-id"
  }
}
```

Error response:

```json
{
  "success": false,
  "message": "Request could not be processed",
  "error": {
    "code": "VALIDATION_ERROR",
    "details": []
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

Field `request_id` digunakan untuk membantu pelacakan request pada log Laravel dan Python.

---

# 4. Authentication and Security

## 4.1 Authentication

Komunikasi Laravel dengan Python menggunakan internal API authentication.

Mekanisme yang dikunci: Bearer Token statis (via env var ANN_SERVICE_TOKEN).

Contoh:

```http
Authorization: Bearer <ANN_SERVICE_TOKEN>
```

Mekanisme authentication masih berstatus provisional sampai konfigurasi keamanan integrasi ditetapkan.

## 4.2 Security Requirements

1. API Python tidak boleh diekspos secara publik tanpa kebutuhan dan pengamanan yang sesuai.
2. Token harus disimpan dalam environment variables.
3. Token tidak boleh ditulis langsung di source code.
4. Token tidak boleh muncul dalam application logs.
5. Request harus divalidasi sebelum diproses.
6. Response harus diperiksa sebelum disimpan Laravel.
7. Endpoint training dan prediction harus memiliki authentication.
8. Batasi akses jaringan Python Service sesuai deployment architecture.
9. Hindari mengirimkan informasi sensitif yang tidak diperlukan untuk ANN.

## 4.3 Environment Variables

Konfigurasi Laravel:

```env
ANN_SERVICE_URL=http://127.0.0.1:8001
ANN_SERVICE_TOKEN=your-secret-token
ANN_SERVICE_TIMEOUT=10
ANN_SERVICE_CONNECT_TIMEOUT=3
```

Nilai tersebut merupakan contoh konfigurasi development, bukan production credentials.

---

# 5. API Endpoint Specification

Endpoint berikut merupakan rancangan awal API v1.

Nama endpoint, struktur payload, dan parameter yang berstatus provisional harus difinalisasi sebelum implementasi integrasi.

## 5.1 Health Check

### Endpoint

`GET /api/v1/health`

### Purpose

Memeriksa apakah Python ANN Service tersedia dan dapat menerima request.

### Request

Tidak membutuhkan request body.

### Successful Response

HTTP `200 OK`

```json
{
  "success": true,
  "message": "ANN Service is healthy",
  "data": {
    "service": "seumpama-ann-service",
    "status": "healthy",
    "version": "1.0.0"
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

### Notes

Health check hanya menunjukkan kondisi service.

Health check tidak menjamin model sudah tersedia atau siap melakukan prediction.

---

## 5.2 Model Status

### Endpoint

`GET /api/v1/model/status`

### Purpose

Mengambil informasi model ANN yang tersedia pada Python Service.

### Request

Tidak membutuhkan request body.

### Successful Response

HTTP `200 OK`

```json
{
  "success": true,
  "message": "Model status retrieved",
  "data": {
    "model_available": true,
    "active_model": {
      "model_version": "ann-001",
      "status": "active",
      "target_type": "daily_ingredient_demand"
    },
    "available_models": 1
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

### Notes

- Model status berasal dari Python Service.
- Laravel menyimpan metadata model yang diperlukan untuk kebutuhan aplikasi.
- Model yang berstatus candidate tidak boleh otomatis dianggap active.
- Format metadata final mengikuti model versioning pada `ANN_SPECIFICATION.md`.

---

## 5.3 Dataset Validation

### Endpoint

`POST /api/v1/datasets/validate`

### Purpose

Memvalidasi struktur dan kelengkapan dataset sebelum proses training.

Laravel bertanggung jawab atas validasi business rules dan penyusunan dataset.

Python bertanggung jawab atas validasi teknis dan kesiapan dataset untuk machine learning.

### Request Body

```json
{
  "dataset_version": "dataset-v001",
  "period": {
    "start_date": "2026-01-01",
    "end_date": "2026-06-30"
  },
  "records": [
    {
      "business_date": "2026-06-01",
      "ingredient_id": 1,
      "ingredient_code": "ING-001",
      "consumption": 12.5,
      "unit": "gram",
      "features": {
        "temperature_avg": 29.5,
        "rainfall_total": 5.2,
        "day_of_week": 1,
        "is_weekend": false
      },
      "data_status": "complete"
    }
  ]
}
```

### Successful Response

HTTP `200 OK`

```json
{
  "success": true,
  "message": "Dataset validation completed",
  "data": {
    "dataset_version": "dataset-v001",
    "is_valid": true,
    "total_records": 1,
    "valid_records": 1,
    "invalid_records": 0,
    "warnings": [],
    "errors": []
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

### Validation Rules

Python harus memeriksa:

1. Dataset version tersedia.
2. Struktur records sesuai schema.
3. Field wajib tersedia.
4. Tipe data sesuai.
5. Tanggal menggunakan format yang benar.
6. Nilai numerik dapat diproses.
7. Ingredient identifier tersedia.
8. Unit konsisten dengan metadata dataset.
9. Data tidak memiliki format yang tidak valid.
10. Kecukupan observasi untuk kebutuhan preprocessing dan training.

### Important Notes

- Missing data tidak boleh otomatis dianggap sebagai zero.
- Zero consumption harus dibedakan dari missing consumption.
- Data simulasi harus memiliki identifikasi yang jelas.
- Dataset validation tidak berarti dataset pasti layak menghasilkan ANN yang akurat.
- Kriteria kelayakan dataset lebih lanjut mengikuti `ANN_SPECIFICATION.md`.

---

## 5.4 Demand Prediction

### Endpoint

`POST /api/v1/predictions/demand`

### Purpose

Meminta Python ANN Service memprediksi kebutuhan harian bahan baku inti.

Prediction dilakukan berdasarkan data historis dan feature yang tersedia pada saat prediction dibuat.

### Request Body

Contoh struktur awal:

```json
{
  "prediction_date": "2026-07-01",
  "business_timezone": "Asia/Jakarta",
  "ingredients": [
    {
      "ingredient_id": 1,
      "ingredient_code": "ING-001",
      "unit": "gram",
      "historical_usage": [
        {
          "business_date": "2026-06-28",
          "quantity": 10.5
        },
        {
          "business_date": "2026-06-29",
          "quantity": 12.0
        },
        {
          "business_date": "2026-06-30",
          "quantity": 11.5
        }
      ],
      "features": {
        "temperature_avg": 29.0,
        "rainfall_total": 3.5,
        "day_of_week": 2,
        "is_weekend": false
      }
    }
  ]
}
```

### Successful Response

HTTP `200 OK`

```json
{
  "success": true,
  "message": "Demand prediction completed",
  "data": {
    "prediction_date": "2026-07-01",
    "model_version": "ann-001",
    "predictions": [
      {
        "ingredient_id": 1,
        "ingredient_code": "ING-001",
        "predicted_quantity": 12.75,
        "unit": "gram",
        "prediction_status": "valid"
      }
    ],
    "generated_at": "2026-07-01T00:15:00+07:00"
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

### Prediction Rules

1. Prediction dilakukan untuk operational business day yang ditentukan.
2. Feature harus mengikuti `ANN_SPECIFICATION.md`.
3. Preprocessing harus konsisten dengan preprocessing ketika model dilatih.
4. Model version harus dicatat.
5. Prediction tidak boleh menggunakan data masa depan yang belum tersedia pada waktu prediction dibuat.
6. Weather forecast dan actual weather harus dibedakan.
7. Missing feature harus ditangani sesuai preprocessing policy.
8. Prediksi negatif harus ditangani sesuai aturan output ANN yang ditetapkan.
9. Prediction yang gagal tidak boleh disimpan sebagai hasil valid.
10. Hasil prediction harus dapat ditelusuri berdasarkan model version dan waktu proses.

### Important Notes

Struktur request di atas merupakan contoh awal.

Daftar feature, jumlah historical observations, dan schema final harus mengikuti `ANN_SPECIFICATION.md`.

Jangan mengasumsikan bahwa tiga observasi historis sudah cukup untuk seluruh model.

---

## 5.5 ANN Model Training

### Endpoint

`POST /api/v1/model/train`

### Purpose

Memulai proses training ANN menggunakan dataset yang telah disiapkan Laravel.

Training dilakukan secara asynchronous agar proses tersebut tidak menghambat operasional aplikasi.

### Request Body

```json
{
  "training_id": "training-2026-001",
  "dataset_version": "dataset-v001",
  "target_ingredients": [
    {
      "ingredient_id": 1,
      "ingredient_code": "ING-001"
    }
  ],
  "training_config": {
    "architecture": "MLP",
    "epochs": 100,
    "batch_size": 16,
    "learning_rate": 0.001,
    "optimizer": "adam",
    "loss": "mse"
  },
  "dataset": {
    "records": []
  }
}
```

### Successful Response

HTTP `202 Accepted`

```json
{
  "success": true,
  "message": "Training request accepted",
  "data": {
    "training_id": "training-2026-001",
    "status": "queued"
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

### Training Rules

1. Training harus dilakukan secara asynchronous.
2. Python harus memvalidasi dataset sebelum training.
3. Dataset harus menggunakan chronological split.
4. Preprocessing tidak boleh menyebabkan data leakage.
5. Scaler harus di-fit hanya menggunakan training data.
6. Model harus dievaluasi menggunakan validation dan test data sesuai strategi yang ditetapkan.
7. Evaluasi harus mencakup MAE dan RMSE.
8. Baseline comparison harus dilakukan.
9. Model artifacts harus disimpan berdasarkan model version.
10. Training result harus dapat ditelusuri berdasarkan training ID dan dataset version.
11. Training baru tidak boleh otomatis menggantikan active model.

### Important Notes

Parameter training di atas merupakan konfigurasi awal untuk perencanaan.

Arsitektur dan hyperparameter final mengikuti eksperimen serta keputusan dalam `ANN_SPECIFICATION.md`.

Dataset payload yang ditampilkan merupakan contoh struktur awal, bukan schema final.

---

## 5.6 Training Status

### Endpoint

`GET /api/v1/model/train/{training_id}`

### Purpose

Mengambil status dan hasil proses training berdasarkan training ID.

### Example Request

`GET /api/v1/model/train/training-2026-001`

### Successful Response — Running

HTTP `200 OK`

```json
{
  "success": true,
  "message": "Training status retrieved",
  "data": {
    "training_id": "training-2026-001",
    "status": "running",
    "progress": null,
    "model_version": null,
    "evaluation": null
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

### Successful Response — Completed

HTTP `200 OK`

```json
{
  "success": true,
  "message": "Training completed",
  "data": {
    "training_id": "training-2026-001",
    "status": "completed",
    "model_version": "ann-002",
    "evaluation": {
      "mae": 2.15,
      "rmse": 3.10
    },
    "baseline_comparison": {
      "baseline_mae": 3.25,
      "baseline_rmse": 4.20
    }
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

### Training Status Values

| Status | Description |
|---|---|
| queued | Training menunggu proses |
| running | Training sedang berjalan |
| completed | Training berhasil diselesaikan |
| failed | Training gagal |

### Notes

- `progress` bersifat opsional.
- Evaluation metrics harus menggunakan hasil evaluasi aktual.
- Model berstatus completed belum otomatis menjadi active.
- Laravel harus menyimpan metadata training yang relevan.
- Python harus mempertahankan informasi training agar dapat ditelusuri.

---

# 6. Dataset Processing Boundary

Proses dataset dibagi menjadi dua tanggung jawab.

## 6.1 Laravel — Business Data Preparation

Laravel bertanggung jawab untuk:

1. Mengambil data transaksi yang relevan.
2. Menghubungkan transaksi dengan recipe dan ingredient usage.
3. Menghitung theoretical consumption.
4. Mengelompokkan data berdasarkan operational business day.
5. Memeriksa validitas data berdasarkan business rules.
6. Menentukan target ingredient.
7. Menyusun dataset payload sesuai API contract.
8. Menyertakan informasi dataset version dan periode data.

Laravel tidak bertanggung jawab melakukan preprocessing khusus untuk kebutuhan internal ANN seperti fitting scaler atau transformasi model input.

## 6.2 Python — Machine Learning Data Processing

Python bertanggung jawab untuk:

1. Memvalidasi struktur payload.
2. Memeriksa format dan tipe data.
3. Memeriksa kecukupan observasi untuk proses machine learning.
4. Menangani missing values sesuai kebijakan preprocessing.
5. Melakukan feature engineering.
6. Melakukan encoding dan normalization.
7. Memastikan preprocessing konsisten antara training dan inference.
8. Menyimpan konfigurasi preprocessing yang diperlukan untuk reproduksi model.

Preprocessing harus mengikuti `ANN_SPECIFICATION.md`.

Python tidak boleh mengubah nilai data operasional asli yang dikirim oleh Laravel.

---

# 7. Data Validation

Validasi dilakukan pada dua sisi.

## 7.1 Laravel Validation

Laravel harus memvalidasi:

- Business rules.
- Transaction status.
- Ingredient identifier.
- Recipe validity.
- Business date.
- Dataset version.
- Data completeness.
- Data availability.
- Target ingredient configuration.
- Prediction date.

## 7.2 Python Validation

Python harus memvalidasi:

- Request structure.
- Required fields.
- Data types.
- Numeric values.
- Feature schema.
- Dataset format.
- Missing values.
- Model compatibility.
- Preprocessing compatibility.
- Training data sufficiency.

## 7.3 Missing Data Policy

Missing data tidak boleh otomatis diganti dengan nilai nol.

Perbedaan berikut harus dipertahankan:

| Condition | Meaning |
|---|---|
| Zero consumption | Tidak terdapat konsumsi pada periode tersebut |
| Missing consumption | Data konsumsi tidak tersedia |
| Closed business day | Operasional tidak berlangsung |
| Missing weather | Data cuaca tidak tersedia |
| Invalid record | Data tidak memenuhi aturan validasi |
| Simulated record | Data hasil simulasi untuk testing |

Penanganan lebih lanjut mengikuti `ANN_SPECIFICATION.md`.

---

# 8. Error Handling

## 8.1 HTTP Status Codes

| HTTP Status | Meaning |
|---|---|
| 200 | Request berhasil |
| 202 | Request asynchronous diterima |
| 400 | Bad request |
| 401 | Unauthorized |
| 404 | Resource tidak ditemukan |
| 409 | Conflict |
| 422 | Validation error |
| 429 | Too many requests |
| 500 | Internal server error |
| 503 | Service unavailable |
| 504 | Gateway timeout |

## 8.2 Error Codes

| Error Code | Description |
|---|---|
| VALIDATION_ERROR | Request tidak valid |
| AUTHENTICATION_ERROR | Authentication gagal |
| DATASET_INVALID | Dataset tidak valid |
| DATA_INSUFFICIENT | Data tidak mencukupi |
| FEATURE_SCHEMA_MISMATCH | Feature schema tidak sesuai |
| MODEL_NOT_AVAILABLE | Model tidak tersedia |
| MODEL_VERSION_MISMATCH | Model version tidak sesuai |
| PREDICTION_FAILED | Prediction gagal |
| TRAINING_FAILED | Training gagal |
| RESOURCE_NOT_FOUND | Resource tidak ditemukan |
| SERVICE_UNAVAILABLE | Service tidak tersedia |
| INTERNAL_ERROR | Internal service error |

## 8.3 Error Response Example

```json
{
  "success": false,
  "message": "Dataset validation failed",
  "error": {
    "code": "DATASET_INVALID",
    "details": [
      {
        "field": "records",
        "message": "Dataset records are invalid"
      }
    ]
  },
  "meta": {
    "request_id": "example-request-id"
  }
}
```

Error detail dapat berbeda sesuai endpoint dan jenis kegagalan.

---

# 9. Timeout and Retry Policy

## 9.1 Initial Timeout Configuration

Konfigurasi awal:

| Operation | Timeout |
|---|---:|
| Health check | 3 seconds |
| Model status | 5 seconds |
| Prediction | 10 seconds |
| Dataset validation | 10 seconds |
| Training request | 10 seconds |

Nilai tersebut merupakan konfigurasi awal dan dapat disesuaikan berdasarkan hasil pengujian.

Training execution tidak menggunakan timeout request yang sama dengan proses training keseluruhan karena training berjalan secara asynchronous.

## 9.2 Retry Policy

Ketentuan:

1. Retry hanya dilakukan untuk kegagalan sementara yang dapat dipulihkan.
2. Gunakan bounded retry.
3. Jangan melakukan retry tanpa batas.
4. Hindari duplicate training request.
5. Training request harus memiliki mekanisme identifikasi unik.
6. Prediction retry harus mempertimbangkan kemungkinan request sebelumnya sudah diproses.
7. Validation error tidak perlu di-retry.
8. Authentication error tidak perlu di-retry sebelum konfigurasi diperbaiki.

Mekanisme idempotency dan retry final perlu ditetapkan pada tahap implementasi integrasi.

## 9.3 Failure Fallback

Jika Python Service tidak tersedia:

- POS tetap berjalan.
- Inventory tetap berjalan.
- Transaksi tetap dapat diproses.
- Prediction dapat ditunda.
- Training dapat ditunda.
- Laravel mencatat kegagalan integrasi.
- Sistem dapat menampilkan prediksi valid terakhir beserta timestamp atau menampilkan status unavailable.

Jangan menggunakan prediksi lama tanpa menunjukkan waktu pembuatannya.

---

# 10. Model Versioning and Evaluation

Setiap model harus memiliki identitas dan metadata yang dapat ditelusuri.

Metadata minimum yang direncanakan:

- Model version.
- Training ID.
- Dataset version.
- Target ingredient.
- Model architecture.
- Hyperparameters.
- Preprocessing configuration.
- Training timestamp.
- Evaluation metrics.
- Model status.
- Model artifact reference.

## 10.1 Model Status

Status model:

| Status | Description |
|---|---|
| candidate | Model baru yang menunggu evaluasi atau persetujuan |
| active | Model yang digunakan untuk prediction |
| archived | Model yang tidak lagi digunakan |

## 10.2 Model Activation

Ketentuan:

1. Model baru harus melewati evaluasi.
2. Model harus dibandingkan dengan baseline.
3. Model harus memenuhi kriteria penerimaan yang telah ditetapkan.
4. Aktivasi model membutuhkan persetujuan sesuai mekanisme sistem.
5. Model lama harus tetap dapat ditelusuri.
6. Aktivasi model tidak boleh menghapus riwayat model sebelumnya.

Model versioning dan evaluation detail mengikuti `ANN_SPECIFICATION.md`.

---

# 11. Prediction and Forecasting Integration

Python hanya menghasilkan estimasi demand bahan baku.

Laravel bertanggung jawab menggabungkan hasil prediction dengan kondisi inventory aktual.

Alur:

1. Laravel menerima hasil prediction.
2. Laravel memvalidasi prediction result.
3. Laravel menyimpan prediction result.
4. Laravel mengambil stok aktual bahan baku.
5. Laravel mengambil safety stock dan supplier lead time.
6. Laravel menghitung kebutuhan replenishment.
7. Laravel menghasilkan stock recommendation.
8. Pengguna melakukan review dan mengambil keputusan.

Rumus awal:

`Safety Stock = Z × σd × √L`

`Reorder Point = Average Daily Demand × L + Safety Stock`

Keterangan:

- Z = service level factor.
- σd = standard deviation of daily demand.
- L = lead time.

Detail formula dan business rules mengikuti `PROJECT_SPEC.md` dan `ANN_SPECIFICATION.md`.

Prediction tidak boleh:

- Mengubah stok aktual.
- Membuat purchase order otomatis.
- Mengurangi inventory.
- Menggantikan stock opname.
- Menjadi keputusan pembelian tanpa persetujuan pengguna.

---

# 12. Logging and Monitoring

Laravel dan Python harus memiliki logging untuk membantu debugging integrasi.

Informasi yang perlu dicatat:

- Request ID.
- Endpoint.
- Timestamp.
- Request status.
- Response status.
- Processing duration.
- Error code.
- Training ID jika berkaitan.
- Dataset version jika berkaitan.
- Model version jika berkaitan.

Jangan mencatat:

- API token.
- Secret credentials.
- Sensitive information yang tidak diperlukan.
- Seluruh payload besar jika tidak dibutuhkan untuk debugging.

Logging harus memungkinkan penelusuran request dari Laravel ke Python.

---

# 13. Integration Workflow

## 13.1 Prediction Workflow

```text
Laravel
   |
   | Retrieve operational data
   v
Business Data Validation
   |
   | Prepare prediction payload
   v
Python ANN Service
   |
   | Validate request
   v
Preprocessing
   |
   | Load active model
   v
ANN Inference
   |
   | Return prediction
   v
Laravel
   |
   | Validate response
   v
Store prediction result
   |
   | Combine with actual inventory
   v
Stock Forecast
   |
   v
Replenishment Recommendation
```

## 13.2 Training Workflow

```text
Laravel
   |
   | Retrieve historical operational data
   v
Business Data Preparation
   |
   | Build dataset version
   v
Python ANN Service
   |
   | Validate dataset
   v
Preprocessing
   |
   | Chronological dataset split
   v
ANN Training
   |
   | Model evaluation
   v
Model Artifacts and Evaluation Results
   |
   | Return training metadata
   v
Laravel
   |
   | Store training metadata
   v
Model Review and Approval
   |
   v
Model Activation
```

---

# 14. API Compatibility and Versioning

API menggunakan version prefix.

Contoh:

`/api/v1`

Ketentuan:

1. Perubahan yang tidak kompatibel harus menggunakan API version baru.
2. Perubahan request schema harus dikoordinasikan.
3. Perubahan response schema harus dikoordinasikan.
4. Feature schema harus memiliki versi atau identitas konfigurasi yang dapat ditelusuri.
5. Laravel dan Python harus menggunakan kontrak yang kompatibel.
6. API lama tidak boleh diubah secara diam-diam jika masih digunakan.

Perubahan API harus disertai pembaruan dokumentasi dan integration tests.

---

# 15. Testing Requirements

Implementasi API harus diuji sebelum digunakan pada environment operasional.

Testing minimum:

## 15.1 Health Check Testing

- Service tersedia.
- Service tidak tersedia.
- Response format sesuai.

## 15.2 Authentication Testing

- Token valid.
- Token tidak valid.
- Token tidak tersedia.

## 15.3 Dataset Validation Testing

- Dataset valid.
- Dataset kosong.
- Field wajib hilang.
- Tipe data salah.
- Missing consumption.
- Zero consumption.
- Invalid business date.
- Insufficient records.

## 15.4 Prediction Testing

- Model tersedia.
- Model tidak tersedia.
- Feature schema tidak sesuai.
- Missing feature.
- Invalid input.
- Prediction berhasil.
- Prediction gagal.
- Model version tidak sesuai.

## 15.5 Training Testing

- Training request valid.
- Training request tidak valid.
- Dataset tidak mencukupi.
- Training berhasil.
- Training gagal.
- Duplicate training request.
- Training status retrieval.

## 15.6 Integration Testing

- Laravel berhasil mengirim request.
- Laravel berhasil menerima response.
- Response gagal divalidasi.
- Python mengalami timeout.
- Python tidak tersedia.
- POS tetap berjalan ketika Python gagal.
- Prediction result tersimpan dengan metadata yang benar.

Detail strategi testing mengikuti `TESTING_STRATEGY.md`.

---

# 16. Environment Configuration

## 16.1 Laravel Environment

Contoh:

```env
ANN_SERVICE_URL=http://127.0.0.1:8001
ANN_SERVICE_TOKEN=your-secret-token
ANN_SERVICE_TIMEOUT=10
ANN_SERVICE_CONNECT_TIMEOUT=3
```

## 16.2 Python Environment

Contoh konfigurasi:

```env
APP_ENV=development
API_PREFIX=/api/v1
API_TOKEN=your-secret-token
MODEL_STORAGE_PATH=storage/models
LOG_LEVEL=INFO
```

Nama environment variable merupakan rancangan awal dan dapat disesuaikan dengan implementasi final.

Secret tidak boleh disimpan dalam repository.

---

# 17. Open Decisions

Hal-hal berikut belum ditetapkan secara final:

| No. | Decision | Status |
|---|---|---|
| 1 | Python API framework | TBD |
| 2 | Final endpoint naming | Provisional |
| 3 | Final authentication mechanism | Provisional |
| 4 | Final feature schema | TBD |
| 5 | Historical observation window | TBD |
| 6 | Dataset payload structure | Provisional |
| 7 | Dataset transfer strategy | Provisional |
| 8 | Final preprocessing configuration | TBD |
| 9 | Final ANN architecture | TBD |
| 10 | Final training configuration | TBD |
| 11 | Model artifact storage implementation | TBD |
| 12 | Model activation workflow | TBD |
| 13 | Idempotency mechanism | TBD |
| 14 | Final timeout and retry configuration | Provisional |
| 15 | Prediction scheduling | TBD |
| 16 | Weather feature availability policy | TBD |
| 17 | Final feature schema versioning | TBD |

Keputusan tersebut harus diselesaikan berdasarkan hasil perancangan dan kebutuhan implementasi.

AI coding assistant tidak boleh menganggap keputusan TBD atau Provisional sebagai keputusan final.

---

# 18. Completion Criteria

API Contract dianggap siap untuk implementasi apabila:

- [ ] Pembagian tanggung jawab Laravel dan Python telah disepakati.
- [ ] API endpoint telah difinalisasi.
- [ ] Request schema telah difinalisasi.
- [ ] Response schema telah difinalisasi.
- [ ] Feature schema telah sesuai ANN specification.
- [ ] Dataset transfer strategy telah ditentukan.
- [ ] Authentication mechanism telah ditentukan.
- [ ] Error handling telah ditentukan.
- [ ] Timeout dan retry policy telah ditentukan.
- [ ] Model versioning telah ditentukan.
- [ ] Prediction workflow telah disepakati.
- [ ] Training workflow telah disepakati.
- [ ] Testing requirements telah diselaraskan.
- [ ] Integration specification telah konsisten.
- [ ] Deployment configuration telah diselaraskan.

Dokumen ini tidak otomatis dianggap final hanya karena seluruh endpoint sudah ditulis.

---

# 19. Document Governance

Dokumen ini merupakan acuan kontrak komunikasi antara Laravel dan Python ANN Service.

Ketentuan:

1. Perubahan API harus dievaluasi terhadap `INTEGRATION_SPECIFICATION.md`.
2. Perubahan feature dan preprocessing harus dievaluasi terhadap `ANN_SPECIFICATION.md`.
3. Perubahan data structure harus dievaluasi terhadap `DATABASE_DESIGN.md`.
4. Perubahan business rules harus mengikuti `PROJECT_SPEC.md`.
5. Perubahan arsitektur harus mengikuti keputusan dalam `PROJECT_MASTER.md`.
6. Setiap perubahan yang disetujui harus disinkronkan ke dokumen terkait.
7. AI coding assistant tidak boleh mengubah kontrak API secara sepihak.

---

# 20. Final Notes

API Contract ini mendefinisikan batas komunikasi antara aplikasi Laravel dan Python ANN Service.

Prinsip utamanya:

- Laravel owns operational data.
- Python owns machine learning execution.
- Communication happens through REST API.
- Business rules remain in Laravel.
- Machine learning preprocessing and inference remain in Python.
- Prediction does not directly modify inventory.
- Python failure does not interrupt POS operations.
- Model activation requires evaluation and approval.

**API Contract Status: DRAFT — TECHNICAL PLANNING**
