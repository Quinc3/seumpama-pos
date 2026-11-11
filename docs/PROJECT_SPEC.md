# PROJECT_SPEC.md

# SYSTEM FUNCTIONAL REQUIREMENTS & BUSINESS RULES
## Seumpama POS — Coffee Shop Seumpama Bunga

**Document Type:** Functional & Technical Specification  
**Project:** Seumpama POS  
**Version:** 1.0  
**Status:** Planning / Approved Direction  
**Backend:** Laravel 12  
**Frontend:** Laravel Blade, Tailwind CSS, Alpine.js  
**Machine Learning:** Python — Artificial Neural Network (ANN)  
**Database:** MariaDB / MySQL  
**Timezone:** Asia/Jakarta (WIB)

---

# 1. DOCUMENT PURPOSE

Dokumen ini mendefinisikan kebutuhan fungsional, aturan bisnis, perilaku sistem, validasi, dan batasan operasional aplikasi Seumpama POS.

Dokumen menjadi acuan implementasi seluruh modul aplikasi.

Setiap fitur yang dikembangkan harus mengikuti spesifikasi ini. Apabila terdapat kebutuhan yang belum didefinisikan, AI coding assistant tidak boleh membuat asumsi yang mengubah aturan bisnis utama.

## 1.1 Requirement Classification

Setiap kebutuhan dikategorikan sebagai:

| Kode | Kategori | Keterangan |
|---|---|---|
| FR | Functional Requirement | Fungsionalitas sistem |
| BR | Business Rule | Aturan bisnis |
| NFR | Non-Functional Requirement | Kualitas dan batasan sistem |
| SEC | Security Requirement | Keamanan |
| ML | Machine Learning Requirement | Kebutuhan ANN dan prediksi |
| UI | User Interface Requirement | Kebutuhan antarmuka |

## 1.2 Requirement Priority

- **MUST:** Wajib tersedia.
- **SHOULD:** Sangat direkomendasikan.
- **COULD:** Fitur tambahan yang dapat dikembangkan secara modular.

---

# 2. USER MANAGEMENT & ACCESS CONTROL

## 2.1 User Management

**FR-USER-001:** Sistem harus menyediakan pengelolaan pengguna.

Admin atau Owner dapat:
- Menambahkan pengguna.
- Mengubah informasi pengguna.
- Mengaktifkan atau menonaktifkan akun.
- Mengatur role dan permission.
- Melakukan reset password.
- Melihat riwayat aktivitas pengguna.

**FR-USER-002:** Setiap pengguna harus memiliki identitas akun yang unik.

**FR-USER-003:** Pengguna yang dinonaktifkan tidak dapat melakukan autentikasi.

## 2.2 Role Management

Role awal:

| Role | Tanggung Jawab |
|---|---|
| Owner | Mengelola dan memantau keseluruhan sistem |
| Admin | Mengelola operasional dan konfigurasi |
| Cashier | Menangani transaksi penjualan |
| Barista | Melihat dan menangani informasi pesanan |

Role dan permission harus dapat dikembangkan tanpa mengubah logika utama aplikasi.

## 2.3 Authorization Rules

**BR-USER-001:** Pengguna hanya dapat mengakses fitur berdasarkan permission yang dimiliki.

**BR-USER-002:** Menyembunyikan tombol di frontend tidak cukup untuk mengamankan fitur. Setiap aksi harus diverifikasi di backend.

**BR-USER-003:** Aktivitas kritis seperti perubahan stok, pembatalan transaksi, dan perubahan resep harus mencatat pengguna yang melakukan tindakan.

---

# 3. DASHBOARD MODULE

## 3.1 Dashboard Objectives

Dashboard menyajikan informasi operasional berdasarkan hak akses pengguna.

## 3.2 Owner Dashboard

Informasi:
- Total penjualan hari ini.
- Jumlah transaksi.
- Rata-rata nilai transaksi.
- Produk terlaris.
- Penggunaan bahan baku.
- Kondisi stok kritis.
- Pengeluaran.
- Ringkasan pengadaan.
- Ringkasan prediksi ANN.
- Rekomendasi kebutuhan stok.

## 3.3 Admin Dashboard

Informasi:
- Statistik transaksi.
- Produk dan kategori.
- Persediaan bahan baku.
- Stok minimum.
- Aktivitas operasional.
- Status integrasi cuaca.
- Status dataset.
- Status model ANN.

## 3.4 Cashier Dashboard

Informasi:
- Ringkasan transaksi.
- Riwayat transaksi.
- Status shift.
- Akses cepat ke halaman POS.

## 3.5 Dashboard Rules

**BR-DASH-001:** Data dashboard harus berasal dari database aktual.

**BR-DASH-002:** Data harus mengikuti periode operasional yang dipilih.

