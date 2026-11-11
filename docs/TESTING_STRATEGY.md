# TESTING STRATEGY
## SEUMPAMA POS

**Dokumen Strategi Pengujian Sistem**

| Informasi | Keterangan |
|---|---|
| Nama Proyek | Seumpama POS |
| Jenis Aplikasi | Web-Based Point of Sale and Inventory Management System |
| Backend | Laravel |
| Frontend | Blade, Tailwind CSS, Alpine.js, JavaScript |
| Database | MySQL |
| ANN Service | Python + Keras |
| Status Dokumen | Draft |
| Versi | 1.0 |

---

## 1. Tujuan Pengujian

Testing Strategy ini menjadi pedoman untuk memastikan setiap modul Seumpama POS berjalan sesuai spesifikasi, terintegrasi dengan benar, dan tidak merusak fungsi lain ketika terjadi perubahan kode.

Pengujian difokuskan pada:

1. Ketepatan proses transaksi penjualan.
2. Konsistensi stok bahan baku dan pergerakan inventaris.
3. Ketepatan perhitungan penggunaan bahan berdasarkan resep (BOM).
4. Pembatasan akses berdasarkan role dan permission.
5. Keakuratan data yang digunakan untuk pelatihan dan prediksi ANN.
6. Ketahanan aplikasi ketika layanan eksternal mengalami gangguan.
7. Kemudahan penggunaan antarmuka pada tablet dan perangkat desktop.

## 2. Prinsip Pengujian

- **Test early:** pengujian dilakukan sejak modul mulai dikembangkan.
- **Test critical paths first:** proses transaksi dan inventaris menjadi prioritas.
- **Isolation:** pengujian tidak boleh mengubah data produksi.
- **Repeatability:** pengujian dapat dijalankan kembali dengan hasil yang konsisten.
- **Traceability:** setiap pengujian harus dapat dikaitkan dengan kebutuhan atau fitur.
- **Regression prevention:** perubahan kode tidak boleh merusak fitur yang sebelumnya berjalan.
- **No false success:** fitur tidak boleh dinyatakan lulus hanya karena halaman berhasil dibuka.

## 3. Jenis Pengujian

### 3.1 Unit Testing

Menguji fungsi atau komponen secara terisolasi.

Contoh:

- Perhitungan subtotal transaksi.
- Perhitungan diskon dan total pembayaran.
- Konversi kuantitas bahan baku.
- Perhitungan kebutuhan bahan berdasarkan BOM.
- Perhitungan safety stock dan reorder point.
- Validasi data input.
- Pengolahan fitur untuk ANN.

### 3.2 Feature Testing

Menguji alur fitur secara lebih menyeluruh, termasuk interaksi antarkomponen aplikasi.

Contoh:

- Login dan logout.
- CRUD produk dan bahan baku.
- Pengelolaan resep.
- Pembuatan transaksi.
- Penerimaan dan penyesuaian stok.
- Laporan penjualan.
- Pengelolaan pengguna dan permission.
- Pengelolaan data prediksi.

### 3.3 Integration Testing

Menguji komunikasi antarmodul atau antarlayanan.

Contoh:

- Laravel dengan MySQL.
- Laravel dengan Python ANN Service.
- Laravel dengan penyedia data cuaca Open-Meteo.
- Sistem transaksi dengan modul inventaris.
- Sistem penjualan dengan printer thermal.
- Sistem laporan dengan data transaksi.

### 3.4 UI Testing

Memastikan antarmuka dapat digunakan sesuai kebutuhan operasional.

Fokus pengujian:

- Responsivitas desktop dan tablet.
- Ukuran tombol dan area sentuh.
- Navigasi halaman.
- Validasi form.
- Loading, empty, success, dan error state.
- Tampilan tabel dan laporan.
- Tampilan POS pada layar tablet.

### 3.5 Regression Testing

Dilakukan setelah perubahan kode, perbaikan bug, atau penambahan fitur.

Minimal mencakup:

- Login dan hak akses.
- Transaksi penjualan.
- Pengurangan stok.
- Pembatalan atau pengembalian transaksi.
- Laporan penjualan.
- Penggunaan BOM.
- Integrasi ANN.

## 4. Prioritas Pengujian

