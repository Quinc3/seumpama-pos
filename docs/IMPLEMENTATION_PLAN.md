# IMPLEMENTATION PLAN
## Seumpama POS

**Project:** Seumpama POS  
**Business:** Coffee Shop Seumpama Bunga  
**Architecture:** Laravel + Python  
**Frontend:** Laravel Blade  
**Primary Database:** Relational Database  
**Machine Learning:** Artificial Neural Network (ANN)  
**Document Status:** Development Planning  
**Version:** 1.0

---

# 1. PURPOSE

Dokumen ini menjelaskan tahapan implementasi Seumpama POS dari persiapan lingkungan pengembangan hingga aplikasi siap digunakan.

Implementation Plan berfungsi sebagai:

- Panduan urutan pengerjaan fitur.
- Acuan pembagian pekerjaan menjadi beberapa fase.
- Pengendali dependensi antarmodul.
- Dasar evaluasi kemajuan pengembangan.
- Acuan pengujian dan kriteria penyelesaian setiap fase.
- Pencegah pengerjaan fitur yang belum memiliki fondasi data dan proses yang memadai.

Pengembangan dilakukan secara bertahap. Setiap fase harus mencapai kriteria penyelesaian sebelum pekerjaan dilanjutkan ke fase berikutnya.

# 2. DEVELOPMENT PRINCIPLES

## 2.1 Incremental Development

Aplikasi dikembangkan secara bertahap berdasarkan prioritas dan dependensi fitur.

Setiap modul harus memiliki fungsi yang dapat diuji sebelum modul berikutnya dibangun di atasnya.

## 2.2 Core Business First

Prioritas pertama adalah memastikan proses operasional utama berjalan dengan benar:

1. Pengelolaan produk dan bahan baku.
2. Pengelolaan resep.
3. Pengelolaan persediaan.
4. Transaksi POS.
5. Pencatatan penggunaan bahan baku.
6. Pelaporan operasional.

Fitur prediksi ANN dikembangkan setelah data operasional tersedia dan tervalidasi.

## 2.3 Data Integrity First

Integritas data merupakan prioritas utama.

Setiap proses yang memengaruhi transaksi, persediaan, dan penggunaan bahan baku harus memiliki aturan konsisten.

Perubahan stok harus dapat ditelusuri melalui riwayat pergerakan persediaan.

## 2.4 Test Before Progress

Setiap modul wajib melalui pengujian sebelum dinyatakan selesai.

Pengujian tidak hanya memastikan halaman dapat dibuka, tetapi juga memeriksa aturan bisnis, validasi, relasi data, hak akses, dan kondisi kegagalan.

## 2.5 Minimal and Maintainable Implementation

Implementasi harus mengikuti konvensi Laravel dan Python yang sesuai.

Hindari:

- Abstraksi yang tidak diperlukan.
- Dependency tambahan tanpa alasan teknis.
- Duplikasi business logic.
- Perubahan struktur proyek tanpa kebutuhan.
- Pembuatan fitur di luar scope yang sudah ditetapkan.
- Refactoring besar ketika perbaikan kecil sudah mencukupi.

## 2.6 Evidence-Based Development

Setiap perubahan harus berdasarkan kebutuhan yang jelas.

AI coding assistant wajib memeriksa implementasi yang sudah ada sebelum mengubah file atau membuat struktur baru.

Jangan mengasumsikan kode belum tersedia hanya karena belum terlihat dalam percakapan.

---

# 3. DEVELOPMENT PHASES

Pengembangan dibagi menjadi 12 fase utama.

| Phase | Fokus | Prioritas |
|---|---|---|
| 0 | Project Preparation | Critical |
| 1 | Application Foundation | Critical |
| 2 | Master Data Management | Critical |
| 3 | Recipe and Inventory System | Critical |
| 4 | Point of Sale | Critical |
| 5 | Operational Reports | High |
| 6 | Weather and Dataset Preparation | High |
| 7 | Python ANN Integration | High |
| 8 | Forecasting and Stock Recommendation | High |
| 9 | User Interface Refinement | Medium |
| 10 | Testing and Validation | Critical |
| 11 | Deployment and Documentation | Critical |

Fase dapat memiliki pekerjaan yang berjalan paralel apabila dependensinya tidak saling mengganggu.

---

# 4. PHASE 0 — PROJECT PREPARATION

## Objective

Mempersiapkan seluruh kebutuhan pengembangan sebelum implementasi fitur dimulai.

## Tasks

### 4.1 Documentation Preparation

- Menyelesaikan dokumen perencanaan proyek.
- Memastikan keputusan arsitektur terdokumentasi.
- Memastikan kebutuhan fungsional dan aturan bisnis sudah didefinisikan.
- Menyusun rancangan database.
- Menyusun spesifikasi ANN.
- Menyusun pedoman UI/UX.
- Menyusun aturan kerja AI coding assistant.

### 4.2 Development Environment

Menyiapkan:

- PHP dan Composer.
- Laravel.
- Node.js dan NPM.
- Database server.
- Python.
- Python virtual environment.
- Git.
- Code editor.
- Browser untuk pengujian.

Versi teknologi harus disesuaikan dengan kompatibilitas proyek dan lingkungan deployment.

### 4.3 Project Repository

Menyiapkan repository Git dengan struktur dasar:

- Source code Laravel.
- Source code Python.
- Dokumentasi.
- File konfigurasi contoh.
- Testing.
- File dependency.

File rahasia seperti `.env`, API key, dan kredensial database tidak boleh dimasukkan ke repository.

## Deliverables

- Lingkungan pengembangan siap.
- Repository tersedia.
- Seluruh dokumen perencanaan tersedia.
- Struktur proyek awal ditetapkan.

## Acceptance Criteria

- Laravel dapat dijalankan.
- Python environment dapat dijalankan.
- Laravel dapat terhubung dengan database.
- Git dapat digunakan.
- Dokumentasi utama sudah tersedia.

---

# 5. PHASE 1 — APPLICATION FOUNDATION

## Objective

Membangun fondasi aplikasi Laravel, autentikasi, otorisasi, struktur layout, dan komunikasi awal dengan Python.

## Tasks

### 5.1 Laravel Application Setup

- Membuat atau menyiapkan aplikasi Laravel.
- Mengatur environment.
- Mengatur koneksi database.
- Mengatur konfigurasi aplikasi.
- Menyiapkan struktur folder dan namespace.
- Menyiapkan sistem logging dan error handling.

### 5.2 Authentication

Mengimplementasikan:

- Login.
- Logout.
- Session management.
- Password hashing.
- Validasi kredensial.
- Proteksi halaman yang memerlukan autentikasi.

### 5.3 Authorization

Mengimplementasikan sistem role dan permission.

Role awal:

- Owner.
- Admin.
- Cashier.
- Barista.

Hak akses diterapkan berdasarkan permission, bukan pemeriksaan nama role yang tersebar di seluruh kode.

### 5.4 Application Layout

Membuat kerangka antarmuka:

- Sidebar navigation.
- Header.
- Main content.
- Breadcrumb.
- Notification.
- User profile menu.
- Responsive navigation.
- Reusable Blade components.

### 5.5 Python Service Foundation

Menyiapkan struktur service Python secara terpisah dari aplikasi Laravel.

Pada tahap ini hanya dilakukan:

- Pembuatan Python project.
- Virtual environment.
- Dependency management.
- Konfigurasi service.
- Endpoint health check.
- Struktur logging dan error handling.

Belum perlu mengimplementasikan model ANN.

### 5.6 Laravel-Python Communication

Menyiapkan komunikasi antara Laravel dan Python melalui HTTP API.

Aturan awal:

- Laravel tetap menjadi aplikasi utama.
- Python bertanggung jawab atas proses machine learning.
- Request dan response menggunakan format JSON.
- Endpoint Python tidak boleh terbuka tanpa pembatasan.
- Kegagalan Python tidak boleh menghentikan fungsi POS.

## Deliverables

- Laravel application foundation.
- Authentication.
- Authorization.
- Dashboard layout.
- Python service skeleton.
- Komunikasi dasar Laravel-Python.

## Acceptance Criteria

- Pengguna dapat login dan logout.
- Halaman terlindungi dari akses tanpa autentikasi.
- Permission dapat membatasi akses.
- Layout dapat digunakan pada desktop dan mobile.
- Laravel dapat mengakses health check Python.
- Kegagalan Python tidak mengganggu transaksi POS.

---

# 6. PHASE 2 — MASTER DATA MANAGEMENT

## Objective

Membangun pengelolaan seluruh data dasar yang dibutuhkan modul operasional.

## Tasks

### 6.1 User Management

- Membuat pengguna.
- Mengubah data pengguna.
- Mengaktifkan dan menonaktifkan pengguna.
- Mengatur role dan permission.
- Mengelola kredensial pengguna.

### 6.2 Product Management

- CRUD produk.
- Kategori produk.
- Nama dan deskripsi produk.
- Harga jual.
- Status ketersediaan.
- Foto produk.
- Pengelompokan menu.

### 6.3 Ingredient Management

- CRUD bahan baku.
- Nama bahan baku.
- Kategori bahan baku.
- Satuan dasar.
- Status aktif.
- Klasifikasi core dan non-core ingredient.
- Minimum stock.
- Lead time pengadaan.
- Parameter persediaan.

### 6.4 Unit Management

- Pengelolaan satuan.
- Satuan dasar.
- Satuan pembelian.
- Konversi satuan.
- Validasi konsistensi satuan.

Contoh satuan:

- Gram.
- Kilogram.
- Milliliter.
- Liter.
- Piece.

Konversi hanya boleh dilakukan apabila hubungan antarunit memang valid.

### 6.5 Supplier Management

- CRUD supplier.
- Informasi kontak.
- Alamat.
- Status supplier.
- Riwayat hubungan pengadaan.

### 6.6 Customer Management

- CRUD pelanggan.
- Nama.
- Kontak.
- Riwayat transaksi.
- Status pelanggan.

### 6.7 Category and Supporting Data

- Kategori produk.
- Kategori bahan baku.
- Kategori pengeluaran.
- Data pendukung lain yang diperlukan.

## Deliverables

- User management.
- Product management.
- Ingredient management.
- Unit management.
- Supplier management.
- Customer management.
- Supporting master data.

## Acceptance Criteria

- Seluruh data master dapat dikelola.
- Validasi input berjalan.
- Relasi data konsisten.
- Data yang sudah digunakan dalam transaksi tidak dapat dihapus secara sembarangan.
- Status aktif/nonaktif dapat digunakan untuk mengendalikan penggunaan data.