**BR-DASH-003:** Dashboard tidak boleh menampilkan nilai simulasi seolah-olah merupakan data aktual.

---

# 4. POINT OF SALE (POS) MODULE

## 4.1 POS Objectives

POS merupakan modul inti untuk mencatat transaksi penjualan dengan cepat, akurat, dan konsisten.

Antarmuka POS harus dioptimalkan untuk penggunaan kasir pada desktop, tablet, dan smartphone.

## 4.2 Product Catalog

**FR-POS-001:** Sistem harus menampilkan katalog produk yang tersedia.

**FR-POS-002:** Kasir dapat:
- Melihat produk.
- Mencari produk berdasarkan nama.
- Memfilter berdasarkan kategori.
- Melihat harga.
- Melihat gambar produk.
- Menambahkan produk ke keranjang.

**FR-POS-003:** Produk tidak aktif atau tidak tersedia tidak dapat ditambahkan ke transaksi baru.

## 4.3 Cart Management

**FR-POS-004:** Sistem harus mendukung pengelolaan keranjang transaksi.

Setiap item memiliki:
- Product ID.
- Product name.
- Quantity.
- Unit price.
- Subtotal.
- Catatan item apabila diperlukan.

**FR-POS-005:** Kasir dapat mengubah jumlah produk dan menghapus item dari keranjang.

**FR-POS-006:** Sistem menghitung subtotal dan total transaksi secara otomatis.

**FR-POS-007:** Sistem mendukung beberapa keranjang transaksi yang belum dibayar.

**BR-POS-001:** Keranjang yang belum dibayar tidak mengurangi stok aktual.

**BR-POS-002:** Harga transaksi harus disimpan sebagai snapshot agar perubahan harga produk tidak mengubah histori transaksi.

## 4.4 Discount Management

**FR-POS-008:** Sistem dapat menerapkan diskon sesuai konfigurasi.

Jenis diskon yang dapat dikembangkan:
- Diskon nominal.
- Diskon persentase.
- Diskon produk.
- Diskon transaksi.

**BR-POS-003:** Diskon harus divalidasi berdasarkan aturan yang berlaku.

**BR-POS-004:** Total transaksi tidak boleh menghasilkan nilai negatif.

## 4.5 Payment Management

Metode pembayaran awal:
- Cash.
- QRIS.
- Transfer.

**FR-POS-009:** Sistem menyediakan pemilihan metode pembayaran.

**FR-POS-010:** Untuk pembayaran tunai, sistem menghitung uang kembalian secara otomatis.

**FR-POS-011:** Sistem menyimpan:
- Payment method.
- Total tagihan.
- Jumlah pembayaran.
- Kembalian.
- Status pembayaran.
- Waktu pembayaran.

**BR-POS-005:** Transaksi hanya dianggap selesai setelah proses pembayaran berhasil dicatat.

**BR-POS-006:** Metode QRIS pada tahap awal dapat menggunakan pencatatan pembayaran manual. Integrasi payment gateway merupakan pengembangan terpisah.

## 4.6 Transaction Processing

Alur transaksi:

1. Kasir memilih produk.
2. Produk dimasukkan ke keranjang.
3. Sistem menghitung total.
4. Kasir memilih metode pembayaran.
5. Sistem memvalidasi pembayaran.
6. Sistem menyimpan transaksi.
7. Sistem menghitung pemakaian bahan baku berdasarkan resep.
8. Sistem mengurangi stok.
9. Sistem mencatat stock movement.
10. Sistem menghasilkan struk.

**BR-POS-007:** Penyimpanan transaksi, detail transaksi, pembayaran, dan pengurangan stok harus konsisten dalam database transaction.

**BR-POS-008:** Jika proses transaksi gagal, perubahan stok tidak boleh tersimpan sebagian.

**BR-POS-009:** Transaksi yang sudah berhasil tidak boleh dihapus secara permanen melalui antarmuka normal.

## 4.7 Transaction Cancellation

**FR-POS-012:** Pengguna yang memiliki permission dapat membatalkan transaksi sesuai prosedur.

**BR-POS-010:** Pembatalan transaksi harus memiliki alasan.

**BR-POS-011:** Jika stok sudah dikurangi, pembatalan harus menghasilkan stock movement pembalik.

**BR-POS-012:** Riwayat transaksi yang dibatalkan tetap tersimpan.

## 4.8 Receipt Management

**FR-POS-013:** Sistem menghasilkan struk transaksi.

Informasi struk:
- Identitas coffee shop.
- Nomor transaksi.
- Tanggal dan waktu.
- Kasir.
- Daftar produk.
- Jumlah.
- Harga.
- Diskon.
- Total.
- Metode pembayaran.
- Uang pembayaran dan kembalian jika tunai.

