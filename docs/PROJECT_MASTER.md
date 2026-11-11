# PROJECT_MASTER.md

# SEUMPAMA POS
**Master Project Document**

| Informasi | Keterangan |
|---|---|
| Nama Proyek | Seumpama POS |
| Jenis Aplikasi | Web-Based POS and Inventory Management System |
| Objek Penelitian | Coffee Shop Seumpama Bunga |
| Lokasi | Tigaraksa, Kabupaten Tangerang, Banten |
| Backend | Laravel |
| ML Service | Python |
| Frontend | Laravel Blade |
| CSS Framework | Tailwind CSS |
| Frontend Interaction | Alpine.js |
| Database | MySQL |
| ANN Framework | Keras |
| Weather Provider | Open-Meteo |
| Metode Prediksi | Artificial Neural Network |
| Cakupan | Full Scope |
| Status | Perencanaan dan penyusunan spesifikasi |

---

## 1. PROJECT OVERVIEW

### 1.1 Project Identity

Seumpama POS merupakan aplikasi Point of Sale (POS) berbasis web yang dikembangkan untuk mendukung kegiatan operasional Coffee Shop Seumpama Bunga.

Aplikasi dirancang untuk mengintegrasikan transaksi penjualan, pengelolaan produk, pengelolaan bahan baku, inventaris, pembelian, pelaporan operasional, serta prediksi kebutuhan bahan baku menggunakan metode Artificial Neural Network (ANN).

Sistem tidak hanya berfungsi sebagai aplikasi kasir, tetapi juga sebagai sistem pendukung pengambilan keputusan dalam pengelolaan persediaan bahan baku.

### 1.2 Background

Permasalahan yang menjadi perhatian dalam pengembangan sistem:

- Pencatatan transaksi penjualan.
- Pemantauan persediaan bahan baku.
- Pencatatan penggunaan bahan baku berdasarkan resep.
- Pemantauan pergerakan stok.
- Penentuan kebutuhan pembelian bahan baku.
- Perkiraan kebutuhan bahan baku berdasarkan pola konsumsi historis.

Sistem dikembangkan untuk mengintegrasikan data operasional tersebut sehingga dapat digunakan sebagai dasar pengelolaan persediaan dan penelitian prediksi.

### 1.3 Project Objectives

1. Mengintegrasikan transaksi penjualan dan pengelolaan persediaan.
2. Mempermudah pencatatan dan pemantauan stok.
3. Menghitung konsumsi bahan baku berdasarkan transaksi dan resep produk.
4. Menyediakan laporan operasional.
5. Mengimplementasikan ANN untuk memprediksi kebutuhan bahan baku.
6. Menyediakan rekomendasi pengadaan berdasarkan prediksi dan persediaan aktual.
7. Mengembangkan sistem yang modular, responsif, dan dapat dipelihara.

---

## 2. PROJECT SCOPE

### 2.1 Scope Classification

Pengembangan menggunakan cakupan full scope, tetapi implementasi dilakukan secara bertahap berdasarkan prioritas dan dependensi antarmodul.

### 2.2 Main Functional Areas

| Area | Cakupan |
|---|---|
| Authentication | Login, logout, session |
| User Management | Pengguna dan hak akses |
| Dashboard | Ringkasan operasional |
| Product Management | Produk dan kategori |
| POS | Keranjang, transaksi, pembayaran, struk |
| Recipe / BOM | Komposisi bahan baku |
| Inventory | Stok dan satuan |
| Stock Movement | Pergerakan stok |
| Supplier | Pengelolaan pemasok |
| Purchasing | Pembelian dan penerimaan |
| Customer | Data pelanggan |
| Promotion | Diskon dan promosi |
| Expense | Pengeluaran operasional |
| Reports | Laporan operasional |
| Weather | Integrasi data cuaca |
| Dataset | Pengelolaan dataset |
| ANN Engine | Training dan evaluasi ANN |
| Forecasting | Prediksi kebutuhan bahan baku |
| Procurement Recommendation | Rekomendasi pengadaan |
| Attendance | Kehadiran karyawan, modular |
| Settings | Konfigurasi sistem |

Keberadaan modul dalam cakupan tidak berarti semuanya harus selesai pada tahap pertama.

---

## 3. SYSTEM ARCHITECTURE

### 3.1 Architecture Decision

Arsitektur menggunakan pendekatan **Laravel + Python**.

Laravel menjadi aplikasi utama, sedangkan Python menjadi layanan khusus pemrosesan machine learning.

### 3.2 Laravel Application

Laravel bertanggung jawab atas:

- Authentication dan authorization.
- User dan role management.
- POS dan transaksi penjualan.
- Product dan category management.
- Recipe dan BOM.
- Inventory dan stock movement.
- Supplier dan purchasing.
- Reporting dan dashboard.
- Pengelolaan data cuaca.
- Dataset management.
- Integrasi layanan Python.
- Penyimpanan hasil prediksi.
- Audit aktivitas.

