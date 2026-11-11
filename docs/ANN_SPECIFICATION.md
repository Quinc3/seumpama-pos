
# ANN_SPECIFICATION.md

# SEUMPAMA POS
## Artificial Neural Network Specification

| Information | Description |
|---|---|
| Project | Seumpama POS |
| Document | ANN Specification |
| Machine Learning Method | Artificial Neural Network |
| Initial Architecture | Multi-Layer Perceptron (MLP) |
| ML Service | Python |
| Main Application | Laravel |
| Prediction Target | Daily Core Ingredient Demand |
| Prediction Granularity | Daily Operational Business Day |
| Status | Draft - Technical Planning |
| Timezone | Asia/Jakarta |

---

# 1. Purpose

Dokumen ini mendefinisikan perencanaan penggunaan Artificial Neural Network (ANN) untuk memprediksi kebutuhan bahan baku pada Coffee Shop Seumpama Bunga.

ANN digunakan untuk memperkirakan kebutuhan harian bahan baku inti berdasarkan data historis dan faktor pendukung yang tersedia.

Hasil prediksi akan digunakan sebagai salah satu input dalam proses inventory forecasting dan stock replenishment recommendation.

ANN tidak digunakan untuk:

- Mengelola transaksi POS.
- Mengubah stok aktual.
- Menggantikan stock opname.
- Membuat purchase order otomatis.
- Mengambil keputusan pembelian tanpa persetujuan pengguna.

Dokumen ini menjadi acuan utama untuk metodologi machine learning, dataset, preprocessing, model architecture, training, evaluation, dan prediction.

---

# 2. System Architecture and Responsibilities

## 2.1 Laravel Responsibilities

Laravel merupakan aplikasi utama dan pemilik data operasional.

Laravel bertanggung jawab atas:

1. Mengambil data transaksi dan inventory.
2. Mengelola produk, bahan baku, dan recipe.
3. Menghitung theoretical ingredient consumption.
4. Mengelompokkan data berdasarkan operational business day.
5. Memvalidasi business rules.
6. Menentukan target ingredient.
7. Menyiapkan dataset berdasarkan data operasional.
8. Mengelola dataset version.
9. Mengirim dataset dan prediction request ke Python.
10. Menerima dan memvalidasi hasil prediction.
11. Menyimpan prediction result dan metadata yang dibutuhkan aplikasi.
12. Menghitung stock forecast dan replenishment recommendation.

Laravel tidak bertanggung jawab melakukan preprocessing internal model seperti fitting scaler atau encoding yang merupakan bagian dari pipeline machine learning.

## 2.2 Python ANN Service Responsibilities

Python bertanggung jawab atas proses machine learning.

Tanggung jawabnya:

1. Memvalidasi struktur input dataset.
2. Memeriksa kelengkapan data untuk kebutuhan machine learning.
3. Melakukan data preprocessing.
4. Melakukan feature engineering.
5. Melakukan encoding dan normalization.
6. Membagi dataset untuk training, validation, dan testing.
7. Melakukan ANN training.
8. Melakukan model evaluation.
9. Menyimpan model artifacts.
10. Menyimpan preprocessing artifacts.
11. Melakukan inference.
12. Mengembalikan hasil training, evaluation, dan prediction kepada Laravel.

Python tidak boleh mengubah data operasional Laravel secara langsung.

## 2.3 Data Ownership

| Component | Responsibility |
|---|---|
| Laravel | Operational data ownership |
| Laravel | Business data preparation |
| Laravel | Dataset business versioning |
| Python | Machine learning preprocessing |
| Python | ANN training |
| Python | Model evaluation |
| Python | Model artifacts |
| Python | Inference |
| Laravel | Prediction result storage |
| Laravel | Inventory forecasting |
| Laravel | Replenishment recommendation |

Integrasi mengikuti `INTEGRATION_SPECIFICATION.md` dan `API_CONTRACT.md`.

---

# 3. Prediction Objective

## 3.1 Main Objective

Memprediksi kebutuhan harian bahan baku inti berdasarkan pola konsumsi historis dan faktor pendukung.

## 3.2 Prediction Target

Target prediction adalah:

**Daily Theoretical Ingredient Consumption**

Artinya, model memperkirakan jumlah bahan baku yang dibutuhkan berdasarkan konsumsi teoritis dari transaksi penjualan.

Rumus dasar:

`Theoretical Consumption = Quantity Sold × Recipe Quantity`

Jika terdapat beberapa produk yang menggunakan bahan baku yang sama:

`Daily Ingredient Demand = Σ (Quantity Sold × Recipe Quantity)`

Contoh:

| Product | Quantity Sold | Ingredient per Product | Consumption |
|---|---:|---:|---:|
| Coffee A | 10 | 15 gram | 150 gram |
| Coffee B | 5 | 18 gram | 90 gram |
| Total | 15 | - | 240 gram |

Total theoretical consumption bahan baku tersebut adalah 240 gram.

## 3.3 Theoretical vs Actual Consumption

Sistem harus membedakan:

**Theoretical Consumption**

Konsumsi yang dihitung dari jumlah produk terjual dikalikan recipe yang berlaku.

**Actual Physical Consumption**

Konsumsi aktual yang terjadi di lapangan, yang dapat berbeda karena waste, kesalahan pembuatan, tumpahan, atau faktor operasional lainnya.

Target awal ANN menggunakan theoretical consumption berdasarkan data transaksi dan recipe.

Actual physical consumption tidak boleh dianggap tersedia apabila belum dicatat dan diverifikasi.

## 3.4 Prediction Granularity

Prediction dilakukan pada tingkat:

- Satu operational business day.
- Satu target ingredient.
- Satu nilai kebutuhan harian.

Dengan demikian, setiap observasi target merepresentasikan satu kombinasi tanggal operasional dan bahan baku.

---

# 4. Operational Business Day

Seluruh dataset dan prediction harus mengikuti operational business day.

Jadwal operasional awal:

| Day | Opening | Closing |
|---|---|---|
| Sunday–Thursday | 15:00 | 23:00 |
| Friday–Saturday | 15:00 | 24:00 |

Operasional yang melewati tengah malam tetap menjadi bagian dari business date ketika sesi operasional dimulai.

Contoh:

Transaksi pada Sabtu pukul 00:15 tetap dapat termasuk dalam business day Jumat apabila masih berada dalam sesi operasional Jumat.

Ketentuan:

1. Dataset tidak boleh menggunakan pergantian tanggal kalender secara langsung sebagai pengganti business day.
2. Seluruh transaksi harus terhubung dengan operational business date.
3. Weather aggregation harus mengikuti periode operasional yang relevan.
4. Prediction date harus merepresentasikan business date.
5. Perubahan jadwal operasional harus memperhitungkan konsistensi data historis.

Detail pengelolaan business session mengikuti `PROJECT_SPEC.md` dan `DATABASE_DESIGN.md`.

---

# 5. Dataset Specification

## 5.1 Dataset Sources

Dataset ANN dapat berasal dari:

| Data Source | Usage |
|---|---|
| Transactions | Historical sales |
| Order items | Product quantity sold |
| Recipes | Ingredient usage per product |
| Ingredient usage records | Historical consumption reference |
| Weather observations | Historical weather features |
| Weather forecast | Future weather features |
| Operational calendar | Day, weekend, holiday, operating status |
| Business settings | Operational schedule |

Laravel bertanggung jawab mengambil dan menyiapkan data operasional dari database.

Python bertanggung jawab memproses data tersebut untuk kebutuhan machine learning.

## 5.2 Dataset Period

Target pengumpulan data:

- Minimum planning target: 3 months.
- Recommended target: 6 months.

Periode tersebut merupakan target awal, bukan jaminan bahwa dataset cukup untuk menghasilkan model yang akurat.

Kelayakan dataset harus dievaluasi berdasarkan:

- Jumlah observasi.
- Kelengkapan data.
- Variasi permintaan.
- Ketersediaan historical features.
- Konsistensi recipe.
- Ketersediaan weather data.
- Kebutuhan chronological validation.

Jika data belum mencukupi, sistem tidak boleh mengklaim ANN sudah layak digunakan.

## 5.3 Dataset Granularity

Granularitas awal dataset:

**One record per business date per target ingredient.**

Contoh:

| Business Date | Ingredient | Consumption |
|---|---|---:|
| 2026-06-01 | ING-001 | 250 |
| 2026-06-02 | ING-001 | 275 |
| 2026-06-03 | ING-001 | 220 |
| 2026-06-01 | ING-002 | 120 |
| 2026-06-02 | ING-002 | 140 |

Setiap record harus dapat diidentifikasi berdasarkan:

- Business date.
- Ingredient identifier.
- Dataset version.

## 5.4 Dataset Versioning

Dataset harus memiliki identitas versi.

Informasi minimum:

- Dataset version.
- Dataset creation timestamp.
- Historical period.
- Target ingredients.
- Total records.
- Data source.
- Validation status.
- Feature configuration version jika sudah ditetapkan.