Sistem harus mendukung cetak ulang struk.

---

# 5. PRODUCT MANAGEMENT MODULE

## 5.1 Product Management

**FR-PROD-001:** Admin dapat menambahkan produk menu.

**FR-PROD-002:** Admin dapat mengubah informasi produk.

**FR-PROD-003:** Admin dapat mengaktifkan dan menonaktifkan produk.

Informasi produk:
- Nama.
- SKU.
- Kategori.
- Harga jual.
- Deskripsi.
- Gambar.
- Status ketersediaan.
- Resep produk.

## 5.2 Category Management

**FR-PROD-004:** Admin dapat mengelola kategori produk.

Kategori bersifat dinamis dan tidak ditentukan secara permanen di dalam kode.

**BR-PROD-001:** Produk harus memiliki kategori yang valid.

**BR-PROD-002:** Kategori yang masih digunakan produk tidak boleh dihapus tanpa penanganan relasi.

---

# 6. INGREDIENT MANAGEMENT MODULE

## 6.1 Ingredient Management

Bahan baku merupakan entitas yang berbeda dari produk menu.

**FR-ING-001:** Admin dapat menambahkan bahan baku baru.

**FR-ING-002:** Admin dapat mengubah informasi bahan baku.

**FR-ING-003:** Admin dapat mengatur satuan pengukuran.

**FR-ING-004:** Admin dapat mengatur stok awal dan stok minimum.

**FR-ING-005:** Admin dapat mengatur lead time pemasok.

**FR-ING-006:** Admin dapat menentukan apakah bahan baku termasuk target prediksi ANN.

**FR-ING-007:** Admin dapat menonaktifkan bahan baku.

Informasi bahan baku:
- Nama.
- SKU atau kode.
- Kategori.
- Satuan.
- Stok aktual.
- Minimum stock.
- Lead time.
- Safety stock configuration.
- Status bahan baku.
- Status target ANN.

## 6.2 Ingredient Classification

Dua klasifikasi:

**Core Ingredients**
- Bahan baku utama.
- Dapat dijadikan target prediksi ANN.
- Memiliki histori pemakaian yang relevan.

**Non-Core Ingredients**
- Bahan pendukung.
- Tetap dikelola melalui inventaris.
- Tidak menjadi target utama penelitian ANN.

Contoh:
- Air.
- Sedotan.
- Cup.
- Tissue.

**BR-ING-001:** Status core dan non-core dapat diubah melalui konfigurasi.

**BR-ING-002:** Bahan baku non-core tetap dapat dicatat dalam stock movement.

**BR-ING-003:** Nama bahan baku tidak boleh ditanam secara permanen di dalam kode program.

**BR-ING-004:** Produk menu tidak boleh otomatis diperlakukan sebagai bahan baku.

---

# 7. RECIPE / BILL OF MATERIALS MODULE

## 7.1 Recipe Management

Setiap produk menu dapat memiliki satu atau lebih komposisi bahan baku.

**FR-RECIPE-001:** Admin dapat membuat resep produk.

**FR-RECIPE-002:** Admin dapat menambahkan beberapa bahan baku ke dalam resep.

**FR-RECIPE-003:** Admin dapat menentukan jumlah dan satuan bahan baku per porsi.

**FR-RECIPE-004:** Admin dapat mengubah resep.

**FR-RECIPE-005:** Sistem harus menyimpan histori perubahan resep.

## 7.2 Recipe Rules

**BR-RECIPE-001:** Satu produk dapat menggunakan banyak bahan baku.

**BR-RECIPE-002:** Satu bahan baku dapat digunakan oleh banyak produk.

**BR-RECIPE-003:** Kuantitas bahan baku harus lebih besar dari nol.

**BR-RECIPE-004:** Satuan resep harus kompatibel dengan satuan inventaris atau memiliki konversi yang valid.

**BR-RECIPE-005:** Perubahan resep tidak boleh mengubah histori pemakaian bahan baku dari transaksi yang sudah selesai.

## 7.3 Consumption Calculation

Formula:

Ingredient Consumption = Quantity Sold × Ingredient Quantity per Recipe

Contoh:

Produk A:
- Terjual: 10 porsi.
- Kopi per porsi: 18 gram.

Pemakaian kopi:

10 × 18 = 180 gram.

Hasil perhitungan digunakan untuk:
- Pengurangan stok.
- Histori pemakaian.
- Pembentukan dataset ANN.
- Laporan inventaris.

---

# 8. INVENTORY MANAGEMENT MODULE

## 8.1 Inventory Features

**FR-INV-001:** Sistem menampilkan stok seluruh bahan baku.

**FR-INV-002:** Sistem menyediakan pencatatan stok masuk.