---

# 7. PHASE 3 — RECIPE AND INVENTORY SYSTEM

## Objective

Membangun sistem resep dan persediaan sebagai fondasi pencatatan penggunaan bahan baku.

## Tasks

### 7.1 Recipe Management

Mengimplementasikan Bill of Materials (BOM).

Fitur:

- Menghubungkan produk dengan bahan baku.
- Mengatur kuantitas bahan baku per produk.
- Mengatur satuan resep.
- Mengubah resep.
- Menyimpan riwayat perubahan resep.
- Mengelola status resep.

Satu produk dapat menggunakan banyak bahan baku.

Satu bahan baku dapat digunakan oleh banyak produk.

### 7.2 Recipe Versioning

Perubahan resep harus memiliki informasi waktu berlakunya.

Tujuannya agar perhitungan penggunaan bahan baku untuk transaksi historis tidak otomatis menggunakan resep terbaru.

### 7.3 Inventory Management

Mengimplementasikan:

- Stok awal.
- Stok masuk.
- Stok keluar.
- Penyesuaian stok.
- Stock opname.
- Minimum stock.
- Riwayat pergerakan stok.
- Low stock notification.
- Status persediaan.

### 7.4 Stock Movement

Setiap perubahan stok harus mencatat:

- Bahan baku.
- Jenis pergerakan.
- Kuantitas.
- Stok sebelum perubahan.
- Stok sesudah perubahan.
- Referensi transaksi atau dokumen.
- Pengguna yang melakukan perubahan.
- Waktu perubahan.
- Alasan perubahan jika diperlukan.

### 7.5 Purchasing and Procurement

Mengimplementasikan:

- Purchase order.
- Pemilihan supplier.
- Item pembelian.
- Kuantitas pembelian.
- Harga pembelian.
- Status pembelian.
- Penerimaan barang.
- Riwayat pembelian.

Stok bertambah ketika barang benar-benar diterima, bukan hanya ketika purchase order dibuat.

### 7.6 Inventory Reconciliation

Membangun mekanisme untuk membandingkan stok sistem dengan stok fisik.

Selisih harus dicatat melalui proses penyesuaian dengan alasan dan identitas pengguna.

### 7.7 Inventory Valuation

Mengimplementasikan pencatatan nilai persediaan apabila metode penilaian dan kebutuhan laporan sudah ditentukan.

**OPEN / perlu validasi:** Metode penilaian persediaan dan perlakuan biaya pembelian.

## Deliverables

- Recipe/BOM management.
- Recipe versioning.
- Inventory management.
- Stock movement.
- Purchasing.
- Goods receiving.
- Stock opname.
- Inventory reconciliation.

## Acceptance Criteria

- Produk dapat memiliki resep.
- Penggunaan bahan baku dapat dihitung berdasarkan resep.
- Perubahan resep tidak merusak perhitungan historis.
- Semua perubahan stok memiliki catatan pergerakan.
- Penerimaan barang menambah stok dengan benar.
- Stock opname dapat mencatat selisih.
- Konversi satuan menghasilkan kuantitas yang benar.

---

# 8. PHASE 4 — POINT OF SALE

## Objective

Membangun sistem transaksi penjualan sebagai fungsi utama operasional coffee shop.

## Tasks

### 8.1 POS Interface

Membuat antarmuka kasir dengan:

- Product catalog.
- Product search.
- Category filter.
- Shopping cart.
- Quantity control.
- Product notes.
- Customer selection.
- Discount.
- Payment summary.
- Checkout.

### 8.2 Cart Management

- Menambah produk.
- Mengubah kuantitas.
- Menghapus produk.
- Mengubah catatan.
- Menghitung subtotal.
- Menghitung diskon.
- Menghitung total pembayaran.
- Mengelola beberapa cart apabila kebutuhan operasional mengharuskannya.

### 8.3 Transaction Processing

Mengimplementasikan:

- Nomor transaksi.
- Detail transaksi.
- Waktu transaksi.
- Kasir.
- Produk yang dibeli.
- Kuantitas.
- Harga saat transaksi.
- Diskon.
- Total pembayaran.
- Metode pembayaran.
- Status transaksi.

### 8.4 Payment Processing

Mendukung metode pembayaran yang telah disepakati.

Untuk pembayaran tunai:

- Input nominal pembayaran.
- Validasi kecukupan pembayaran.
- Perhitungan uang kembalian.

**OPEN / perlu validasi:** Daftar metode pembayaran dan apakah pembayaran non-tunai hanya dicatat manual atau diintegrasikan dengan payment gateway.

### 8.5 Stock Deduction

Stok bahan baku hanya dikurangi setelah transaksi berhasil dibayar dan disimpan.

Proses berikut harus dilakukan secara konsisten dalam database transaction:

1. Validasi transaksi.
2. Simpan transaksi.
3. Simpan detail transaksi.
4. Hitung penggunaan bahan baku berdasarkan resep yang berlaku.
5. Catat stock movement.
6. Perbarui stok.
7. Selesaikan transaksi.

Jika salah satu proses penting gagal, perubahan harus di-rollback agar transaksi dan stok tidak berada dalam kondisi tidak konsisten.

### 8.6 Receipt