Dataset yang telah digunakan untuk training harus dapat ditelusuri kembali.

Perubahan recipe, historical records, atau feature configuration tidak boleh mengubah dataset historis yang telah digunakan tanpa membuat versi dataset baru.

## 5.5 Data Quality

Data harus diperiksa sebelum digunakan.

Hal yang perlu diperhatikan:

- Missing transaction records.
- Invalid transactions.
- Cancelled transactions.
- Recipe availability.
- Recipe changes.
- Missing ingredient consumption.
- Zero consumption.
- Closed business days.
- Missing weather observations.
- Incomplete operational data.
- Simulated records.

Dataset tidak boleh menganggap seluruh tanggal tanpa transaksi sebagai tanggal dengan konsumsi nol sebelum status operasional dan kelengkapan datanya diverifikasi.

---

# 6. Feature Specification

Feature merupakan variabel input yang digunakan model untuk memprediksi kebutuhan bahan baku.

Feature berikut merupakan kandidat awal dan belum seluruhnya menjadi feature final.

## 6.1 Historical Ingredient Consumption

Historical consumption merupakan kandidat feature utama.

Kandidat lag:

| Feature | Description |
|---|---|
| Lag D-1 | Consumption satu business day sebelumnya |
| Lag D-2 | Consumption dua business days sebelumnya |
| Lag D-3 | Consumption tiga business days sebelumnya |
| Lag D-7 | Consumption tujuh business days sebelumnya |

Contoh:

Untuk memprediksi kebutuhan tanggal 10 Juni:

- D-1 menggunakan data 9 Juni.
- D-2 menggunakan data 8 Juni.
- D-3 menggunakan data 7 Juni.
- D-7 menggunakan data 3 Juni.

Perhitungan harus menggunakan urutan operational business day yang valid.

Tanggal operasional yang tutup atau memiliki data hilang harus ditangani sesuai kebijakan dataset.

## 6.2 Rolling Consumption Features

Kandidat rolling features:

| Feature | Description |
|---|---|
| Rolling Mean 3 | Rata-rata consumption tiga observasi operasional sebelumnya |
| Rolling Mean 7 | Rata-rata consumption tujuh observasi operasional sebelumnya |
| Rolling Standard Deviation | Variasi consumption dalam window yang ditentukan |

Rolling features hanya boleh menggunakan data yang sudah tersedia sebelum prediction date.

Window dan metode perhitungan final harus dievaluasi berdasarkan dataset aktual.

## 6.3 Historical Sales Features

Historical sales dapat digunakan sebagai feature tambahan.

Contoh kandidat:

- Total transaction count.
- Total product quantity sold.
- Historical product sales.
- Historical sales aggregation.

Penggunaan feature sales harus dievaluasi berdasarkan kontribusinya terhadap prediksi kebutuhan ingredient.

Jangan memasukkan variabel yang secara tidak langsung menyebabkan data leakage.

## 6.4 Weather Features

Kandidat weather features:

| Feature | Unit |
|---|---|
| Average Temperature | °C |
| Total Rainfall | mm |
| Weather Condition | Categorical |

Weather data harus dibedakan berdasarkan jenisnya.

**Historical Weather**

Cuaca aktual yang digunakan untuk observasi historis.

**Forecast Weather**

Prakiraan cuaca yang tersedia ketika prediction dibuat.

Untuk prediction tanggal mendatang, sistem tidak boleh menggunakan actual weather yang baru diketahui setelah tanggal tersebut berlalu.

Jika weather forecast tidak tersedia:

- Missing weather harus tetap diidentifikasi.
- Jangan mengganti missing weather dengan nol tanpa dasar.
- Penanganan missing feature mengikuti preprocessing policy.
- Prediction harus dapat mencatat keterbatasan input yang digunakan.

## 6.5 Calendar Features

Kandidat calendar features:

- Day of week.
- Weekend indicator.
- Holiday indicator.
- Operational day status.

Calendar features harus menggunakan business date dan kalender operasional yang relevan.

## 6.6 Feature Selection

Feature final belum ditetapkan.

Pemilihan feature dilakukan berdasarkan:

1. Ketersediaan data aktual.
2. Kualitas data.
3. Hubungan dengan target prediction.
4. Kebutuhan historical window.
5. Risiko data leakage.
6. Hasil eksperimen dan evaluation.

Jangan memasukkan semua kandidat feature secara otomatis.

Feature yang tidak tersedia atau tidak terbukti berguna dapat dikeluarkan dari model.