**FR-INV-003:** Sistem menyediakan pencatatan stok keluar.

**FR-INV-004:** Sistem menyediakan penyesuaian stok.

**FR-INV-005:** Sistem menyediakan stock opname.

**FR-INV-006:** Sistem menyimpan histori stock movement.

**FR-INV-007:** Sistem memberikan indikator stok rendah.

**FR-INV-008:** Sistem mendukung pencatatan tanggal kedaluwarsa apabila diperlukan.

## 8.2 Stock Movement

Setiap perubahan stok harus mencatat:
- Ingredient.
- Movement type.
- Quantity.
- Previous stock.
- New stock.
- Reference.
- User.
- Timestamp.
- Description.

Jenis pergerakan:
- Initial stock.
- Purchase stock in.
- Sales consumption.
- Manual adjustment.
- Stock opname.
- Transaction reversal.
- Waste or damaged goods.

**BR-INV-001:** Stok tidak boleh berubah tanpa pencatatan movement.

**BR-INV-002:** Perubahan stok harus divalidasi di backend.

**BR-INV-003:** Histori stock movement tidak boleh dihapus melalui antarmuka normal.

**BR-INV-004:** Koreksi stok dilakukan melalui adjustment, bukan mengubah histori transaksi lama.

## 8.3 Stock Availability

Sistem harus menyediakan informasi:
- Actual stock.
- Minimum stock.
- Safety stock.
- Reorder point.
- Predicted demand.
- Stock coverage.

Peringatan stok harus dapat dibedakan berdasarkan tingkat urgensi.

---

# 9. SUPPLIER & PURCHASING MODULE

## 9.1 Supplier Management

**FR-SUP-001:** Admin dapat mengelola informasi pemasok.

Informasi:
- Nama pemasok.
- Kontak.
- Alamat.
- Produk atau bahan baku yang disediakan.
- Lead time.
- Status pemasok.

## 9.2 Purchasing

**FR-PUR-001:** Admin dapat membuat pengadaan bahan baku.

**FR-PUR-002:** Sistem mendukung pencatatan item pengadaan dan kuantitas.

**FR-PUR-003:** Sistem mendukung status pengadaan:
- Draft.
- Ordered.
- Partially received.
- Received.
- Cancelled.

**FR-PUR-004:** Penerimaan barang harus memperbarui stok melalui stock movement.

**BR-PUR-001:** Stok tidak bertambah hanya karena purchase order dibuat.

**BR-PUR-002:** Stok bertambah setelah barang diterima dan diverifikasi.

**BR-PUR-003:** Sistem harus mendukung penerimaan barang sebagian.

**BR-PUR-004:** Pengadaan harus dapat ditelusuri ke pemasok dan pengguna yang membuatnya.

---

# 10. OPERATIONAL DAY & BUSINESS HOURS

## 10.1 Operating Hours

Jadwal operasional:

| Hari | Buka | Tutup |
|---|---|---|
| Minggu | 15:00 | 23:00 |
| Senin | 15:00 | 23:00 |
| Selasa | 15:00 | 23:00 |
| Rabu | 15:00 | 23:00 |
| Kamis | 15:00 | 23:00 |
| Jumat | 15:00 | 24:00 |
| Sabtu | 15:00 | 24:00 |

Timezone: Asia/Jakarta.

## 10.2 Business Day Rules

**BR-OPS-001:** Sistem harus membedakan tanggal kalender dan hari operasional.

**BR-OPS-002:** Transaksi harus terhubung dengan hari operasional yang benar.

**BR-OPS-003:** Jam operasional harus dapat diubah melalui konfigurasi.

**BR-OPS-004:** Sistem harus mendukung operasional yang melewati tengah malam apabila jadwal diperpanjang.

**BR-OPS-005:** Rekap penjualan, pemakaian bahan baku, dan dataset harus menggunakan business day.

Contoh:

Operasional Jumat:
- Mulai Jumat pukul 15:00.
- Berakhir pada batas tutup yang dikonfigurasi.
- Transaksi yang terjadi setelah tengah malam harus mengikuti sesi operasional jika sesi tersebut masih aktif.

---

# 11. WEATHER INTEGRATION MODULE

## 11.1 Weather Data

Data cuaca digunakan sebagai variabel pendukung penelitian ANN.

Parameter awal:
- Temperature.
- Rainfall.
- Weather condition.

## 11.2 Weather Integration Rules

**FR-WEA-001:** Sistem dapat mengambil data cuaca dari provider eksternal.

**FR-WEA-002:** Sistem menyimpan histori data cuaca.

**FR-WEA-003:** Sistem menampilkan status sinkronisasi.

**FR-WEA-004:** Admin dapat menjalankan sinkronisasi manual jika diperlukan.