- Menampilkan ringkasan transaksi.
- Mencetak struk.
- Menampilkan nomor transaksi.
- Menampilkan detail produk.
- Menampilkan pembayaran.
- Menampilkan uang kembalian apabila relevan.

### 8.7 Transaction History

- Riwayat transaksi.
- Detail transaksi.
- Filter tanggal.
- Filter kasir.
- Filter status.
- Pencarian nomor transaksi.

### 8.8 Transaction Cancellation

Pembatalan transaksi harus memiliki otorisasi.

Aturan pembatalan wajib memperhitungkan transaksi yang stoknya sudah dikurangi.

Pengembalian stok dilakukan melalui stock movement pembalik yang dapat ditelusuri, bukan menghapus riwayat transaksi.

## Deliverables

- POS interface.
- Cart management.
- Transaction processing.
- Payment processing.
- Stock deduction.
- Receipt.
- Transaction history.
- Transaction cancellation.

## Acceptance Criteria

- Kasir dapat membuat transaksi.
- Total pembayaran dihitung dengan benar.
- Transaksi berhasil menghasilkan catatan penjualan.
- Stok bahan baku berkurang sesuai resep.
- Transaksi gagal tidak menyebabkan stok berkurang permanen.
- Pembatalan transaksi menghasilkan riwayat yang dapat ditelusuri.
- Struk dapat ditampilkan dan dicetak.
- Transaksi tetap dapat dilakukan tanpa Python service.

---

# 9. PHASE 5 — OPERATIONAL REPORTS

## Objective

Menyediakan laporan operasional sebagai sumber informasi bisnis dan fondasi analisis data.

## Tasks

### 9.1 Sales Reports

- Laporan penjualan harian.
- Laporan penjualan mingguan.
- Laporan penjualan bulanan.
- Total transaksi.
- Total pendapatan.
- Rata-rata nilai transaksi.
- Penjualan berdasarkan produk.
- Penjualan berdasarkan kategori.

### 9.2 Product Performance

- Produk terlaris.
- Produk dengan penjualan terendah.
- Jumlah produk terjual.
- Performa produk berdasarkan periode.

### 9.3 Inventory Reports

- Stok bahan baku.
- Stok minimum.
- Riwayat stok masuk.
- Riwayat stok keluar.
- Penggunaan bahan baku.
- Selisih stock opname.

### 9.4 Purchasing Reports

- Riwayat pembelian.
- Pembelian berdasarkan supplier.
- Kuantitas pembelian.
- Nilai pembelian.
- Riwayat penerimaan barang.

### 9.5 Expense Reports

- Pencatatan pengeluaran.
- Kategori pengeluaran.
- Laporan pengeluaran berdasarkan periode.

### 9.6 Operational Day Handling

Semua laporan harus mengikuti aturan operational day yang sudah ditentukan.

Pengelompokan transaksi tidak boleh hanya bergantung pada tanggal kalender apabila jam operasional melewati tengah malam.

## Deliverables

- Sales reports.
- Product performance reports.
- Inventory reports.
- Purchasing reports.
- Expense reports.
- Dashboard analytics.

## Acceptance Criteria

- Data laporan sesuai dengan transaksi aktual.
- Filter periode bekerja.
- Pengelompokan operational day konsisten.
- Total laporan dapat diverifikasi terhadap data sumber.
- Transaksi yang dibatalkan tidak dihitung sebagai penjualan berhasil.

---

# 10. PHASE 6 — WEATHER AND DATASET PREPARATION

## Objective

Membangun sistem pengumpulan dan pengolahan dataset yang diperlukan untuk eksperimen ANN.

## Tasks

### 10.1 Weather Integration

Mengimplementasikan integrasi penyedia data cuaca.

Data yang berpotensi digunakan:

- Temperature.
- Rainfall.
- Weather condition.

Integrasi menggunakan adapter/provider layer agar pergantian penyedia tidak memerlukan perubahan pada keseluruhan sistem.

### 10.2 Weather Data Storage

- Menyimpan data cuaca.
- Menyimpan waktu pengamatan.
- Menghubungkan data dengan operational day.
- Mencatat sumber data.
- Menangani data yang tidak tersedia.
- Menghindari duplikasi data.

### 10.3 Historical Data Preparation

Mengumpulkan data historis dari:

- Transaksi penjualan.
- Detail transaksi.
- Recipe/BOM.
- Riwayat perubahan resep.
- Penggunaan bahan baku.
- Data cuaca.
- Kalender operasional.

### 10.4 Dataset Generation

Membangun proses ekstraksi dan transformasi data.

Proses:

1. Mengambil data transaksi.
2. Mengelompokkan transaksi berdasarkan operational day.
3. Menghitung jumlah produk terjual.
4. Menghitung estimasi penggunaan bahan baku berdasarkan resep yang berlaku saat transaksi.
5. Menggabungkan data cuaca.
6. Menghasilkan data harian.
7. Memvalidasi data.
8. Menangani missing values.
9. Melakukan feature engineering.
10. Menyimpan dataset hasil pengolahan.

### 10.5 Data Validation

Validasi meliputi:

- Data duplikat.
- Data kosong.
- Nilai negatif yang tidak valid.
- Ketidaksesuaian satuan.
- Perubahan resep.
- Hari tanpa transaksi.
- Data cuaca tidak tersedia.
- Anomali penggunaan bahan baku.