---

# 7. Feature Schema and API Integration

## 7.1 General Rule

Feature schema untuk training dan prediction harus konsisten.

Setiap model harus mengetahui:

- Feature names.
- Feature order.
- Feature types.
- Feature units.
- Missing value policy.
- Preprocessing configuration.
- Feature schema version.

## 7.2 Laravel Data Preparation

Laravel bertanggung jawab:

1. Mengambil historical operational data.
2. Menghitung theoretical consumption.
3. Mengidentifikasi target ingredients.
4. Mengelompokkan data berdasarkan business date.
5. Mengidentifikasi missing atau invalid business records.
6. Menyiapkan historical features yang berasal dari data operasional.
7. Menyiapkan weather dan calendar data yang tersedia.
8. Menyusun payload sesuai API Contract.

## 7.3 Python Feature Processing

Python bertanggung jawab:

1. Memvalidasi feature schema.
2. Memeriksa tipe dan format feature.
3. Melakukan transformasi machine learning.
4. Melakukan encoding.
5. Melakukan normalization.
6. Memastikan urutan feature konsisten.
7. Menggunakan preprocessing artifacts yang sesuai model version.

Python tidak boleh menggunakan feature yang tidak terdapat pada schema model yang sedang dijalankan.

## 7.4 API Contract Reference

Komunikasi menggunakan endpoint yang didefinisikan dalam `API_CONTRACT.md`.

Endpoint utama:

| Endpoint | Purpose |
|---|---|
| `GET /api/v1/health` | Service health check |
| `GET /api/v1/model/status` | Model status |
| `POST /api/v1/datasets/validate` | Dataset validation |
| `POST /api/v1/predictions/demand` | Demand prediction |
| `POST /api/v1/model/train` | ANN training |
| `GET /api/v1/model/train/{training_id}` | Training status |

Struktur request dan response harus mengikuti API Contract.

Perubahan feature schema harus disinkronkan ke `API_CONTRACT.md`.

Contoh payload API tidak boleh dianggap sebagai feature specification final apabila statusnya masih provisional.

---

# 8. Data Preprocessing

Preprocessing dilakukan oleh Python ANN Service.

Tujuannya menyiapkan input agar sesuai dengan kebutuhan model.

## 8.1 Data Validation

Pemeriksaan meliputi:

- Missing values.
- Invalid numeric values.
- Duplicate observations.
- Incorrect data types.
- Inconsistent units.
- Invalid dates.
- Inconsistent feature schema.
- Insufficient historical observations.

Validasi teknis tidak menggantikan validasi business rules yang dilakukan Laravel.

## 8.2 Missing Value Handling

Missing data harus dibedakan berdasarkan penyebabnya.

Contoh:

| Condition | Handling |
|---|---|
| Zero consumption | Tetap menjadi nilai nol jika sudah terverifikasi |
| Missing consumption | Ditandai sebagai missing |
| Closed business day | Ditangani berdasarkan operational calendar |
| Missing weather | Ditandai sebagai missing |
| Invalid record | Dikeluarkan atau ditangani sesuai kebijakan |
| Simulated data | Dipisahkan dari data aktual |

Strategi imputasi atau penghapusan data harus dievaluasi dan dicatat.

Tidak boleh mengisi semua missing values dengan nol secara otomatis.

## 8.3 Feature Engineering

Python dapat melakukan:

- Lag feature generation jika input belum disiapkan Laravel.
- Rolling statistics.
- Categorical encoding.
- Numerical transformation.
- Feature alignment.

Feature engineering harus menggunakan data yang tersedia pada waktu prediction.

Jangan menggunakan informasi masa depan.

## 8.4 Normalization and Scaling

Feature numerical dapat menggunakan normalization atau standardization.

Scaler harus:

1. Di-fit hanya menggunakan training data.
2. Digunakan untuk validation dan test data tanpa fitting ulang.
3. Disimpan sebagai preprocessing artifact.
4. Digunakan kembali ketika inference.
5. Memiliki identitas dan versi yang terkait dengan model.

Scaler training tidak boleh dihitung ulang menggunakan seluruh dataset.

## 8.5 Training-Inference Consistency

Preprocessing ketika prediction harus sama dengan preprocessing ketika model dilatih.

Ketentuan:

- Feature names harus sama.
- Feature order harus sama.
- Feature units harus sama.
- Encoding harus sama.
- Scaling harus sama.
- Missing value handling harus sama.
- Feature schema version harus kompatibel.