**BR-WEA-001:** API key disimpan melalui environment configuration atau mekanisme secret yang aman.

**BR-WEA-002:** Provider cuaca harus dapat diganti tanpa mengubah business logic ANN.

**BR-WEA-003:** Kegagalan API cuaca tidak boleh menghentikan transaksi POS.

**BR-WEA-004:** Data cuaca yang tidak tersedia harus ditandai sebagai missing, bukan dibuat secara acak.

**BR-WEA-005:** Data cuaca harus dapat dikaitkan dengan business day.

## 11.3 Weather Aggregation

Agregasi mengikuti jadwal operasional.

Data yang dapat dihitung:
- Rata-rata suhu.
- Total curah hujan.
- Kondisi cuaca dominan.
- Jumlah pengamatan.

Frekuensi pengambilan data dan metode agregasi final mengikuti kemampuan provider dan hasil evaluasi kualitas data.

---

# 12. DATASET MANAGEMENT MODULE

## 12.1 Dataset Source

Sumber dataset:
- Data transaksi.
- Data resep.
- Data pemakaian bahan baku.
- Data cuaca.
- Data kalender operasional.

## 12.2 Dataset Duration

Target pengumpulan:
- Minimum perencanaan: 3 bulan.
- Rekomendasi: 6 bulan.

Durasi tersebut merupakan target awal dan tidak menjamin kecukupan data untuk model ANN.

## 12.3 Dataset Granularity

Satu baris dataset merepresentasikan satu hari operasional untuk satu target bahan baku.

Contoh:

| Date | Sales | Temperature | Rainfall | Ingredient Usage |
|---|---:|---:|---:|---:|
| Day 1 | 45 | 29 | 0 | 0.8 kg |
| Day 2 | 52 | 28 | 2 | 0.9 kg |
| Day 3 | 38 | 30 | 0 | 0.7 kg |

Angka hanya ilustrasi.

## 12.4 Dataset Rules

**BR-DATA-001:** Dataset harus berasal dari data historis yang tercatat.

**BR-DATA-002:** Dataset harus dapat diregenerasi berdasarkan sumber data.

**BR-DATA-003:** Dataset harus memiliki versi.

**BR-DATA-004:** Perubahan resep harus diperhitungkan ketika menghitung pemakaian historis.

**BR-DATA-005:** Hari tanpa transaksi harus dibedakan dari data yang hilang.

**BR-DATA-006:** Data simulasi harus memiliki penanda khusus.

**BR-DATA-007:** Data training, validation, dan testing harus dibagi secara kronologis.

**BR-DATA-008:** Data testing tidak boleh digunakan dalam proses training.

---

# 13. ARTIFICIAL NEURAL NETWORK MODULE

## 13.1 ANN Objectives

Modul ANN bertugas memprediksi kebutuhan harian bahan baku utama.

Metode utama penelitian adalah Artificial Neural Network menggunakan pendekatan Multilayer Perceptron (MLP) sebagai model awal eksperimen.

## 13.2 Prediction Target

Target:

Predicted Daily Ingredient Demand.

Setiap bahan baku core yang memenuhi kriteria dapat dijadikan target model.

Contoh:
- Coffee bean.
- Fresh milk.
- Vanilla syrup.
- Green tea powder.

## 13.3 Input Features

Kandidat fitur:
- Historical sales.
- Historical ingredient consumption.
- Historical demand.
- Temperature.
- Rainfall.
- Day of week.
- Rolling average demand.

Fitur final harus mengikuti hasil analisis dataset dan ANN_SPECIFICATION.md.

## 13.4 Training Requirements

**FR-ANN-001:** Sistem menyediakan proses pembentukan dataset.

**FR-ANN-002:** Sistem mendukung training ANN melalui Python service.

**FR-ANN-003:** Sistem menyimpan histori training.

**FR-ANN-004:** Sistem menyimpan parameter model.

**FR-ANN-005:** Sistem menyimpan hasil evaluasi.

**FR-ANN-006:** Sistem menyimpan versi model.

**FR-ANN-007:** Sistem dapat menjalankan prediksi menggunakan model yang telah disetujui.

## 13.5 Model Evaluation

Metrik evaluasi:
- MAE.
- RMSE.
- Metrik tambahan apabila dibutuhkan berdasarkan ANN_SPECIFICATION.md.

ANN harus dibandingkan dengan baseline sederhana.

**BR-ANN-001:** Model tidak boleh dianggap layak hanya karena proses training berhasil.

**BR-ANN-002:** Model harus memiliki histori evaluasi.

**BR-ANN-003:** Model baru tidak otomatis menggantikan model aktif.

**BR-ANN-004:** Prediksi negatif harus ditangani sesuai batasan kebutuhan stok.