Hari tanpa transaksi harus dibedakan dari data yang hilang.

### 10.6 Dataset Versioning

Setiap dataset hasil pengolahan harus memiliki identitas versi dan informasi sumber data.

Hal ini diperlukan agar eksperimen ANN dapat direproduksi.

### 10.7 Data Sufficiency Assessment

Mengevaluasi kelayakan data historis.

Target awal pengumpulan data adalah 3–6 bulan, dengan 6 bulan sebagai target yang diutamakan apabila tersedia.

Jumlah data tidak otomatis menjamin model layak digunakan. Kualitas, variasi, kelengkapan, dan pola temporal harus ikut diperiksa.

## Deliverables

- Weather integration.
- Weather data storage.
- Historical data extraction.
- Dataset generation pipeline.
- Data validation.
- Dataset versioning.
- Data sufficiency report.

## Acceptance Criteria

- Dataset dapat dibentuk dari data operasional.
- Perhitungan penggunaan bahan baku dapat ditelusuri.
- Data historis menggunakan resep yang sesuai dengan periode berlakunya.
- Missing values ditangani secara eksplisit.
- Data cuaca yang tidak tersedia tidak dibuat-buat.
- Dataset memiliki versi dan sumber yang jelas.
- Kelayakan data untuk ANN telah dievaluasi.

---

# 11. PHASE 7 — PYTHON ANN INTEGRATION

## Objective

Mengembangkan model Artificial Neural Network untuk memprediksi kebutuhan bahan baku berdasarkan dataset yang telah divalidasi.

## Tasks

### 11.1 ANN Research Implementation

Mengimplementasikan spesifikasi ANN berdasarkan `ANN_SPECIFICATION.md`.

Model awal menggunakan pendekatan Multilayer Perceptron (MLP).

Konfigurasi awal merupakan baseline eksperimen, bukan parameter final.

### 11.2 Data Preprocessing

- Memilih fitur.
- Menangani missing values.
- Normalisasi data.
- Membentuk input sequence atau fitur lag.
- Memisahkan dataset secara kronologis.
- Menyiapkan training, validation, dan testing data.

Pembagian data tidak boleh menggunakan random shuffle yang menghilangkan urutan waktu.

### 11.3 Model Training

- Training ANN.
- Validation.
- Hyperparameter tuning.
- Penyimpanan model.
- Pencatatan konfigurasi training.
- Pencatatan hasil eksperimen.

### 11.4 Model Evaluation

Mengukur performa menggunakan:

- MAE.
- RMSE.

Hasil ANN juga perlu dibandingkan dengan baseline sederhana agar manfaat model dapat dievaluasi secara objektif.

### 11.5 Model Management

- Model versioning.
- Model metadata.
- Dataset version reference.
- Training history.
- Evaluation results.
- Model activation status.

Model baru tidak boleh langsung menggantikan model aktif sebelum hasil evaluasinya diperiksa.

### 11.6 Prediction API

Membuat endpoint Python untuk:

- Health check.
- Model information.
- Prediction.
- Prediction validation.
- Error response.

### 11.7 Laravel Integration

Laravel mengirimkan data yang diperlukan ke Python dan menerima hasil prediksi.

Laravel bertanggung jawab untuk:

- Menyediakan data input.
- Menyimpan hasil prediksi.
- Menampilkan hasil kepada pengguna.
- Mengelola histori prediksi.
- Menghubungkan hasil prediksi dengan data stok.

Python bertanggung jawab untuk:

- Preprocessing sesuai model.
- Memuat model.
- Menjalankan prediksi.
- Mengembalikan hasil dan metadata model.

## Deliverables

- ANN training pipeline.
- Model evaluation.
- Model versioning.
- Prediction API.
- Laravel-Python integration.
- Prediction history.

## Acceptance Criteria

- Model dapat dilatih menggunakan dataset tervalidasi.
- Proses evaluasi dapat diulang.
- Hasil prediksi memiliki referensi versi model dan dataset.
- Model menghasilkan nilai prediksi yang valid.
- Laravel dapat menerima dan menyimpan hasil prediksi.
- Kegagalan model tidak mengganggu transaksi POS.
- Hasil model dibandingkan dengan baseline.

---

# 12. PHASE 8 — FORECASTING AND STOCK RECOMMENDATION

## Objective

Menghubungkan hasil prediksi ANN dengan kondisi persediaan aktual untuk menghasilkan rekomendasi pengadaan bahan baku.

## Tasks

### 12.1 Demand Forecasting

- Menampilkan prediksi kebutuhan bahan baku.
- Memilih bahan baku yang akan diprediksi.
- Menentukan periode prediksi.
- Menampilkan histori prediksi.
- Menyimpan waktu dan versi model.

### 12.2 Safety Stock Calculation

Mengimplementasikan perhitungan safety stock sesuai metode yang ditentukan dalam spesifikasi.

Untuk asumsi lead time tetap dan variasi permintaan harian yang independen:

\[
\sigma_L = \sigma_d \sqrt{L}
\]

\[
SS = Z \times \sigma_L
\]

Keterangan:

- \(SS\): Safety stock.
- \(Z\): Z-score berdasarkan target service level.
- \(\sigma_d\): Standar deviasi permintaan harian.
- \(L\): Lead time dalam hari.