| Prioritas | Area | Alasan |
|---|---|---|
| P0 - Critical | Transaksi dan pembayaran manual | Kesalahan dapat menyebabkan kerugian atau data transaksi tidak konsisten |
| P0 - Critical | Pengurangan dan pergerakan stok | Kesalahan dapat membuat stok tidak akurat |
| P0 - Critical | Authentication dan authorization | Mencegah akses tidak sah |
| P1 - High | BOM dan penggunaan bahan | Menjadi dasar perhitungan konsumsi bahan |
| P1 - High | Laporan penjualan dan inventaris | Menjamin ketepatan informasi operasional |
| P1 - High | Dataset dan prediksi ANN | Memengaruhi rekomendasi kebutuhan stok |
| P2 - Medium | Printer thermal dan PDF | Mendukung proses operasional dan dokumentasi |
| P2 - Medium | Dashboard dan visualisasi | Mendukung pemantauan usaha |
| P3 - Low | Detail visual dan animasi | Tidak boleh mengalahkan prioritas fungsi inti |

## 5. Test Scenario per Modul

### 5.1 Authentication dan Authorization

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| AUTH-001 | Login menggunakan akun valid | Pengguna berhasil masuk |
| AUTH-002 | Login menggunakan password salah | Login ditolak |
| AUTH-003 | Mengakses halaman tanpa login | Pengguna diarahkan ke halaman login |
| AUTH-004 | Kasir mengakses halaman owner | Akses ditolak |
| AUTH-005 | Pengguna logout | Session berakhir |
| AUTH-006 | Mengakses endpoint tanpa permission | Server menolak permintaan |

Pengujian permission harus dilakukan pada backend, bukan hanya menyembunyikan tombol atau menu di frontend.

### 5.2 Product Management

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| PROD-001 | Menambahkan produk valid | Produk tersimpan |
| PROD-002 | Menambahkan produk tanpa nama | Validasi menolak |
| PROD-003 | Mengubah informasi produk | Data diperbarui |
| PROD-004 | Menonaktifkan produk | Produk tidak tersedia untuk transaksi baru |
| PROD-005 | Menghapus produk yang sudah memiliki transaksi | Riwayat transaksi tetap terjaga |

### 5.3 Ingredient dan Inventory Management

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| INV-001 | Menambahkan bahan baku | Bahan tersimpan |
| INV-002 | Menambahkan stok masuk | Stok bertambah sesuai jumlah |
| INV-003 | Melakukan penyesuaian stok | Stok diperbarui dan tercatat |
| INV-004 | Melakukan pengeluaran bahan | Stok berkurang dan tercatat |
| INV-005 | Memasukkan jumlah negatif | Validasi menolak |
| INV-006 | Stok mencapai batas minimum | Sistem memberikan indikator stok rendah |
| INV-007 | Melihat riwayat pergerakan stok | Riwayat dapat ditampilkan |

Bahan baku harus mendukung satuan yang jelas dan konsisten. Konversi satuan hanya diperbolehkan jika aturan konversinya telah ditentukan.

### 5.4 Recipe dan Bill of Materials (BOM)

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| BOM-001 | Membuat resep produk | Resep tersimpan |
| BOM-002 | Menambahkan beberapa bahan ke resep | Komposisi tersimpan |
| BOM-003 | Mengubah jumlah bahan | Versi resep terbaru tersimpan |
| BOM-004 | Menghitung kebutuhan bahan dari transaksi | Jumlah konsumsi dihitung berdasarkan BOM yang berlaku |
| BOM-005 | Mengubah resep setelah transaksi lama | Riwayat konsumsi lama tidak berubah |
| BOM-006 | Produk tidak memiliki resep | Sistem memberikan penanganan yang jelas |

Setiap transaksi harus menggunakan versi resep yang berlaku pada saat transaksi terjadi. Perubahan resep tidak boleh mengubah perhitungan historis secara retroaktif.

### 5.5 Point of Sale (POS)

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| POS-001 | Menambahkan produk ke keranjang | Produk masuk ke keranjang |
| POS-002 | Mengubah kuantitas produk | Subtotal diperbarui |
| POS-003 | Menghapus produk dari keranjang | Produk dihapus |
| POS-004 | Menyelesaikan pembayaran manual | Transaksi berhasil tersimpan |
| POS-005 | Melakukan transaksi dengan beberapa produk | Total dihitung dengan benar |
| POS-006 | Membatalkan transaksi sebelum pembayaran berhasil | Tidak ada pengurangan stok |
| POS-007 | Transaksi berhasil | Pergerakan stok tercatat sesuai BOM |
| POS-008 | Terjadi kegagalan saat menyimpan transaksi | Tidak ada transaksi atau pengurangan stok parsial |
| POS-009 | Melakukan pembatalan setelah transaksi selesai | Reversal tercatat sesuai permission |
| POS-010 | Membuka struk transaksi | Data struk sesuai transaksi |