**BR-ANN-005:** Kegagalan Python service tidak boleh menghentikan fungsi POS.

**BR-ANN-006:** Training tidak boleh menghambat transaksi operasional.

---

# 14. STOCK FORECASTING & REPLENISHMENT

## 14.1 Forecasting

Sistem menggabungkan:
- Hasil prediksi ANN.
- Stok aktual.
- Safety stock.
- Lead time.
- Reorder point.

## 14.2 Safety Stock

Formula awal:

σL = σd × √L

SS = Z × σL

Keterangan:
- SS = Safety Stock.
- Z = Z-score service level.
- σd = Standar deviasi kebutuhan harian.
- L = Lead time dalam hari.
- σL = Standar deviasi kebutuhan selama lead time.

Formula ini merupakan pendekatan awal dengan asumsi kebutuhan harian independen dan lead time tetap.

Jika asumsi tersebut tidak terpenuhi, metode perhitungan harus dievaluasi.

## 14.3 Reorder Point

Formula:

ROP = Average Daily Demand × Lead Time + Safety Stock

Sistem dapat menggunakan hasil prediksi ANN untuk memperkirakan kebutuhan selama lead time.

## 14.4 Stock Recommendation

**FR-FORE-001:** Sistem menampilkan prediksi kebutuhan bahan baku.

**FR-FORE-002:** Sistem menampilkan stok aktual.

**FR-FORE-003:** Sistem menampilkan safety stock.

**FR-FORE-004:** Sistem menampilkan reorder point.

**FR-FORE-005:** Sistem menghitung estimasi stock coverage.

**FR-FORE-006:** Sistem memberikan rekomendasi kuantitas pengadaan.

**BR-FORE-001:** Rekomendasi bukan perintah pembelian otomatis.

**BR-FORE-002:** Pengadaan tetap membutuhkan persetujuan pengguna berwenang.

**BR-FORE-003:** Setiap bahan baku dapat memiliki lead time berbeda.

---

# 15. REPORTING & ANALYTICS MODULE

## 15.1 Sales Reports

Laporan:
- Penjualan harian.
- Penjualan mingguan.
- Penjualan bulanan.
- Total transaksi.
- Rata-rata nilai transaksi.
- Produk terlaris.
- Penjualan berdasarkan kategori.
- Penjualan berdasarkan kasir.

## 15.2 Inventory Reports

Laporan:
- Stok bahan baku.
- Pemakaian bahan baku.
- Stock movement.
- Stok masuk dan keluar.
- Stok kritis.
- Hasil stock opname.
- Histori pengadaan.

## 15.3 ANN Reports

Laporan:
- Dataset.
- Histori training.
- Versi model.
- Parameter training.
- Hasil evaluasi.
- Prediksi kebutuhan bahan baku.
- Perbandingan aktual dan prediksi.

## 15.4 Export

Sistem dapat dikembangkan untuk mengekspor laporan ke:
- PDF.
- XLSX.

Data laporan harus mengikuti filter dan periode yang dipilih pengguna.

---

# 16. ATTENDANCE MODULE (OPTIONAL)

Modul absensi bersifat opsional dan terpisah dari fungsi inti POS.

Fitur yang dapat dikembangkan:
- Check-in.
- Check-out.
- Attendance history.
- Shift configuration.
- Attendance report.
- Location validation apabila dibutuhkan.

Modul ini tidak boleh menjadi dependensi POS, inventory, atau ANN.

---

# 17. SYSTEM SETTINGS

## 17.1 General Settings

Konfigurasi:
- Nama coffee shop.
- Logo.
- Alamat.
- Kontak.
- Timezone.
- Format tanggal.
- Format mata uang.

## 17.2 Operational Settings

Konfigurasi:
- Jam operasional.
- Batas hari operasional.
- Pengaturan shift.
- Nomor transaksi.
- Pengaturan struk.

## 17.3 Inventory Settings

Konfigurasi:
- Default minimum stock.
- Satuan.
- Stock notification.
- Pengaturan konversi satuan.

## 17.4 ANN Settings

Konfigurasi:
- Target bahan baku.
- Dataset period.
- Model aktif.
- Jadwal training.
- Jadwal prediksi.

Perubahan konfigurasi harus memiliki validasi dan permission yang sesuai.

---

# 18. MODULAR SYSTEM REQUIREMENTS

## 18.1 Module Independence

Setiap modul harus memiliki batas tanggung jawab yang jelas.

Modul inti:
- POS.
- Products.
- Categories.
- Inventory.
- Recipes.
- Sales.
- Users.
- Reports.

Modul pendukung:
- Suppliers.
- Purchasing.
- Customers.
- Promotions.
- Expenses.
- Weather.
- Dataset.
- ANN.
- Forecasting.
- Attendance.
- Settings.