Formula harus dievaluasi kembali apabila lead time supplier bervariasi atau asumsi statistik tidak terpenuhi.

### 12.3 Reorder Point

Menghitung Reorder Point:

\[
ROP = \bar{d} \times L + SS
\]

Keterangan:

- \(ROP\): Reorder point.
- \(\bar{d}\): Rata-rata permintaan harian.
- \(L\): Lead time.
- \(SS\): Safety stock.

### 12.4 Stock Coverage

Menghitung estimasi berapa lama stok tersedia berdasarkan kebutuhan yang diprediksi.

### 12.5 Purchase Recommendation

Menghasilkan rekomendasi:

- Bahan baku yang perlu dibeli.
- Stok saat ini.
- Prediksi kebutuhan.
- Safety stock.
- Reorder point.
- Estimasi kekurangan.
- Rekomendasi jumlah pembelian.

### 12.6 Recommendation Review

Pengguna yang memiliki permission dapat meninjau rekomendasi.

Sistem hanya memberikan rekomendasi. Purchase order tidak dibuat atau dikirim otomatis tanpa tindakan dan persetujuan pengguna.

## Deliverables

- Demand forecasting.
- Safety stock calculation.
- Reorder point calculation.
- Stock coverage.
- Purchase recommendation.
- Forecasting dashboard.

## Acceptance Criteria

- Prediksi dapat ditampilkan berdasarkan bahan baku.
- Perhitungan safety stock dan ROP dapat diverifikasi.
- Parameter perhitungan dapat disesuaikan.
- Rekomendasi mempertimbangkan stok aktual dan lead time.
- Rekomendasi dapat ditelusuri ke hasil prediksi.
- Tidak ada pembelian otomatis tanpa persetujuan pengguna.

---

# 13. PHASE 9 — USER INTERFACE REFINEMENT

## Objective

Menyempurnakan pengalaman pengguna dan konsistensi antarmuka seluruh aplikasi.

## Tasks

### 13.1 Design Consistency

- Konsistensi warna.
- Blue accent color.
- Typography.
- Spacing.
- Border radius.
- Shadows.
- Icons.
- Form controls.
- Tables.
- Modal.
- Notifications.

### 13.2 Responsive Refinement

Memastikan pengalaman yang sesuai untuk:

- Desktop.
- Tablet.
- Mobile.

Mobile tidak boleh hanya menjadi versi desktop yang diperkecil.

### 13.3 POS UX Refinement

Mengoptimalkan:

- Kecepatan input produk.
- Navigasi kategori.
- Pengelolaan cart.
- Checkout.
- Payment flow.
- Receipt.
- Touch interaction.

### 13.4 Accessibility and Usability

- Kontras teks.
- Ukuran elemen interaktif.
- Keyboard navigation.
- Label form.
- Feedback validasi.
- Loading state.
- Empty state.
- Error state.

## Deliverables

- Konsistensi UI.
- Responsive refinement.
- POS UX refinement.
- Reusable interface components.

## Acceptance Criteria

- Seluruh modul memiliki tampilan konsisten.
- Tampilan dapat digunakan pada perangkat target.
- Interaksi utama dapat digunakan dengan mouse, keyboard, dan touch sesuai kebutuhan.
- Loading, empty, success, dan error state tersedia.
- Tidak ada masalah UI yang menghambat proses operasional.

---

# 14. PHASE 10 — TESTING AND VALIDATION

## Objective

Memastikan seluruh fungsi aplikasi berjalan sesuai kebutuhan dan aturan bisnis.

## Tasks

### 14.1 Unit Testing

Menguji fungsi secara terisolasi.

Contoh:

- Perhitungan subtotal.
- Perhitungan diskon.
- Konversi satuan.
- Perhitungan penggunaan bahan baku.
- Perhitungan safety stock.
- Perhitungan ROP.

### 14.2 Feature Testing

Menguji alur aplikasi secara menyeluruh.

Contoh:

- Login.
- Pengelolaan produk.
- Pengelolaan bahan baku.
- Pengelolaan resep.
- Pembelian.
- Penerimaan barang.
- Transaksi POS.
- Pembatalan transaksi.
- Laporan.

### 14.3 Integration Testing

Menguji komunikasi dan integrasi:

- Laravel dengan database.
- Laravel dengan Python.
- Laravel dengan weather provider.
- Transaksi dengan inventory.
- Transaksi dengan recipe/BOM.
- Forecasting dengan inventory.

### 14.4 Security Testing

- Authentication.
- Authorization.
- Input validation.
- CSRF protection.
- SQL injection prevention.
- XSS prevention.
- Secret management.
- Endpoint protection.
- Audit trail.

### 14.5 Data Integrity Testing

Memastikan:

- Tidak ada stok negatif yang tidak diizinkan.
- Transaksi tidak menyebabkan pengurangan stok ganda.
- Pembatalan menghasilkan stock movement pembalik.
- Penerimaan barang tidak tercatat dua kali.
- Data historis tetap konsisten.
- Penggunaan resep historis dapat ditelusuri.

### 14.6 ANN Validation

- Validasi dataset.
- Evaluasi MAE.
- Evaluasi RMSE.
- Perbandingan baseline.
- Pemeriksaan hasil prediksi.
- Pemeriksaan model version.
- Pemeriksaan stabilitas hasil.

### 14.7 User Acceptance Testing (UAT)