Laravel merupakan pusat pengelolaan aturan bisnis.

### 3.3 Python Application

Python bertanggung jawab atas:

- Dataset preprocessing.
- Data validation.
- Feature engineering.
- Normalization.
- ANN model training.
- Model evaluation.
- Prediction.
- Model persistence.
- Model versioning.

Framework ANN yang digunakan adalah Keras.

Python tidak mengelola transaksi bisnis secara langsung.

### 3.4 Communication Flow

Komunikasi Laravel dan Python menggunakan internal API.

Alur:

1. Laravel menyiapkan data operasional.
2. Dataset diproses dan divalidasi.
3. Laravel mengirimkan data yang dibutuhkan kepada Python.
4. Python menjalankan training atau prediction.
5. Python mengembalikan hasil pemrosesan.
6. Laravel memvalidasi dan menyimpan hasil.
7. Hasil ditampilkan melalui dashboard dan laporan.

Proses training tidak boleh menghambat transaksi kasir.

Kegagalan layanan Python tidak boleh menyebabkan POS berhenti berfungsi.

### 3.5 Database

Database menggunakan MySQL.

Seluruh data operasional utama dikelola melalui Laravel.

Data mencakup:

- Pengguna dan permission.
- Produk dan kategori.
- Bahan baku dan satuan.
- Resep dan BOM.
- Transaksi penjualan.
- Detail transaksi.
- Pergerakan stok.
- Supplier dan purchasing.
- Data cuaca.
- Dataset.
- Model ANN.
- Riwayat training.
- Hasil prediksi.
- Rekomendasi pengadaan.
- Audit log.

Python menerima data melalui mekanisme integrasi yang telah ditentukan dan tidak mengubah data operasional secara langsung.

### 3.6 Architectural Principles

- Laravel tetap menjadi pusat business logic.
- Python hanya menangani kebutuhan machine learning.
- Database menjadi sumber data utama.
- Komunikasi antarlayanan harus terdefinisi.
- Training berjalan terpisah dari transaksi POS.
- Prediksi dapat dijalankan secara terjadwal maupun manual sesuai permission.
- Model ANN harus memiliki versi.
- Integrasi eksternal menggunakan adapter jika diperlukan.
- Hindari microservices yang tidak memiliki kebutuhan teknis jelas.

---

## 4. FRONTEND AND UI TECHNOLOGY

### 4.1 Technology Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel |
| Template Engine | Blade |
| CSS | Tailwind CSS |
| Interaction | Alpine.js |
| Additional JavaScript | JavaScript |
| Icons | Lucide Icons |
| Database | MySQL |

Tidak menggunakan SPA sebagai kebutuhan utama.

### 4.2 Responsive Design

Sistem mendukung:

- Desktop.
- Tablet.
- Smartphone.

Setiap perangkat harus mendapatkan penyesuaian struktur, navigasi, dan interaksi.

### 4.3 UI Design Direction

Arah visual:

- Profesional dan modern.
- Bersih dan mudah dibaca.
- Memiliki hierarki informasi yang jelas.
- Tidak menyerupai template CRUD generik.
- Mengutamakan efisiensi operasional.
- Konsisten antarhalaman.

### 4.4 Color System

Warna utama menggunakan aksen biru.

| Elemen | Arah Warna |
|---|---|
| Primary | Blue 600 / Blue 700 |
| Background | Slate / Off-white |
| Surface | White |
| Primary Text | Slate 900 |
| Secondary Text | Slate 500–600 |
| Success | Green |
| Warning | Amber |
| Danger | Red |

Warna status menggunakan warna semantik.

### 4.5 Layout Direction

**Desktop**
- Sidebar navigation.
- Topbar.
- Dashboard widgets.
- Data tables.
- Multi-column layout.

**Tablet**
- Navigasi yang disesuaikan.
- Komponen dengan ukuran nyaman untuk sentuhan.
- Layout yang mendukung operasional kasir.

**Mobile**
- Drawer navigation.
- Card-based presentation jika diperlukan.
- Tombol tindakan mudah dijangkau.
- POS dengan layout khusus mobile.

### 4.6 POS Interface

Desktop:
- Katalog produk.
- Keranjang dan pembayaran.
- Search dan category filter.
- Kontrol kuantitas.

Tablet:
- Katalog produk dan keranjang dalam layout yang optimal.
- Tombol besar dan mudah disentuh.
- Alur pembayaran sederhana.

Mobile:
- Product grid.
- Search.
- Cart drawer atau bottom sheet.
- Checkout yang mudah dijangkau.

---

## 5. BUSINESS OPERATION

### 5.1 Business Identity

Business Name: Coffee Shop Seumpama Bunga.

Business Type: Coffee Shop.

Location: Tigaraksa, Kabupaten Tangerang, Banten.

### 5.2 Operating Hours

Jam operasional yang digunakan:

| Hari | Jam Buka | Jam Tutup |
|---|---|---|
| Minggu | 15.00 | 23.00 |
| Senin | 15.00 | 23.00 |
| Selasa | 15.00 | 23.00 |
| Rabu | 15.00 | 23.00 |
| Kamis | 15.00 | 23.00 |
| Jumat | 15.00 | 24.00 |
| Sabtu | 15.00 | 24.00 |

Timezone: Asia/Jakarta (WIB).

Jam operasional digunakan sebagai dasar:

- Validasi operasional.
- Rekapitulasi penjualan.
- Agregasi cuaca.
- Pembentukan dataset.
- Laporan harian dan mingguan.

Jam operasional harus dapat dikonfigurasi tanpa perubahan kode program.

### 5.3 Operational Day

Sistem menggunakan konsep hari operasional (*business day*).

Transaksi dikelompokkan berdasarkan sesi operasional, bukan semata-mata tanggal kalender.

Contoh:

Operasional Jumat berlangsung pukul 15.00–24.00.

Batas hari operasional harus dapat dikonfigurasi dan digunakan secara konsisten pada laporan, transaksi, konsumsi bahan baku, dan dataset ANN.

---

## 6. USER AND ACCESS MANAGEMENT

### 6.1 User Roles

| Role | Tanggung Jawab |
|---|---|
| Owner | Pemantauan bisnis dan pengambilan keputusan |
| Admin | Pengelolaan data dan sistem |
| Cashier | Transaksi penjualan |
| Barista | Informasi pesanan dan operasional produksi |

### 6.2 Role-Based Access Control

Sistem menggunakan RBAC.

Ketentuan:

- Setiap pengguna memiliki role.
- Setiap role memiliki permission.
- Permission diperiksa pada backend.
- Hak akses dapat dikonfigurasi.
- Aktivitas penting dicatat dalam audit log.

Pengecekan permission tidak boleh tersebar menggunakan hardcoded nama role di seluruh aplikasi.

---

## 7. PRODUCT MANAGEMENT

### 7.1 Product Data

Setiap produk memiliki informasi:

- Nama produk.
- SKU.
- Kategori.
- Deskripsi.
- Gambar.
- Harga jual.
- Status aktif atau nonaktif.
- Informasi resep atau BOM.

### 7.2 Product Category

Kategori digunakan untuk mengelompokkan produk.

Kategori dapat ditambahkan, diubah, dan dinonaktifkan.

### 7.3 Product Availability

Produk yang tidak tersedia dapat dinonaktifkan sementara tanpa menghapus histori transaksi sebelumnya.

Produk menu dan bahan baku harus dipisahkan secara konseptual maupun dalam pengelolaan data.

---

## 8. POINT OF SALE

### 8.1 POS Functionality

Fitur POS:

- Product search.
- Category filtering.
- Product grid.
- Barcode scanning support.
- Cart management.
- Quantity adjustment.
- Order notes.
- Customer selection.
- Discount and promotion.
- Payment processing.
- Cash payment.
- Payment calculation.
- Change calculation.
- Transaction confirmation.
- Receipt generation.
- Transaction history.
- Transaction detail.
- Cancellation with permission.
- Automatic inventory deduction.

### 8.2 Cart Management

Sistem mendukung beberapa keranjang transaksi.

Setiap keranjang memiliki:

- Cart identifier.
- Product list.
- Quantity.
- Price.
- Subtotal.
- Discount.
- Total.
- Customer.
- Transaction notes.

Keranjang yang belum dibayar tidak mengurangi stok aktual.

### 8.3 Payment Decision

Pembayaran awal menggunakan sistem manual.

Metode pembayaran dapat dicatat sesuai metode yang tersedia pada operasional.

Payment gateway tidak termasuk implementasi awal.

Struktur sistem boleh disiapkan agar integrasi payment gateway dapat ditambahkan kemudian, tanpa membangun kompleksitas yang belum diperlukan.

### 8.4 Transaction Workflow

Select Products  
→ Add to Cart  
→ Review Order  
→ Apply Discount  
→ Select Payment  
→ Confirm Payment  
→ Save Transaction  
→ Deduct Inventory  
→ Generate Receipt

Transaksi dan pengurangan stok harus konsisten menggunakan database transaction.

### 8.5 Transaction Integrity

- Keranjang belum dibayar tidak mengurangi stok.
- Stok dikurangi setelah transaksi berhasil.
- Transaksi gagal tidak menghasilkan pengurangan stok permanen.
- Pembatalan transaksi mengikuti permission.
- Pembatalan harus memiliki histori dan mekanisme pengembalian stok yang sesuai.

---

## 9. INGREDIENT MANAGEMENT

### 9.1 Dynamic Ingredient Data

Daftar bahan baku bersifat dinamis.

Admin dapat:

- Menambah bahan baku.
- Mengubah nama.
- Mengatur satuan.
- Mengatur stok minimum.
- Mengatur lead time.
- Mengatur safety stock.
- Menentukan target prediksi ANN.
- Menonaktifkan bahan baku.