**Aturan kritis:** pembuatan transaksi, detail transaksi, dan pergerakan stok harus dijalankan secara konsisten dalam database transaction. Kegagalan pada salah satu proses harus mencegah kondisi data setengah tersimpan.

Keranjang yang belum dibayar tidak boleh mengurangi stok.

### 5.6 Sales Report

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| RPT-001 | Melihat laporan harian | Data sesuai transaksi pada hari operasional |
| RPT-002 | Melihat laporan berdasarkan periode | Data sesuai rentang tanggal |
| RPT-003 | Memfilter berdasarkan produk | Data produk sesuai filter |
| RPT-004 | Mengekspor laporan PDF | File dapat dibuka dan datanya sesuai |
| RPT-005 | Tidak ada transaksi pada periode | Empty state ditampilkan |

Pengelompokan laporan harus mengikuti konsep hari operasional Seumpama Bunga, bukan secara otomatis mengandalkan pergantian tanggal kalender.

### 5.7 Attendance

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| ATT-001 | Karyawan melakukan absensi dalam radius yang diizinkan | Absensi dapat diproses |
| ATT-002 | Karyawan berada di luar radius | Absensi ditolak atau ditandai sesuai aturan |
| ATT-003 | Lokasi perangkat tidak tersedia | Sistem memberikan pesan yang jelas |
| ATT-004 | Karyawan melakukan absensi berulang | Duplikasi ditangani sesuai aturan |
| ATT-005 | Melihat riwayat absensi | Data sesuai catatan absensi |

Nilai radius awal yang digunakan adalah 20 meter. Validasi lokasi perlu mempertimbangkan keterbatasan akurasi GPS dan izin lokasi perangkat.

### 5.8 ANN Prediction

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| ANN-001 | Mengirim dataset valid untuk training | Proses training dapat dijalankan |
| ANN-002 | Dataset memiliki nilai kosong | Data ditangani berdasarkan aturan preprocessing |
| ANN-003 | Dataset memiliki nilai tidak valid | Data ditolak atau ditangani secara eksplisit |
| ANN-004 | Melakukan prediksi kebutuhan bahan | Hasil prediksi tersedia |
| ANN-005 | Python Service tidak tersedia | POS tetap dapat digunakan |
| ANN-006 | Model belum tersedia | Sistem menampilkan status yang jelas |
| ANN-007 | Menyimpan model baru | Versi model dan metadatanya tercatat |
| ANN-008 | Membandingkan hasil prediksi dengan data aktual | Metrik evaluasi dapat dihitung |
| ANN-009 | Menggunakan data cuaca yang tidak tersedia | Missing data ditandai, bukan dibuat-buat |

Evaluasi model wajib menggunakan pembagian dataset secara kronologis agar data masa depan tidak bocor ke proses pelatihan.

Metrik evaluasi minimal:

- MAE (Mean Absolute Error).
- RMSE (Root Mean Squared Error).

Hasil ANN perlu dibandingkan dengan baseline sederhana. Model tidak boleh dinyatakan lebih baik hanya karena proses training berhasil.

### 5.9 Weather Integration

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| WTH-001 | Mengambil data cuaca dari Open-Meteo | Data diterima dan disimpan |
| WTH-002 | API tidak merespons | Sistem mencatat kegagalan |
| WTH-003 | Data cuaca tidak lengkap | Field yang hilang ditandai |
| WTH-004 | Mengolah data berdasarkan jam operasional | Agregasi sesuai hari operasional |
| WTH-005 | Mengganti provider cuaca | Modul lain tidak perlu diubah secara besar |

Integrasi harus menggunakan adapter agar provider cuaca dapat diganti tanpa mengubah logika inti aplikasi.

### 5.10 Printer dan Receipt

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| PRN-001 | Membuka struk digital | Struk tampil |
| PRN-002 | Mengunduh atau mencetak PDF | Dokumen dapat dibuat |
| PRN-003 | Mencetak melalui printer thermal | Struk tercetak |
| PRN-004 | Printer tidak terhubung | Sistem menampilkan informasi kegagalan |
| PRN-005 | Membuka cash drawer melalui printer | Berfungsi jika perangkat dan protokol mendukung |
| PRN-006 | Mencetak ulang transaksi | Struk dapat dicetak ulang tanpa membuat transaksi baru |