Melakukan pengujian berdasarkan alur operasional Coffee Shop Seumpama Bunga.

Pengguna yang relevan melakukan pengujian terhadap fungsi sesuai perannya.

## Deliverables

- Unit test results.
- Feature test results.
- Integration test results.
- Security test results.
- Data integrity test results.
- ANN evaluation report.
- UAT results.
- Bug and issue list.

## Acceptance Criteria

- Pengujian kritis berhasil.
- Tidak ada bug kritis yang belum diselesaikan.
- Transaksi dan stok konsisten.
- Hak akses berjalan sesuai ketentuan.
- Integrasi Python dapat menangani kegagalan.
- Hasil ANN telah dievaluasi.
- UAT disetujui oleh pihak terkait.

---

# 15. PHASE 11 — DEPLOYMENT AND DOCUMENTATION

## Objective

Mempersiapkan aplikasi untuk digunakan pada lingkungan operasional dan menyelesaikan dokumentasi teknis.

## Tasks

### 15.1 Production Environment

Menyiapkan:

- Server.
- PHP runtime.
- Web server.
- Database.
- Python runtime.
- Python dependencies.
- Environment variables.
- Storage.
- Queue worker apabila diperlukan.
- Scheduler apabila diperlukan.

### 15.2 Deployment

- Menyiapkan konfigurasi production.
- Menjalankan database migration.
- Membangun frontend assets.
- Mengatur permission storage.
- Mengatur service Python.
- Mengatur HTTPS.
- Mengatur domain.
- Mengaktifkan logging.

### 15.3 Backup and Recovery

- Database backup.
- File backup.
- Backup model ANN.
- Prosedur restore.
- Pengujian pemulihan data.

### 15.4 Monitoring

- Application logs.
- Python service logs.
- Error monitoring.
- Database monitoring.
- Queue monitoring apabila digunakan.
- Monitoring kegagalan integrasi.

### 15.5 Technical Documentation

Melengkapi:

- Installation guide.
- Environment configuration.
- Database documentation.
- API documentation.
- Deployment guide.
- Backup and recovery guide.
- Troubleshooting guide.

### 15.6 User Documentation

Menyusun panduan:

- Login dan pengelolaan akun.
- Pengelolaan produk.
- Pengelolaan bahan baku.
- Pengelolaan resep.
- Pengelolaan persediaan.
- Transaksi POS.
- Laporan.
- Forecasting.
- Rekomendasi pembelian.

## Deliverables

- Production-ready application.
- Deployment configuration.
- Backup and recovery procedure.
- Monitoring setup.
- Technical documentation.
- User manual.

## Acceptance Criteria

- Aplikasi dapat berjalan di lingkungan production.
- Database production dapat digunakan.
- Laravel dan Python service berjalan.
- Backup dapat dibuat dan dipulihkan.
- HTTPS aktif.
- Dokumentasi instalasi tersedia.
- Pengguna dapat menjalankan proses operasional utama.

---

# 16. FEATURE DEPENDENCY

Urutan dependensi utama:

```text
Project Preparation
        |
        v
Application Foundation
        |
        v
Master Data Management
        |
        v
Recipe and Inventory
        |
        v
Point of Sale
        |
        v
Operational Reports
        |
        v
Weather and Dataset Preparation
        |
        v
Python ANN Integration
        |
        v
Forecasting and Stock Recommendation
        |
        v
Testing and Validation
        |
        v
Deployment
```

UI refinement dapat dilakukan secara bertahap selama pengembangan, tidak harus menunggu seluruh fitur selesai.

Testing juga dilakukan pada setiap fase, bukan hanya pada fase testing akhir.

---

# 17. CRITICAL DEVELOPMENT RULES

Aturan berikut berlaku sepanjang implementasi.

## 17.1 Transaction Integrity

Transaksi penjualan dan perubahan stok harus konsisten.

Jangan memisahkan proses yang seharusnya atomik sehingga kegagalan sebagian proses meninggalkan data tidak valid.

## 17.2 Historical Data Integrity

Data historis tidak boleh berubah secara tidak sengaja akibat perubahan data master atau resep.

Gunakan snapshot atau mekanisme versioning yang sesuai.

## 17.3 ANN Is Not a POS Dependency

POS harus tetap berjalan meskipun Python service, weather provider, atau model ANN sedang tidak tersedia.

## 17.4 No Fake Dataset

Jangan membuat data cuaca, transaksi, atau penggunaan bahan baku palsu lalu memperlakukannya sebagai data operasional aktual.

Data simulasi hanya boleh digunakan untuk testing dan harus diberi label yang jelas.

## 17.5 No Automatic Purchasing

Hasil prediksi dan rekomendasi pembelian tidak boleh otomatis menghasilkan pembelian tanpa persetujuan pengguna.

## 17.6 No Premature Optimization

Optimasi dilakukan berdasarkan hasil pengujian atau kebutuhan yang terukur, bukan asumsi.

## 17.7 Preserve Existing Implementation

Sebelum mengubah file:

1. Periksa implementasi yang sudah tersedia.
2. Identifikasi dependensi.
3. Tentukan perubahan minimum.
4. Implementasikan.
5. Jalankan pengujian.
6. Catat perubahan.

Jangan mengganti file atau struktur yang sudah bekerja tanpa alasan yang jelas.

---

# 18. DEVELOPMENT WORKFLOW