Contoh bahan baku:

- Biji kopi Arabica.
- House Blend Espresso.
- Arabika Gayo.
- Robusta Temanggung.
- Fresh milk.
- Full cream milk.
- Condensed milk (SKM).
- Vanilla syrup.
- Caramel syrup.
- Gula aren cair.
- Green tea powder.

Daftar tersebut hanya contoh awal, bukan daftar permanen.

Nama seperti Americano merupakan produk menu, bukan otomatis bahan baku.

### 9.2 Ingredient Classification

**Core Ingredients**

Bahan baku utama yang menjadi target prediksi ANN.

Contoh:

- Biji kopi.
- Susu.
- Syrup.
- Bubuk minuman.

**Non-Core Inventory**

Barang pendukung yang tetap dikelola inventarisnya tetapi tidak menjadi target utama ANN.

Contoh:

- Cup.
- Sedotan.
- Tissue.
- Perlengkapan penyajian.

Setiap bahan baku memiliki satuan dan parameter inventaris masing-masing.

---

## 10. RECIPE AND BILL OF MATERIALS

### 10.1 BOM Concept

BOM mendefinisikan komposisi bahan baku untuk menghasilkan satu produk.

Hubungan produk dan bahan baku bersifat many-to-many.

### 10.2 Recipe Data

Setiap resep memiliki:

- Produk.
- Bahan baku.
- Kuantitas.
- Satuan.
- Status.
- Riwayat perubahan.

### 10.3 Consumption Calculation

Rumus:

`Konsumsi bahan baku = Jumlah produk terjual × Kuantitas bahan baku per produk`

Contoh:

Produk A terjual 10 unit.

Kebutuhan kopi per produk 18 gram.

Total konsumsi = 180 gram.

Angka hanya ilustrasi.

Perhitungan harus menggunakan versi resep yang berlaku saat transaksi dilakukan.

Perubahan resep tidak boleh mengubah perhitungan historis secara retroaktif.

---

## 11. INVENTORY MANAGEMENT

### 11.1 Inventory Functionality

- Stock overview.
- Stock in.
- Stock out.
- Stock adjustment.
- Stock opname.
- Stock movement history.
- Minimum stock.
- Low stock notification.
- Ingredient unit management.
- Stock reconciliation.
- Expiration date jika diperlukan.
- Stock valuation jika dibutuhkan.

### 11.2 Stock Movement

Setiap pergerakan stok harus mencatat:

- Bahan baku.
- Movement type.
- Quantity.
- Previous stock.
- New stock.
- Reference transaction.
- User.
- Timestamp.
- Description.

### 11.3 Stock Consistency

Perubahan stok harus melalui mekanisme terkontrol.

Histori stok tidak boleh dihapus sembarangan.

---

## 12. SUPPLIER AND PURCHASING

### 12.1 Supplier Management

Informasi pemasok:

- Nama pemasok.
- Informasi kontak.
- Alamat.
- Bahan baku yang disediakan.
- Estimasi lead time.
- Status pemasok.

### 12.2 Purchasing Management

Fitur:

- Pembuatan pembelian.
- Pemilihan pemasok.
- Detail bahan baku.
- Kuantitas.
- Harga pembelian.
- Status pembelian.
- Penerimaan bahan baku.

### 12.3 Procurement Recommendation

Rekomendasi pembelian mempertimbangkan:

- Prediksi kebutuhan.
- Stok aktual.
- Safety stock.
- Reorder point.
- Lead time.

Sistem tidak melakukan pembelian otomatis tanpa persetujuan pengguna berwenang.

---

## 13. WEATHER DATA INTEGRATION

### 13.1 Weather Decision

Provider awal: **Open-Meteo**.

Integrasi harus menggunakan pendekatan adapter agar provider dapat diganti apabila bermasalah atau tidak lagi sesuai kebutuhan.

### 13.2 Weather Parameters

Variabel yang direncanakan:

- Temperature.
- Rainfall.
- Weather condition.

Variabel tambahan hanya digunakan jika tersedia dan memiliki alasan penelitian yang jelas.

### 13.3 Weather Aggregation

Pengumpulan data mengikuti jam operasional:

- Minggu–Kamis: 15.00–23.00.
- Jumat–Sabtu: 15.00–24.00.

Agregasi dilakukan berdasarkan hari operasional.

Contoh:

- Rata-rata temperatur.
- Total curah hujan.
- Kondisi cuaca dominan.

### 13.4 Weather Data Integrity

- Data cuaca harus menyimpan waktu pengamatan.
- Data yang tidak tersedia ditandai sebagai missing.
- Sistem tidak boleh membuat data cuaca palsu.
- Kegagalan API harus dicatat.
- Penggantian provider tidak boleh mengharuskan perubahan besar pada modul ANN.
- API key tidak boleh ditanam langsung dalam source code.

---

## 14. DATASET MANAGEMENT