Model tidak boleh melakukan inference menggunakan preprocessing configuration yang tidak sesuai.

---

# 9. ANN Model Architecture

## 9.1 Initial Model

Model awal yang direncanakan:

**Multi-Layer Perceptron (MLP)**

Arsitektur baseline:

```text
Input Layer
     |
Dense Layer (16 Neurons, ReLU)
     |
Dense Layer (8 Neurons, ReLU)
     |
Output Layer (1 Neuron, Linear)
     |
Predicted Daily Ingredient Demand
```

Arsitektur ini merupakan baseline eksperimen, bukan keputusan bahwa konfigurasi tersebut pasti menjadi model final.

## 9.2 Model Strategy

Strategi awal yang dipertimbangkan:

**Individual Model per Ingredient**

Setiap target ingredient memiliki model tersendiri.

Kelebihan:

- Setiap bahan baku dapat memiliki pola permintaan berbeda.
- Evaluasi dapat dilakukan per ingredient.
- Konfigurasi model dapat disesuaikan berdasarkan karakteristik target.

Kekurangan:

- Membutuhkan lebih banyak model artifacts.
- Training dan maintenance lebih banyak.
- Dataset setiap ingredient mungkin terbatas.

Alternatif:

**Multi-Output Model**

Satu model memprediksi beberapa ingredient sekaligus.

Alternatif ini dapat dievaluasi jika jumlah data dan hubungan antar-target mendukung.

Keputusan final harus mempertimbangkan jumlah target ingredient, jumlah observasi, dan hasil eksperimen.

## 9.3 Output Layer

Output awal berupa satu nilai numerik untuk kebutuhan ingredient.

Nilai prediction harus:

- Memiliki satuan yang jelas.
- Berhubungan dengan target ingredient.
- Memiliki model version.
- Memiliki prediction date.
- Divalidasi sebelum digunakan Laravel.

Penanganan nilai negatif harus ditetapkan sebagai bagian dari output policy.

Prediksi negatif tidak boleh langsung dipakai sebagai kebutuhan stok.

---

# 10. Training Configuration

## 10.1 Initial Hyperparameters

Konfigurasi awal eksperimen:

| Parameter | Initial Value |
|---|---|
| Optimizer | Adam |
| Learning Rate | 0.001 |
| Loss Function | Mean Squared Error |
| Batch Size | 16 |
| Maximum Epochs | 100 |
| Hidden Activation | ReLU |
| Output Activation | Linear |
| Early Stopping | Enabled |

Nilai tersebut merupakan starting point untuk eksperimen, bukan hyperparameter final.

## 10.2 Training Process

Alur training:

1. Menerima dataset dari Laravel.
2. Memvalidasi struktur dataset.
3. Memeriksa data quality.
4. Menentukan target ingredient.
5. Melakukan feature engineering.
6. Melakukan preprocessing.
7. Melakukan chronological dataset split.
8. Melakukan ANN training.
9. Melakukan validation.
10. Melakukan model evaluation.
11. Membandingkan hasil dengan baseline.
12. Menyimpan model artifacts.
13. Menyimpan preprocessing artifacts.
14. Mengembalikan training result kepada Laravel.

## 10.3 Training Reproducibility

Setiap training harus dapat ditelusuri.

Informasi minimum:

- Training ID.
- Dataset version.
- Target ingredient.
- Feature schema version.
- Feature configuration.
- Preprocessing configuration.
- Model architecture.
- Hyperparameters.
- Random seed.
- Training timestamp.
- Evaluation results.
- Model artifact reference.

Tujuannya agar hasil eksperimen dapat diperiksa dan direproduksi.

---

# 11. Dataset Splitting

Dataset harus dibagi berdasarkan urutan waktu.

Random global splitting tidak boleh digunakan untuk evaluasi time-series.

## 11.1 Initial Split

Rencana awal:

| Dataset | Percentage |
|---|---:|
| Training | 70% |
| Validation | 15% |
| Testing | 15% |

Pembagian harus dilakukan secara chronological.

Contoh:

```text
Historical Records
       |
       v
Chronological Ordering
       |
       v
Training Set (70%)
       |
       v
Validation Set (15%)
       |
       v
Testing Set (15%)
```

Rasio tersebut merupakan konfigurasi awal dan dapat dievaluasi berdasarkan jumlah observasi aktual.

## 11.2 Data Leakage Prevention

Hal-hal yang wajib diperhatikan:

1. Jangan menggunakan future consumption sebagai input.
2. Jangan menggunakan actual weather masa depan untuk prediction historis.
3. Jangan melakukan random global split.
4. Jangan melakukan scaler fitting pada seluruh dataset.
5. Jangan membuat rolling features menggunakan observasi setelah prediction date.
6. Jangan memasukkan target value ke dalam feature.
7. Jangan menggunakan informasi yang belum tersedia pada waktu prediction.

## 11.3 Alternative Evaluation

Jika jumlah data dan kondisi eksperimen mendukung, gunakan:

- Walk-forward validation.
- TimeSeriesSplit.
- Rolling-origin evaluation.

Metode final ditentukan berdasarkan kebutuhan dan jumlah observasi yang tersedia.

---

# 12. Model Evaluation

## 12.1 Evaluation Metrics

Metrik utama:

**Mean Absolute Error (MAE)**

Mengukur rata-rata kesalahan absolut antara nilai aktual dan prediksi.

**Root Mean Squared Error (RMSE)**

Mengukur akar rata-rata kuadrat kesalahan sehingga kesalahan besar mendapatkan penalti lebih tinggi.

Rumus:

MAE:

`MAE = (1/n) × Σ |Actual - Predicted|`

RMSE:

`RMSE = √((1/n) × Σ (Actual - Predicted)²)`

Metrik tambahan yang dapat dievaluasi:

- WAPE (Weighted Absolute Percentage Error).

Penggunaan metrik tambahan harus mempertimbangkan kemungkinan nilai aktual nol.

## 12.2 Baseline Comparison

ANN tidak boleh dievaluasi hanya berdasarkan nilai MAE atau RMSE tanpa pembanding.

Kandidat baseline:

- Naive Forecast.
- Historical Average.
- Rolling Average.

ANN harus dibandingkan dengan baseline pada data evaluasi yang sama.

## 12.3 Per-Ingredient Evaluation

Evaluasi dilakukan untuk setiap target ingredient.

Hasil evaluasi harus dapat membedakan performa model antar-bahan baku.

Jangan hanya menggunakan satu nilai evaluasi agregat jika performa setiap ingredient berbeda secara signifikan.

## 12.4 Evaluation Threshold

Threshold penerimaan model belum ditetapkan.

Penetapan threshold harus mempertimbangkan:

- Karakteristik ingredient.
- Satuan pengukuran.
- Variasi demand.
- Baseline performance.
- Kualitas dataset.
- Kebutuhan operasional.

Jangan membuat threshold akurasi tanpa dasar evaluasi.

---

# 13. Model Versioning and Lifecycle

Setiap model harus memiliki versi.

Informasi minimum:

- Model version.
- Training ID.
- Dataset version.
- Target ingredient.
- Feature schema version.
- Architecture.
- Hyperparameters.
- Preprocessing configuration.
- Evaluation metrics.
- Artifact location.
- Created timestamp.
- Model status.

## 13.1 Model Status

| Status | Description |
|---|---|
| Candidate | Model baru yang menunggu evaluasi atau persetujuan |
| Active | Model yang digunakan untuk prediction |
| Archived | Model yang tidak lagi digunakan |

## 13.2 Model Activation

Ketentuan:

1. Model baru harus melalui evaluasi.
2. Model harus dibandingkan dengan baseline.
3. Model harus memenuhi kriteria penerimaan yang disetujui.
4. Aktivasi model membutuhkan persetujuan.
5. Model lama harus tetap dapat ditelusuri.
6. Model baru tidak otomatis menggantikan model aktif.
7. Model yang gagal evaluasi tidak boleh digunakan untuk prediction operasional.

## 13.3 Model Artifact Storage

Python bertanggung jawab menyimpan:

- Model weights.
- Model configuration.
- Scaler.
- Encoder.
- Feature configuration.
- Metadata preprocessing.

Laravel menyimpan metadata dan referensi artifact yang dibutuhkan aplikasi.

Python tidak boleh mengubah database operasional Laravel untuk mencatat model artifacts.

Detail penyimpanan mengikuti `DEPLOYMENT_GUIDE.md`.

---

# 14. Prediction Workflow

## 14.1 Prediction Schedule

Prediction direncanakan dilakukan setelah business day selesai dan data operasional selesai direkonsiliasi.

Prediction dapat dipicu melalui:

- Scheduled process.
- Manual trigger oleh pengguna yang memiliki permission.

Jadwal final belum ditetapkan.

## 14.2 Prediction Process

Alur prediction:

1. Laravel menentukan prediction date.
2. Laravel mengambil historical operational data.
3. Laravel menyiapkan historical features.
4. Laravel mengambil weather forecast yang tersedia jika digunakan.
5. Laravel melakukan business validation.
6. Laravel menyusun prediction request.
7. Laravel mengirim request ke Python.
8. Python memvalidasi payload.
9. Python memeriksa model version.
10. Python melakukan preprocessing menggunakan artifacts model.
11. Python menjalankan inference.
12. Python mengembalikan prediction result.
13. Laravel memvalidasi response.
14. Laravel menyimpan prediction dan metadata.
15. Laravel menyediakan hasil untuk stock forecasting.

## 14.3 Prediction Output

Setiap prediction minimum memiliki:

- Prediction date.
- Ingredient identifier.
- Predicted quantity.
- Unit.
- Model version.
- Generated timestamp.
- Prediction status.

Contoh:

| Field | Example |
|---|---|
| Prediction Date | 2026-07-01 |
| Ingredient | ING-001 |
| Predicted Quantity | 12.75 |
| Unit | Gram |
| Model Version | ann-001 |
| Status | Valid |

Nilai tersebut hanya contoh format, bukan data aktual.

## 14.4 Prediction Failure

Jika prediction gagal:

- Jangan menyimpan hasil sebagai prediction valid.
- Catat error.
- Jangan mengubah stok.
- Jangan menghentikan operasional POS.
- Tampilkan status unavailable atau prediction terakhir yang masih valid dengan timestamp.

Prediction lama tidak boleh ditampilkan seolah-olah merupakan hasil terbaru.

---

# 15. Forecasting and Inventory Integration

ANN hanya memprediksi kebutuhan bahan baku.

Laravel bertanggung jawab melakukan perhitungan inventory dan replenishment.

Input forecasting:

- ANN predicted demand.
- Current stock.
- Safety stock.
- Supplier lead time.
- Historical demand statistics.

Formula awal:

`Safety Stock = Z × σd × √L`

`Reorder Point = Average Daily Demand × L + Safety Stock`

Keterangan:

- Z = service level factor.
- σd = standard deviation of daily demand.
- L = supplier lead time.

Replenishment recommendation merupakan saran untuk pengguna.

Sistem tidak boleh melakukan pembelian otomatis berdasarkan prediksi ANN.

Detail business rules mengikuti `PROJECT_SPEC.md`.

---

# 16. Python Service Reliability

Python ANN Service merupakan komponen pendukung dan tidak boleh menjadi single point of failure untuk POS.

Ketentuan:

1. POS tetap beroperasi jika Python tidak tersedia.
2. Transaksi tidak boleh bergantung pada prediction response.
3. Inventory management tetap berjalan.
4. Training dapat ditunda jika service tidak tersedia.
5. Prediction dapat ditunda jika service tidak tersedia.
6. Laravel harus mencatat kegagalan komunikasi.
7. Request harus menggunakan timeout.
8. Retry harus dibatasi.
9. Prediction terakhir yang valid dapat digunakan sebagai informasi historis dengan timestamp yang jelas.

---

# 17. API Integration

Seluruh komunikasi mengikuti `API_CONTRACT.md`.

Endpoint yang direncanakan:

| Endpoint | Purpose |
|---|---|
| `GET /api/v1/health` | Service health check |
| `GET /api/v1/model/status` | Model status |
| `POST /api/v1/datasets/validate` | Dataset validation |
| `POST /api/v1/predictions/demand` | Demand prediction |
| `POST /api/v1/model/train` | Model training |
| `GET /api/v1/model/train/{training_id}` | Training status |

Ketentuan:

1. Request dan response menggunakan JSON.
2. API menggunakan version prefix.
3. Authentication mengikuti kontrak integrasi.
4. Feature schema harus kompatibel.
5. Model version harus dapat ditelusuri.
6. Training harus asynchronous.
7. Prediction tidak boleh memblokir POS.
8. Perubahan payload harus disinkronkan ke API Contract.

---

# 18. Testing Requirements

Testing ANN harus mencakup beberapa tingkat.

## 18.1 Dataset Testing

- Dataset structure validation.
- Missing values.
- Duplicate records.
- Zero consumption.
- Invalid consumption.
- Closed business days.
- Missing weather.
- Recipe changes.
- Dataset versioning.
- Insufficient observations.

## 18.2 Preprocessing Testing

- Feature order.
- Feature types.
- Missing value handling.
- Encoding.
- Scaling.
- Training-inference consistency.
- Prevention of data leakage.

## 18.3 Model Testing