## 18.2 Module Rules

**BR-MOD-001:** Modul harus dapat dikembangkan tanpa mengubah modul lain secara tidak perlu.

**BR-MOD-002:** Komunikasi antarmodul harus melalui interface atau service yang jelas apabila diperlukan.

**BR-MOD-003:** Modul opsional tidak boleh menjadi dependensi wajib modul inti.

**BR-MOD-004:** Modul yang memiliki dependensi tidak boleh dinonaktifkan apabila masih dibutuhkan oleh modul aktif.

**BR-MOD-005:** Modular tidak berarti setiap fitur harus memiliki abstraksi atau service tersendiri.

**BR-MOD-006:** Gunakan struktur Laravel yang konvensional selama belum ada kebutuhan nyata untuk arsitektur package modular tambahan.

---

# 19. UI/UX FUNCTIONAL REQUIREMENTS

## 19.1 Responsive Interface

Sistem harus menyediakan pengalaman penggunaan yang optimal pada:
- Smartphone.
- Tablet.
- Laptop.
- Desktop.

Desktop dan mobile tidak boleh hanya menggunakan tampilan yang sama dengan ukuran elemen diperkecil.

Struktur dan interaksi harus disesuaikan dengan perangkat.

## 19.2 Desktop

Karakteristik:
- Pemanfaatan ruang layar secara optimal.
- Sidebar untuk navigasi administratif.
- Data table yang informatif.
- Multi-column layout.
- Panel transaksi POS yang mudah diakses.

## 19.3 Mobile

Karakteristik:
- Touch-first interaction.
- Navigasi yang disesuaikan.
- Kartu data untuk menggantikan tabel yang terlalu lebar.
- Drawer atau bottom sheet sesuai kebutuhan.
- Form yang mudah diisi.
- Tombol aksi utama mudah dijangkau.
- Tidak memerlukan horizontal scrolling pada konten utama.

## 19.4 Design System

Ketentuan:
- Tailwind CSS.
- Laravel Blade components.
- Alpine.js untuk interaksi frontend.
- Heroicons atau Lucide sebagai ikon.
- Figtree sebagai tipografi utama.
- Aksen warna biru sebagai warna identitas antarmuka.
- Semantic colors untuk status.
- Konsistensi spacing dan typography.
- Accessible contrast.
- Focus states yang jelas.

## 19.5 UI State Requirements

Setiap halaman yang membutuhkan data harus memiliki state yang sesuai:
- Loading.
- Empty.
- Error.
- Success.
- Disabled.
- Validation error.

## 19.6 POS UI

Desktop:
- Katalog produk.
- Search dan filter kategori.
- Keranjang di sisi kanan.
- Ringkasan pembayaran yang mudah diakses.

Mobile:
- Grid produk responsif.
- Search yang mudah dijangkau.
- Keranjang melalui drawer atau bottom sheet.
- Ringkasan total transaksi yang selalu mudah ditemukan.
- Checkout dengan kontrol berukuran nyaman untuk sentuhan.

## 19.7 UI Quality Rules

**BR-UI-001:** Tidak menggunakan tampilan CRUD generik tanpa hierarchy visual.

**BR-UI-002:** Komponen harus konsisten tetapi tidak memaksakan semua halaman memiliki layout identik.

**BR-UI-003:** Tidak menggunakan emoji sebagai pengganti ikon antarmuka.

**BR-UI-004:** Semua tombol harus memiliki fungsi yang jelas.

**BR-UI-005:** Tampilan harus diuji pada ukuran mobile dan desktop.

---

# 20. SECURITY & DATA INTEGRITY

## 20.1 Authentication

- Login.
- Logout.
- Password hashing.
- Session management.
- Password reset.

## 20.2 Authorization

- Role-based access.
- Permission-based authorization.
- Backend authorization.
- Protection terhadap akses tidak sah.

## 20.3 Input Validation

Semua input pengguna harus divalidasi di backend.

Validasi frontend hanya membantu pengalaman pengguna dan bukan pengganti validasi backend.

## 20.4 Database Integrity

- Foreign key.
- Unique constraints.
- Database transactions.
- Appropriate decimal precision.
- Consistent stock movement.
- Referential integrity.

## 20.5 Audit Trail

Aktivitas kritis harus dapat ditelusuri.

Contoh:
- Perubahan harga.
- Perubahan resep.
- Penyesuaian stok.
- Pembatalan transaksi.
- Perubahan permission.
- Perubahan konfigurasi ANN.

## 20.6 External Service Security

- API key tidak boleh masuk repository.
- Komunikasi Python service harus dibatasi.
- Request eksternal harus memiliki timeout.
- Error harus dicatat.
- Kegagalan eksternal tidak boleh merusak transaksi bisnis.