### 14.1 Dataset Source

Sumber data:

- Riwayat transaksi penjualan.
- Data produk.
- Data resep/BOM.
- Perhitungan konsumsi bahan baku.
- Riwayat inventaris.
- Data cuaca.
- Data historis operasional.

### 14.2 Dataset Generation

Sumber utama pemakaian bahan baku adalah:

`Jumlah Penjualan × Komposisi BOM`

Hasil perhitungan kemudian diagregasi berdasarkan hari operasional dan bahan baku.

Data pemakaian hasil estimasi BOM harus dibedakan dari pengukuran konsumsi fisik aktual apabila keduanya tersedia.

### 14.3 Dataset Duration

Target pengumpulan:

- Minimum awal: 3 bulan.
- Preferensi: 6 bulan.

Enam bulan menjadi target awal yang lebih diutamakan.

Namun, kecukupan dataset harus dinilai berdasarkan jumlah sampel, kelengkapan, variasi, dan kualitas data, bukan hanya jumlah bulan.

### 14.4 Dataset Structure

Setiap baris dataset mewakili satu hari operasional.

Contoh:

| Date | Sales | Temperature | Rainfall | Coffee Usage | Milk Usage |
|---|---:|---:|---:|---:|---:|
| Day 1 | 45 | 29 | 0 | 0.8 kg | 3.2 L |
| Day 2 | 52 | 28 | 2 | 0.9 kg | 3.8 L |
| Day 3 | 38 | 30 | 0 | 0.7 kg | 2.9 L |

Angka hanya ilustrasi.

### 14.5 Data Processing

Tahapan:

1. Data extraction.
2. Data validation.
3. Data cleaning.
4. Missing value handling.
5. Feature engineering.
6. Normalization.
7. Dataset versioning.
8. Training preparation.

Pembagian dataset harus mempertahankan urutan waktu.

Training, validation, dan testing tidak menggunakan random split yang mengabaikan urutan kronologis.

---

## 15. ARTIFICIAL NEURAL NETWORK

### 15.1 ANN Decision

Metode penelitian: Artificial Neural Network.

Framework: **Keras**.

Model awal yang akan dievaluasi: Multilayer Perceptron (MLP).

Target prediksi:

`Predicted Daily Ingredient Demand`

ANN digunakan untuk memprediksi kebutuhan atau konsumsi harian bahan baku inti.

### 15.2 Prediction Input

Variabel awal yang direncanakan:

- Historical sales.
- Historical ingredient consumption.
- Temperature.
- Rainfall.
- Day of week.
- Historical demand patterns.
- Rolling average demand.

Fitur final harus ditentukan berdasarkan analisis dataset dan kebutuhan penelitian.

### 15.3 Initial Architecture

Kandidat arsitektur awal:

INPUT LAYER  
↓  
HIDDEN LAYER 1 — 16 NEURONS  
ReLU  
↓  
HIDDEN LAYER 2 — 8 NEURONS  
ReLU  
↓  
OUTPUT LAYER — 1 NEURON  
Linear  
↓  
PREDICTED DEMAND

Arsitektur tersebut merupakan konfigurasi eksperimen awal, bukan arsitektur final yang telah terbukti optimal.

### 15.4 Initial Training Parameters

| Parameter | Kandidat Awal |
|---|---|
| Framework | Keras |
| Model | MLP |
| Hidden Layers | 2 |
| Neurons | 16 dan 8 |
| Activation | ReLU |
| Output Activation | Linear |
| Optimizer | Adam |
| Learning Rate | 0.001 |
| Loss Function | MSE |
| Batch Size | 16 atau 32 |
| Maximum Epoch | 100 |

Parameter tersebut harus diuji dan dibandingkan berdasarkan hasil evaluasi.

### 15.5 Training Process

Dataset  
↓  
Data Cleaning  
↓  
Feature Engineering  
↓  
Normalization  
↓  
Chronological Split  
↓  
ANN Training  
↓  
Model Evaluation  
↓  
Save Best Model  
↓  
Prediction

### 15.6 Model Evaluation

Evaluasi menggunakan:

- Mean Absolute Error (MAE).
- Root Mean Squared Error (RMSE).

ANN harus dibandingkan dengan baseline sederhana.

Model tidak boleh dinyatakan baik hanya karena berhasil menghasilkan prediksi.

### 15.7 Model Versioning

Informasi yang disimpan:

- Model version.
- Dataset version.
- Training date.
- Input features.
- Hyperparameters.
- Evaluation metrics.
- Model status.

Model baru tidak boleh otomatis menggantikan model yang digunakan apabila performanya belum memenuhi kriteria.

### 15.8 Model Management

Keras digunakan untuk pengembangan dan training model.

Model serta preprocessing artifacts harus dapat disimpan dan dimuat kembali.

Laravel bertanggung jawab menyimpan metadata dan hasil prediksi.

---

## 16. SAFETY STOCK AND REORDER POINT

### 16.1 Safety Stock