Pengujian printer fisik baru dapat difinalisasi setelah merek, tipe, koneksi, protokol, dan kompatibilitas cash drawer diketahui.

## 6. ANN Testing dan Data Validation

Pengujian ANN tidak hanya memeriksa apakah model menghasilkan angka. Pengujian juga harus memastikan kualitas data dan proses evaluasi.

### 6.1 Dataset Validation

Validasi meliputi:

- Tanggal operasional.
- Identitas bahan baku.
- Jumlah konsumsi.
- Satuan bahan.
- Data penjualan.
- Versi BOM.
- Data cuaca.
- Missing values.
- Duplikasi data.
- Nilai tidak wajar.

### 6.2 Data Leakage Prevention

Data training, validation, dan testing harus dipisahkan berdasarkan urutan waktu.

Data masa depan tidak boleh digunakan untuk membentuk fitur atau preprocessing yang seharusnya hanya diketahui dari data masa lalu.

### 6.3 Prediction Validation

Hasil prediksi harus:

- Memiliki identitas bahan yang jelas.
- Memiliki periode prediksi yang jelas.
- Memiliki satuan yang sesuai.
- Tidak menghasilkan nilai konsumsi negatif.
- Menyimpan versi model yang digunakan.
- Dapat ditelusuri ke dataset dan konfigurasi model.

Prediksi ANN merupakan rekomendasi perencanaan stok, bukan instruksi pembelian otomatis.

## 7. Security Testing

Pengujian keamanan minimal mencakup:

- Authentication.
- Authorization dan permission.
- CSRF protection.
- Validasi input.
- SQL injection prevention.
- XSS prevention.
- Session handling.
- Proteksi akses file dan laporan.
- Pembatasan akses endpoint internal Python Service.
- Penyimpanan credential dan API key melalui environment configuration.

Tidak boleh ada password, API key, atau credential sensitif yang ditulis langsung di source code atau repository.

## 8. Performance Testing

Pengujian performa dilakukan untuk memastikan aplikasi tetap responsif dalam penggunaan operasional.

Area pengujian:

- Waktu membuka dashboard.
- Waktu memuat daftar produk.
- Waktu pencarian produk.
- Waktu menyelesaikan transaksi.
- Waktu memuat laporan.
- Waktu mengambil hasil prediksi ANN.

Target performa numerik belum ditetapkan. Target akan ditentukan setelah baseline pengukuran pada perangkat dan lingkungan deployment yang digunakan.

Pengujian performa tidak boleh mengorbankan konsistensi transaksi dan integritas data.

## 9. Compatibility Testing

Perangkat yang menjadi fokus:

| Perangkat | Fokus |
|---|---|
| Desktop / Laptop | Dashboard, manajemen, laporan |
| Tablet | Antarmuka POS dan operasional |
| Smartphone | Tampilan responsif dan akses karyawan |
| Thermal Printer | Pencetakan struk |
| PDF Reader | Pembukaan dan pencetakan dokumen |

Sistem operasi tablet, browser, dan model printer masih perlu dikonfirmasi. Pengujian kompatibilitas perangkat fisik dilakukan setelah spesifikasi perangkat tersedia.

## 10. Test Environment

Pengujian harus menggunakan lingkungan terpisah dari produksi.

| Environment | Kegunaan |
|---|---|
| Local Development | Pengembangan dan pengujian awal |
| Testing | Pengujian otomatis dan manual |
| Staging | Simulasi penggunaan sebelum deployment |
| Production | Operasional sebenarnya |

Aturan:

1. Database testing harus terpisah dari database produksi.
2. Data uji tidak boleh bercampur dengan transaksi nyata.
3. Credential produksi tidak boleh digunakan pada local testing.
4. Pengujian migrasi dilakukan sebelum diterapkan ke produksi.
5. Integrasi eksternal dapat menggunakan mock atau test response ketika diperlukan.

## 11. Testing Tools

| Tools | Kegunaan | Status |
|---|---|---|
| PHPUnit / Pest | Unit dan feature testing Laravel | Mengikuti setup proyek |
| Laravel HTTP Testing | Pengujian request dan response | Mengikuti setup proyek |
| Laravel Database Testing | Pengujian database | Mengikuti setup proyek |
| Python unittest / pytest | Pengujian Python ANN Service | Pilihan implementasi |
| Browser DevTools | Pemeriksaan UI dan responsivitas | Digunakan |
| Manual Device Testing | Pengujian tablet dan printer fisik | Diperlukan |