---

# 21. SYSTEM VALIDATION & ACCEPTANCE CRITERIA

Sistem dianggap memenuhi spesifikasi apabila kriteria berikut terpenuhi.

| ID | Acceptance Criteria |
|---|---|
| AC-001 | Pengguna dapat login sesuai role dan permission |
| AC-002 | Kasir dapat membuat transaksi |
| AC-003 | Pembayaran dapat dicatat dengan benar |
| AC-004 | Stok berkurang sesuai resep |
| AC-005 | Transaksi gagal tidak menghasilkan perubahan stok permanen |
| AC-006 | Histori transaksi dapat ditampilkan |
| AC-007 | Admin dapat mengelola produk dan kategori |
| AC-008 | Admin dapat mengelola bahan baku |
| AC-009 | Resep dapat diubah tanpa merusak histori pemakaian |
| AC-010 | Stock movement mencatat seluruh perubahan stok |
| AC-011 | Sistem mendukung pengadaan bahan baku |
| AC-012 | Data cuaca dapat disimpan dan ditelusuri |
| AC-013 | Dataset dapat dibentuk dari data historis |
| AC-014 | ANN dapat dilatih melalui Python service |
| AC-015 | Hasil evaluasi model dapat disimpan |
| AC-016 | Prediksi kebutuhan bahan baku dapat ditampilkan |
| AC-017 | Safety stock dan ROP dapat dihitung |
| AC-018 | Rekomendasi pengadaan dapat ditampilkan |
| AC-019 | Sistem berfungsi pada desktop dan mobile |
| AC-020 | Modul opsional tidak mengganggu modul inti |

Pengujian dilakukan secara bertahap mengikuti IMPLEMENTATION_PLAN.md.

---

# 22. OUT OF SCOPE & IMPLEMENTATION BOUNDARIES

Fitur berikut tidak otomatis menjadi bagian implementasi inti:

- Payment gateway production.
- Integrasi akuntansi eksternal.
- Multi-branch management.
- Multi-warehouse management.
- Supplier marketplace.
- Automatic purchase ordering.
- Native Android/iOS application.
- Advanced deep learning architecture.
- Real-time weather streaming.

Fitur tersebut dapat dipertimbangkan sebagai pengembangan lanjutan apabila terdapat kebutuhan nyata.

---

# 23. OPEN REQUIREMENTS

Hal berikut masih membutuhkan data operasional atau evaluasi sebelum implementasi final:

1. Daftar bahan baku aktual.
2. Resep dan kuantitas aktual setiap produk.
3. Satuan dasar setiap bahan baku.
4. Harga dan pemasok bahan baku.
5. Lead time masing-masing pemasok.
6. Data historis penjualan yang tersedia.
7. Kualitas dan ketersediaan data cuaca historis.
8. Fitur input ANN final.
9. Parameter training ANN final.
10. Target service level untuk safety stock.
11. Permission final setiap role.
12. Kebijakan pembatalan dan refund.
13. Detail perangkat cetak struk dan metode pembayaran.

AI coding assistant harus menggunakan konfigurasi sementara yang diberi penanda apabila data aktual belum tersedia. Jangan mengarang nilai operasional dan menganggapnya sebagai data penelitian.

---

# 24. DOCUMENT GOVERNANCE

Dokumen ini memiliki tanggung jawab khusus dalam mendefinisikan kebutuhan fungsional dan aturan bisnis.

Hubungan dengan dokumen lainnya:

| Document | Responsibility |
|---|---|
| PROJECT_MASTER.md | Identitas dan arsitektur keseluruhan |
| PROJECT_SPEC.md | Kebutuhan fitur dan aturan bisnis |
| IMPLEMENTATION_PLAN.md | Urutan implementasi |
| ANN_SPECIFICATION.md | Detail teknis ANN dan penelitian |
| DATABASE_DESIGN.md | Struktur database |
| UI_UX_GUIDELINES.md | Standar desain antarmuka |
| AI_RULES.md | Aturan AI coding assistant |
| PROGRESS.md | Status dan histori pekerjaan |

Jika terdapat konflik:
1. Keputusan terbaru yang sudah dikonfirmasi menjadi acuan.
2. PROJECT_MASTER.md menjadi acuan arah dan keputusan arsitektur.
3. PROJECT_SPEC.md menjadi acuan perilaku dan aturan bisnis.
4. ANN_SPECIFICATION.md menjadi acuan teknis model penelitian.
5. DATABASE_DESIGN.md menjadi acuan implementasi struktur data.

Perubahan aturan harus dicatat dan disinkronkan ke dokumen terkait.

---

# END OF PROJECT_SPEC.md