Safety Stock merupakan persediaan cadangan untuk mengantisipasi variasi kebutuhan dan keterlambatan pengadaan.

Formula awal:

`σL = σd × √L`

`Safety Stock = Z × σL`

Keterangan:

- SS = Safety Stock.
- Z = Z-score berdasarkan service level.
- σd = Standar deviasi kebutuhan harian.
- L = Lead time dalam hari.
- σL = Standar deviasi kebutuhan selama lead time.

Contoh:

- Rata-rata kebutuhan kopi: 5 kg/hari.
- Standar deviasi: 1,2 kg.
- Lead time: 4 hari.
- Service level: 95%.
- Z-score: 1,65.

Perhitungan:

`σL = 1,2 × √4 = 2,4`

`SS = 1,65 × 2,4 = 3,96 kg`

Safety stock dibulatkan menjadi sekitar 4 kg.

Formula tersebut mengasumsikan kebutuhan harian independen dan lead time tetap. Asumsi harus diperiksa terhadap kondisi aktual.

### 16.2 Reorder Point

Formula awal:

`ROP = Rata-rata kebutuhan harian × Lead Time + Safety Stock`

Perhitungan dapat dikembangkan menggunakan hasil prediksi ANN untuk memperkirakan kebutuhan selama lead time.

### 16.3 Procurement Period

Periode pengadaan ditentukan berdasarkan karakteristik masing-masing bahan baku dan pemasok.

Lead time harus dapat dikonfigurasi.

### 16.4 Stock Recommendation

Dashboard menampilkan:

- Current stock.
- Predicted demand.
- Safety stock.
- Reorder point.
- Estimated stock coverage.
- Recommended purchase quantity.
- Stock status.

Rekomendasi tidak langsung menjadi pembelian otomatis.

---

## 17. REPORTING AND ANALYTICS

Laporan yang direncanakan:

- Daily sales report.
- Weekly sales report.
- Monthly sales report.
- Product performance.
- Best-selling products.
- Transaction history.
- Ingredient consumption.
- Stock movement.
- Inventory valuation.
- Purchasing report.
- Expense report.
- ANN prediction report.
- Model evaluation report.
- Stock recommendation report.

### 17.1 PDF Export

Sistem mendukung pembuatan dokumen PDF untuk:

- Struk transaksi digital.
- Laporan penjualan.
- Laporan persediaan.
- Laporan pembelian.
- Laporan konsumsi bahan baku.
- Laporan prediksi ANN.
- Laporan evaluasi model.
- Laporan rekomendasi pengadaan.

PDF harus memiliki format yang sesuai dengan jenis dokumen.

### 17.2 Report Design

Laporan harus memiliki:

- Judul laporan.
- Identitas bisnis.
- Periode laporan.
- Tanggal pembuatan.
- Informasi ringkasan.
- Tabel data.
- Total atau ringkasan yang relevan.
- Penomoran halaman jika diperlukan.

PDF laporan dan PDF struk tidak harus menggunakan layout yang sama.

---

## 18. PRINTER AND CASH DRAWER INTEGRATION

### 18.1 Printer Decision

Sistem mendukung dua jalur output:

1. Thermal printer untuk struk fisik.
2. PDF untuk struk dan dokumentasi digital.

### 18.2 Thermal Printer

Sistem dirancang agar dapat diintegrasikan dengan thermal printer melalui mekanisme yang sesuai dengan perangkat.

Bluetooth menjadi salah satu opsi konektivitas, tetapi belum dikunci sebagai satu-satunya metode.

Kemungkinan koneksi:

- Bluetooth.
- USB.
- LAN/Wi-Fi.

Pemilihan ditentukan setelah perangkat diketahui.

### 18.3 Cash Drawer

Kondisi operasional yang diketahui:

- Kasir menggunakan tablet.
- Cash drawer saat ini terhubung dengan thermal printer.
- Merek dan tipe printer belum diketahui.
- Model cash drawer belum diketahui.
- Sistem operasi tablet belum diketahui.

Sistem harus mempertahankan kemungkinan penggunaan mekanisme pembukaan cash drawer melalui printer.

Namun, fitur drawer-kick hanya dapat diimplementasikan apabila printer, cash drawer, koneksi, dan protokolnya kompatibel.

### 18.4 Printer Architecture

Printer service harus dipisahkan dari business logic POS.

Kegagalan pencetakan tidak boleh membatalkan transaksi yang sudah berhasil disimpan.

Sistem harus menyediakan alternatif PDF apabila pencetakan gagal.

### 18.5 Unconfirmed Hardware Details

Belum dikunci:

- Merek dan model printer.
- Lebar kertas 58 mm atau 80 mm.
- Sistem operasi tablet.
- Jenis koneksi.
- ESC/POS compatibility.
- Dukungan drawer-kick.
- Protokol cash drawer.

---

## 19. SECURITY AND DATA INTEGRITY

Sistem menerapkan:

- Authentication.
- Role-Based Access Control.
- Server-side authorization.
- Input validation.
- CSRF protection.
- Password hashing.
- Environment configuration untuk secret.
- Database transaction.
- Audit log.
- Pembatasan akses Python service.
- Validasi data prediksi.
- Backup database.
- Recovery procedure.

Data operasional tidak boleh berubah tanpa proses yang dapat ditelusuri.

---

## 20. MODULAR DEVELOPMENT PRINCIPLES

Setiap modul memiliki tanggung jawab yang jelas.

Contoh pengelompokan konseptual:

- POS.
- Products.
- Categories.
- Inventory.
- Recipes.
- Suppliers.
- Purchasing.
- Sales.
- Customers.
- Promotions.
- Expenses.
- Reports.
- Weather.
- Dataset.
- ANN.
- Forecasting.
- Attendance.
- Settings.

Struktur final mengikuti konvensi Laravel.

Tidak wajib menggunakan package modular tambahan.

Prinsip:

1. Batas tanggung jawab modul harus jelas.
2. Ketergantungan antarmodul harus diperhitungkan.
3. Perubahan satu modul tidak boleh merusak modul lain.
4. Hindari abstraksi yang tidak diperlukan.
5. Modul opsional dapat dinonaktifkan apabila tidak menjadi dependensi wajib.

---

## 21. DEVELOPMENT PRINCIPLES

### 21.1 Incremental Development

Pengembangan dilakukan secara bertahap berdasarkan prioritas dan dependensi.

### 21.2 Maintainability

Kode harus mudah dibaca, diuji, dan dikembangkan.

### 21.3 Minimal Necessary Complexity

Hindari overengineering dan dependency tanpa kebutuhan jelas.

### 21.4 Preserve Working Implementation

Sebelum mengubah kode yang sudah berjalan, periksa implementasi aktual dan dampaknya.

Jangan melakukan refactoring besar tanpa alasan teknis.

### 21.5 Evidence-Based Development

Keputusan teknis harus didasarkan pada dokumentasi, data, dan hasil pengujian.

### 21.6 Research Integrity

Data aktual, simulasi, dan estimasi harus dibedakan.

Jangan menyajikan data simulasi sebagai data operasional aktual.

Jangan mengklaim performa model tanpa evaluasi.

### 21.7 UI/UX Quality

Pengembangan antarmuka harus mengikuti prinsip:

- Anti-slop UI.
- Human-centered design.
- Consistent design system.
- Responsive interaction.
- Accessibility.
- Clear visual hierarchy.
- Reusable components.

Skill dan panduan UI/UX yang telah disiapkan harus digunakan sebagai acuan, bukan alasan untuk menambahkan komponen yang tidak diperlukan.

---

## 22. PROJECT DOCUMENTATION

Dokumentasi utama proyek:

| Dokumen | Tanggung Jawab |
|---|---|
| `PROJECT_MASTER.md` | Identitas, arsitektur, gambaran keseluruhan, dan keputusan utama |
| `PROJECT_SPEC.md` | Kebutuhan fungsional, nonfungsional, dan aturan bisnis |
| `DATABASE_DESIGN.md` | ERD, tabel, relasi, constraint, dan rancangan database |
| `ANN_SPECIFICATION.md` | Dataset, preprocessing, ANN, training, dan evaluasi |
| `UI_UX_GUIDELINES.md` | Design system, komponen, layout, dan responsivitas |
| `IMPLEMENTATION_PLAN.md` | Tahapan implementasi dan dependensi pekerjaan |
| `AI_RULES.md` | Aturan wajib AI coding assistant |
| `PROGRESS.md` | Status pekerjaan, keputusan, perubahan, dan langkah berikutnya |

Setiap dokumen memiliki tanggung jawab berbeda.

`PROJECT_MASTER.md` menjadi acuan tingkat tertinggi.

Apabila terdapat perbedaan antar dokumen, perbedaan harus diperiksa dan diselaraskan. AI coding assistant tidak boleh memilih keputusan secara sepihak.

---

## 23. PROJECT SUCCESS CRITERIA

Proyek dianggap memenuhi tujuan pengembangan apabila:

1. Transaksi penjualan dapat dicatat dengan benar.
2. Stok bahan baku diperbarui secara konsisten.
3. Konsumsi bahan baku dihitung berdasarkan BOM.
4. Riwayat pergerakan stok dapat ditelusuri.
5. Dataset dapat dibentuk dari data operasional.
6. ANN dapat dilatih dan dievaluasi menggunakan dataset valid.
7. Hasil prediksi dapat disimpan dan ditampilkan.
8. Rekomendasi pengadaan dapat dihasilkan berdasarkan prediksi dan stok aktual.
9. Hak akses diterapkan dengan benar.
10. Antarmuka dapat digunakan pada desktop, tablet, dan smartphone.
11. Printer thermal dan PDF dapat digunakan sesuai perangkat yang kompatibel.
12. Data dan proses dapat dipertanggungjawabkan secara teknis maupun akademis.