Tidak perlu menambahkan seluruh tools sekaligus. Gunakan tools yang sudah tersedia dan tambahkan dependency hanya jika ada kebutuhan nyata.

## 12. Test Execution Workflow

Urutan pengujian setiap fitur:

1. Memahami acceptance criteria fitur.
2. Menentukan skenario pengujian.
3. Menyiapkan data uji.
4. Menjalankan unit test.
5. Menjalankan feature test.
6. Menjalankan integration test jika terdapat integrasi.
7. Melakukan UI atau manual testing.
8. Memperbaiki bug yang ditemukan.
9. Menjalankan regression test.
10. Memperbarui dokumentasi dan progress.

### Test Status

| Status | Keterangan |
|---|---|
| Not Tested | Belum diuji |
| Passed | Seluruh kriteria pengujian terpenuhi |
| Failed | Terdapat hasil yang tidak sesuai |
| Blocked | Belum dapat diuji karena dependensi atau perangkat belum tersedia |
| Retest Required | Perlu diuji ulang setelah perbaikan |

## 13. Bug Reporting

Setiap bug harus memiliki informasi:

- Bug ID.
- Modul terkait.
- Deskripsi masalah.
- Langkah reproduksi.
- Expected result.
- Actual result.
- Tingkat prioritas.
- Screenshot atau log jika tersedia.
- Status perbaikan.

### Bug Severity

| Level | Keterangan |
|---|---|
| Critical | Transaksi gagal, kehilangan data, atau akses tidak sah |
| High | Fungsi utama tidak berjalan dengan benar |
| Medium | Fungsi berjalan tetapi terdapat kesalahan yang mengganggu |
| Low | Masalah visual atau minor |

## 14. Definition of Done (DoD)

Sebuah fitur dinyatakan selesai apabila:

- Implementasi sesuai spesifikasi.
- Validasi input tersedia.
- Authorization diterapkan pada backend.
- Unit atau feature test yang relevan lulus.
- Integration test lulus jika diperlukan.
- Tidak terdapat bug Critical atau High yang belum diselesaikan.
- UI telah diperiksa pada perangkat target.
- Error handling tersedia.
- Dokumentasi diperbarui.
- Tidak merusak fitur yang sudah berjalan.

Fitur yang membutuhkan perangkat eksternal tetapi belum dapat diuji secara fisik harus diberi status Blocked atau Partially Verified, bukan dianggap sepenuhnya lulus.

## 15. Regression Checklist

Checklist ini digunakan sebelum rilis atau setelah perubahan yang berpotensi memengaruhi modul lain.

- [ ] Login dan logout berjalan.
- [ ] Role dan permission berjalan.
- [ ] Produk dapat dikelola.
- [ ] Bahan baku dapat dikelola.
- [ ] Resep dan BOM berjalan.
- [ ] Transaksi berhasil disimpan.
- [ ] Perhitungan total transaksi benar.
- [ ] Stok berkurang sesuai BOM.
- [ ] Kegagalan transaksi tidak menghasilkan stok parsial.
- [ ] Pembatalan dan reversal tercatat.
- [ ] Laporan sesuai transaksi.
- [ ] Absensi berjalan sesuai aturan.
- [ ] ANN Service dapat diakses.
- [ ] POS tetap berjalan ketika ANN Service gagal.
- [ ] Data cuaca ditangani dengan benar.
- [ ] PDF dapat dibuat.
- [ ] Printer thermal diuji pada perangkat aktual.

## 16. Open Items

Hal berikut belum dapat ditetapkan secara final:

| Item | Status |
|---|---|
| Target response time aplikasi | TBD |
| Target jumlah pengguna bersamaan | TBD |
| Browser dan OS tablet | TBD |
| Merek dan tipe printer thermal | TBD |
| Jenis koneksi printer | TBD |
| Protokol cash drawer | TBD |
| Target test coverage | TBD |
| Automation testing untuk browser | TBD |

Item tersebut akan diperbarui setelah kebutuhan operasional dan perangkat aktual diketahui.

## 17. Referensi

- Laravel 12 Documentation — Testing: https://laravel.com/docs/12.x/testing
- Laravel 12 Documentation — Database Testing: https://laravel.com/docs/12.x/database-testing

---

**End of Document — TESTING_STRATEGY.md**