Setiap pekerjaan mengikuti siklus berikut:

```text
Read Documentation
        |
        v
Inspect Existing Implementation
        |
        v
Identify Current Task
        |
        v
Check Dependencies
        |
        v
Plan Minimal Changes
        |
        v
Implement
        |
        v
Run Tests
        |
        v
Review Results
        |
        v
Update Documentation
        |
        v
Update Progress
```

## 18.1 Task Scope

Setiap task harus memiliki:

- Tujuan.
- File atau modul yang relevan.
- Dependensi.
- Output yang diharapkan.
- Acceptance criteria.

## 18.2 Change Management

Perubahan terhadap keputusan yang sudah dikunci harus:

- Memiliki alasan.
- Mengidentifikasi dampak.
- Memperbarui dokumen terkait.
- Tidak dilakukan secara diam-diam.

## 18.3 AI Coding Assistant Workflow

AI coding assistant wajib:

- Membaca `AI_RULES.md`.
- Membaca dokumen yang relevan dengan task.
- Memeriksa kode sebelum melakukan perubahan.
- Tidak mengarang struktur file atau fungsi yang belum diperiksa.
- Tidak mengubah file di luar scope tanpa alasan.
- Tidak mengulang validasi yang sudah berhasil tanpa indikasi masalah.
- Memberikan ringkasan perubahan.
- Menyampaikan hasil pengujian.
- Menandai hal yang belum selesai.

---

# 19. PROGRESS TRACKING

Status setiap fase dicatat menggunakan kategori berikut:

| Status | Meaning |
|---|---|
| NOT STARTED | Belum dikerjakan |
| IN PROGRESS | Sedang dikerjakan |
| BLOCKED | Terhambat dependensi atau masalah |
| REVIEW | Menunggu pemeriksaan |
| COMPLETED | Memenuhi acceptance criteria |

Sebuah fase hanya boleh ditandai `COMPLETED` apabila seluruh deliverables dan acceptance criteria telah dipenuhi.

Progress keseluruhan tidak boleh hanya dihitung dari jumlah halaman atau file yang dibuat.

Kemajuan harus mencerminkan fungsi yang benar-benar berjalan dan sudah diuji.

---

# 20. CURRENT DEVELOPMENT STATUS

**Initial status:** Planning and documentation.

| Phase | Status |
|---|---|
| Phase 0 — Project Preparation | IN PROGRESS |
| Phase 1 — Application Foundation | NOT STARTED |
| Phase 2 — Master Data Management | NOT STARTED |
| Phase 3 — Recipe and Inventory | NOT STARTED |
| Phase 4 — Point of Sale | NOT STARTED |
| Phase 5 — Operational Reports | NOT STARTED |
| Phase 6 — Weather and Dataset | NOT STARTED |
| Phase 7 — Python ANN Integration | NOT STARTED |
| Phase 8 — Forecasting | NOT STARTED |
| Phase 9 — UI Refinement | NOT STARTED |
| Phase 10 — Testing and Validation | NOT STARTED |
| Phase 11 — Deployment | NOT STARTED |

Status di atas merupakan baseline perencanaan. Perbarui berdasarkan kondisi implementasi aktual.

---

# 21. FINAL PROJECT COMPLETION CRITERIA

Seumpama POS dapat dinyatakan selesai apabila:

1. Seluruh fungsi operasional utama berjalan.
2. Pengelolaan produk dan bahan baku berfungsi.
3. Recipe/BOM dapat digunakan untuk menghitung kebutuhan bahan baku.
4. Persediaan dan stock movement konsisten.
5. Transaksi POS berjalan dengan benar.
6. Laporan operasional dapat diverifikasi.
7. Dataset ANN dapat dibentuk dari data operasional.
8. Model ANN telah dilatih dan dievaluasi.
9. Hasil prediksi dapat diintegrasikan dengan sistem persediaan.
10. Safety stock dan ROP dapat dihitung.
11. Rekomendasi pembelian dapat ditampilkan.
12. Seluruh fungsi kritis telah diuji.
13. Aplikasi dapat dijalankan pada lingkungan production.
14. Dokumentasi teknis dan panduan pengguna tersedia.

**Catatan:** Penyelesaian aplikasi dan keberhasilan penelitian ANN merupakan dua hal yang saling berkaitan tetapi tidak identik. Aplikasi dapat berfungsi secara operasional, sementara kelayakan model ANN tetap harus dibuktikan melalui hasil eksperimen dan evaluasi.

---

# 22. RELATED DOCUMENTS

| Document | Responsibility |
|---|---|
| `PROJECT_MASTER.md` | Identitas, tujuan, dan gambaran keseluruhan proyek |
| `PROJECT_SPEC.md` | Kebutuhan fungsional dan aturan bisnis |
| `IMPLEMENTATION_PLAN.md` | Tahapan implementasi dan urutan pengerjaan |
| `DATABASE_DESIGN.md` | Struktur database dan relasi |
| `ANN_SPECIFICATION.md` | Spesifikasi penelitian dan implementasi ANN |
| `UI_UX_GUIDELINES.md` | Pedoman desain antarmuka |
| `AI_RULES.md` | Aturan AI coding assistant |
| `PROGRESS.md` | Status pengerjaan dan riwayat perubahan |

---

**END OF IMPLEMENTATION PLAN**