---

# 24. PROJECT DECISION REGISTER

## 24.1 Confirmed Decisions

Keputusan yang sudah disetujui:

| Area | Keputusan |
|---|---|
| Application Type | Web-Based POS and Inventory |
| Project Scope | Full Scope |
| Backend | Laravel |
| ML Service | Python |
| Frontend | Laravel Blade |
| CSS Framework | Tailwind CSS |
| Frontend Interaction | Alpine.js |
| Database | MySQL |
| ANN Framework | Keras |
| Initial ANN Model | MLP |
| Weather Provider | Open-Meteo |
| Weather Architecture | Provider Adapter |
| Payment | Manual |
| Payment Gateway | Tidak termasuk implementasi awal |
| Printer | Thermal Printer + PDF |
| Cash Drawer | Terintegrasi melalui printer jika kompatibel |
| Cashier Device | Tablet |
| Operating Hours | Minggu–Kamis 15.00–23.00 |
| Operating Hours | Jumat–Sabtu 15.00–24.00 |
| Prediction Target | Core Ingredient Demand |
| Main Consumption Source | Sales × BOM |
| Dataset Duration | Target 3–6 bulan |
| UI Direction | Professional, modern, responsive |
| UI Accent | Blue |
| Development Approach | Modular, incremental |

## 24.2 Initial Technical Recommendations

Rancangan awal yang masih harus divalidasi:

| Area | Rekomendasi Awal |
|---|---|
| ANN Architecture | MLP |
| Hidden Layers | 2 |
| Neurons | 16 dan 8 |
| Activation | ReLU |
| Output Activation | Linear |
| Optimizer | Adam |
| Learning Rate | 0.001 |
| Loss | MSE |
| Batch Size | 16 atau 32 |
| Epoch | Maximum 100 |
| Evaluation | MAE dan RMSE |
| Dataset | Preferensi 6 bulan |
| Weather Aggregation | Operational-day aggregation |
| Safety Stock | Z × σd × √L |
| Reorder Point | Average demand × Lead Time + Safety Stock |
| Procurement | Berdasarkan lead time masing-masing bahan baku |

## 24.3 Open Decisions and Validation

Hal yang belum boleh dianggap final:

- Arsitektur ANN terbaik.
- Jumlah neuron dan hidden layer terbaik.
- Fitur input ANN final.
- Strategi single-output atau multi-output.
- Hyperparameter final.
- Strategi retraining.
- Kualitas dataset aktual.
- Penanganan missing weather data.
- Nilai service level dan Z-score.
- Formula final procurement recommendation.
- Detail role permissions.
- Detail aturan pembatalan dan pengembalian transaksi.
- Versi framework dan dependency final.
- Konfigurasi deployment.

## 24.4 Hardware Validation

Menunggu informasi perangkat:

| Item | Status |
|---|---|
| Tablet Model | TBD |
| Tablet Operating System | TBD |
| Thermal Printer Brand | TBD |
| Thermal Printer Model | TBD |
| Printer Connection | TBD |
| Paper Width | TBD |
| Cash Drawer Model | TBD |
| ESC/POS Compatibility | TBD |
| Drawer-kick Compatibility | TBD |

Jangan mengasumsikan semua printer Bluetooth mendukung pencetakan ESC/POS atau pembukaan cash drawer.

## 24.5 Decision Change Management

Setiap perubahan keputusan harus:

1. Menjelaskan alasan perubahan.
2. Menjelaskan dampaknya terhadap sistem.
3. Memperbarui dokumen yang terdampak.
4. Memperbarui `PROGRESS.md`.
5. Mempertahankan keputusan lain yang tidak berkaitan.
6. Mendapat persetujuan sebelum mengganti keputusan yang sudah dikunci.

---

# 25. FINAL PROJECT PRINCIPLE

Seumpama POS dikembangkan sebagai sistem operasional nyata untuk Coffee Shop Seumpama Bunga, bukan hanya demonstrasi aplikasi kasir atau implementasi algoritma ANN.

Prioritas pengembangan:

1. Akurasi pencatatan transaksi.
2. Konsistensi data produk dan resep.
3. Akurasi pencatatan penggunaan bahan baku.
4. Kualitas dataset.
5. Validitas model ANN.
6. Kegunaan rekomendasi stok.
7. Kemudahan penggunaan pada perangkat operasional.
8. Maintainability dan keamanan sistem.

Prinsip utama:

Data transaksi merupakan sumber informasi operasional yang harus dapat dipercaya.

ANN digunakan untuk membantu memperkirakan kebutuhan bahan baku, sedangkan keputusan pengadaan tetap mempertimbangkan stok aktual, safety stock, lead time, dan kondisi operasional.

Sistem harus tetap dapat menjalankan fungsi POS meskipun layanan prediksi atau integrasi eksternal mengalami gangguan.

**END OF PROJECT_MASTER.md**