- Training execution.
- Prediction execution.
- Model loading.
- Model version compatibility.
- Output validation.
- Negative prediction handling.
- Reproducibility.

## 18.4 Evaluation Testing

- MAE calculation.
- RMSE calculation.
- Baseline comparison.
- Per-ingredient evaluation.
- Chronological split validation.

## 18.5 Integration Testing

- Laravel request preparation.
- Python request validation.
- Prediction response.
- Training request.
- Training status.
- Timeout handling.
- Service unavailable handling.
- Invalid response handling.

Strategi testing lengkap mengikuti `TESTING_STRATEGY.md`.

---

# 19. Open Decisions

Keputusan berikut belum ditetapkan secara final.

| No. | Decision | Status |
|---|---|---|
| 1 | Final target ingredient list | TBD |
| 2 | Final dataset period | TBD |
| 3 | Final feature selection | TBD |
| 4 | Historical observation window | TBD |
| 5 | Rolling window configuration | TBD |
| 6 | Weather provider | TBD |
| 7 | Weather forecast integration | TBD |
| 8 | Missing value strategy | TBD |
| 9 | Final feature schema | TBD |
| 10 | Individual vs multi-output model | TBD |
| 11 | Final ANN architecture | TBD |
| 12 | Final hyperparameters | TBD |
| 13 | Final dataset split configuration | Provisional |
| 14 | Evaluation acceptance threshold | TBD |
| 15 | Model artifact storage | TBD |
| 16 | Prediction schedule | TBD |
| 17 | Model activation approval workflow | TBD |
| 18 | Python framework | TBD |

Keputusan tersebut harus ditetapkan berdasarkan kebutuhan aktual, ketersediaan data, eksperimen, dan hasil evaluasi.

AI coding assistant tidak boleh menetapkan keputusan tersebut secara sepihak.

---

# 20. Completion Criteria

ANN Specification dianggap siap untuk implementasi apabila:

- [ ] Prediction objective telah disepakati.
- [ ] Target ingredients telah ditentukan.
- [ ] Dataset source telah diverifikasi.
- [ ] Dataset granularity telah disepakati.
- [ ] Operational business day telah konsisten.
- [ ] Feature schema telah ditetapkan.
- [ ] Historical observation window telah ditetapkan.
- [ ] Weather data policy telah ditetapkan.
- [ ] Missing value policy telah ditetapkan.
- [ ] Model architecture telah diputuskan.
- [ ] Training configuration telah diputuskan.
- [ ] Dataset splitting telah diputuskan.
- [ ] Evaluation metrics telah ditetapkan.
- [ ] Baseline comparison telah ditentukan.
- [ ] Model versioning telah ditentukan.
- [ ] Prediction workflow telah disepakati.
- [ ] API Contract telah diselaraskan.
- [ ] Integration Specification telah diselaraskan.
- [ ] Testing requirements telah diselaraskan.

Dokumen ini tidak otomatis dianggap final hanya karena rancangan model sudah ditulis.

---

# 21. Document Governance

Dokumen ini merupakan acuan utama untuk metodologi dan kebutuhan machine learning Seumpama POS.

Ketentuan:

1. Perubahan ANN methodology harus dicatat dalam dokumen ini.
2. Perubahan API payload harus disinkronkan dengan `API_CONTRACT.md`.
3. Perubahan integrasi harus disinkronkan dengan `INTEGRATION_SPECIFICATION.md`.
4. Perubahan database harus disinkronkan dengan `DATABASE_DESIGN.md`.
5. Perubahan business rules harus mengikuti `PROJECT_SPEC.md`.
6. Perubahan arsitektur harus mengikuti keputusan `PROJECT_MASTER.md`.
7. Perubahan yang belum disetujui harus tetap berstatus Draft, TBD, atau Provisional.

---

# 22. Final Notes

ANN digunakan untuk memperkirakan kebutuhan harian bahan baku inti berdasarkan data historis dan faktor pendukung.

Prinsip utama:

- Laravel owns operational data.
- Python handles machine learning.
- Dataset berasal dari data operasional yang telah divalidasi.
- Prediction menggunakan operational business day.
- Theoretical consumption menjadi target awal.
- Training dan inference menggunakan preprocessing yang konsisten.
- Chronological evaluation digunakan untuk mencegah data leakage.
- ANN harus dibandingkan dengan baseline.
- Model baru harus dievaluasi sebelum aktivasi.
- Prediction tidak mengubah stok aktual.
- Python failure tidak menghentikan POS.

**ANN Specification Status: DRAFT — TECHNICAL PLANNING**
