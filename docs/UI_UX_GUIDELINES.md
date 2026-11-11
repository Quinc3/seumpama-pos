# UI_UX_GUIDELINES.md

# SEUMPAMA POS
**UI/UX Design System & Interface Guidelines**

**Status:** Approved Design Direction  
**Project:** Seumpama POS  
**Frontend:** Laravel Blade  
**Styling:** Tailwind CSS  
**Interaction:** Alpine.js  
**Primary Device:** Tablet (POS)  
**Supported Devices:** Desktop, Tablet, Mobile

---

# 1. DESIGN OVERVIEW

## 1.1 Design Objectives

Antarmuka Seumpama POS harus dirancang untuk mendukung kegiatan operasional Coffee Shop Seumpama Bunga.

Tujuan desain:

1. Mempermudah pengguna menjalankan aktivitas operasional.
2. Mempercepat proses transaksi kasir.
3. Mempermudah pemantauan persediaan bahan baku.
4. Menyajikan informasi secara jelas dan terstruktur.
5. Mengurangi kesalahan interaksi pengguna.
6. Memberikan pengalaman penggunaan yang konsisten.
7. Mendukung berbagai ukuran perangkat.

Desain harus mengutamakan kegunaan dan kejelasan informasi, bukan sekadar tampilan visual.

## 1.2 Design Philosophy

Prinsip desain:

- Clean and Modern.
- Professional.
- Human-centered.
- Consistent.
- Responsive.
- Accessible.
- Functional over Decorative.
- Minimal Visual Clutter.

Hindari tampilan yang terlalu ramai, penggunaan dekorasi berlebihan, dan komponen yang tidak memberikan manfaat terhadap pengguna.

## 1.3 Design Priorities

Urutan prioritas:

1. Usability.
2. Information Clarity.
3. Operational Efficiency.
4. Visual Consistency.
5. Accessibility.
6. Visual Aesthetics.

---

# 2. DESIGN DIRECTION

## 2.1 Visual Identity

Seumpama POS menggunakan gaya visual profesional dengan pendekatan modern business application.

Karakteristik:

- Layout bersih.
- Hierarki informasi jelas.
- Komponen sederhana tetapi berkualitas.
- Penggunaan whitespace yang cukup.
- Tipografi mudah dibaca.
- Warna tidak berlebihan.
- Konsistensi antarhalaman.

## 2.2 Primary Color

Warna utama menggunakan aksen biru.

| Token | Warna | Penggunaan |
|---|---|---|
| Primary | Blue 600 | Tombol utama dan aksi penting |
| Primary Hover | Blue 700 | Hover tombol utama |
| Primary Light | Blue 50 | Highlight dan selected state |
| Primary Border | Blue 200 | Border komponen aktif |
| Background | Slate 50 | Latar belakang aplikasi |
| Surface | White | Card dan panel |
| Text Primary | Slate 900 | Teks utama |
| Text Secondary | Slate 500 | Informasi sekunder |
| Border | Slate 200 | Garis pembatas |

## 2.3 Semantic Colors

| Status | Warna | Penggunaan |
|---|---|---|
| Success | Green | Berhasil, stok tersedia |
| Warning | Amber | Peringatan dan stok menipis |
| Danger | Red | Kesalahan dan stok kritis |
| Info | Blue | Informasi dan pemberitahuan |
| Neutral | Slate | Status umum |

Warna semantik harus digunakan secara konsisten.

Jangan menggunakan warna hanya sebagai dekorasi tanpa makna.

## 2.4 Color Usage Rules

- Biru menjadi warna identitas utama.
- Background menggunakan warna netral.
- Putih digunakan untuk surface utama.
- Warna merah hanya untuk kondisi yang membutuhkan perhatian.
- Warna hijau digunakan untuk status berhasil.
- Warna amber digunakan untuk peringatan.
- Hindari penggunaan terlalu banyak warna dalam satu halaman.

## 2.5 Typography

Gunakan tipografi sans-serif yang mudah dibaca.

Rekomendasi awal:

- Inter sebagai font utama.
- Font fallback menggunakan system sans-serif.

Hierarki:

| Element | Style |
|---|---|
| Page Title | 24–30px |
| Section Heading | 18–20px |
| Card Heading | 16px |
| Body Text | 14–16px |
| Supporting Text | 12–14px |
| Table Text | 13–14px |

Ukuran akhir harus disesuaikan dengan perangkat dan kepadatan informasi.

Angka transaksi, harga, dan stok harus mudah dibaca.

---

# 3. RESPONSIVE DESIGN

## 3.1 Supported Devices

Sistem mendukung:

| Device | Primary Usage |
|---|---|
| Desktop | Administrasi dan analisis |
| Tablet | Operasional POS |
| Smartphone | Monitoring dan akses operasional |

Tablet merupakan perangkat utama untuk kegiatan kasir.

## 3.2 Responsive Strategy

Gunakan pendekatan mobile-first dengan Tailwind CSS.

Layout harus menyesuaikan karakteristik perangkat, bukan sekadar mengecilkan seluruh elemen desktop.

Breakpoints mengikuti konfigurasi Tailwind yang digunakan dan dapat disesuaikan berdasarkan hasil pengujian perangkat aktual.

## 3.3 Desktop Layout

Karakteristik:

- Sidebar navigation.
- Topbar.
- Area konten utama.
- Dashboard multi-column.
- Tabel dengan informasi lengkap.
- Form dengan layout yang terstruktur.

Desktop diutamakan untuk pengelolaan data dan analisis.

## 3.4 Tablet Layout

Karakteristik:

- Navigasi ringkas.
- Komponen mudah disentuh.
- POS menggunakan layout yang optimal untuk tablet.
- Tombol memiliki ukuran sentuh yang nyaman.
- Form tidak terlalu padat.
- Informasi penting tetap terlihat tanpa scrolling berlebihan.

## 3.5 Mobile Layout

Karakteristik:

- Navigasi drawer atau pola navigasi ringkas.
- Konten menggunakan single-column layout jika sesuai.
- Tabel dapat berubah menjadi card-based presentation.
- Form disusun vertikal.
- Tombol utama mudah dijangkau.
- Informasi penting tidak boleh tersembunyi.

## 3.6 Responsive Testing

Setiap halaman harus diuji pada:

- Desktop.
- Tablet portrait.
- Tablet landscape.
- Smartphone portrait.
- Smartphone landscape jika relevan.

Tidak boleh ada:

- Horizontal overflow yang tidak disengaja.
- Tombol saling bertumpuk.
- Teks terpotong.
- Form yang sulit digunakan.
- Modal yang keluar dari layar.
- Elemen interaktif yang terlalu kecil.

---

# 4. APPLICATION LAYOUT

## 4.1 Main Layout Structure

Struktur utama aplikasi:

- Sidebar.
- Topbar.
- Main content.
- Page header.
- Content section.
- Notification area.

Layout harus menggunakan reusable Blade components.

## 4.2 Sidebar Navigation

Sidebar berisi navigasi utama.

Struktur konseptual:

Dashboard

OPERATIONS
- Point of Sale
- Transactions
- Products
- Categories
- Recipes / BOM

INVENTORY
- Ingredients
- Stock Overview
- Stock Movement
- Stock Opname

PROCUREMENT
- Suppliers
- Purchasing

ANALYTICS
- Sales Reports
- Inventory Reports
- Weather Data
- Dataset
- ANN Prediction
- Stock Forecasting

MANAGEMENT
- Customers
- Promotions
- Expenses
- Users & Roles

SYSTEM
- Settings

Menu harus ditampilkan berdasarkan permission pengguna.

Menu yang tidak dapat diakses tidak boleh ditampilkan sebagai navigasi aktif.

## 4.3 Topbar

Topbar menampilkan:

- Page context jika diperlukan.
- Notification.
- User profile.
- Role atau informasi akun.
- Logout melalui menu akun.

Jangan memenuhi topbar dengan informasi yang tidak diperlukan.

## 4.4 Page Header

Setiap halaman administrasi menggunakan struktur:

- Page title.
- Short description.
- Breadcrumb jika diperlukan.
- Primary action.
- Secondary actions jika diperlukan.

Contoh:

Ingredients

Manage raw materials, stock levels, and inventory information.

[Add Ingredient]

---

# 5. DESIGN SYSTEM COMPONENTS

Semua halaman harus menggunakan komponen UI yang konsisten.

## 5.1 Buttons

Jenis tombol:

| Variant | Usage |
|---|---|
| Primary | Aksi utama |
| Secondary | Aksi alternatif |
| Outline | Aksi sekunder |
| Ghost | Aksi ringan |
| Danger | Aksi berisiko |
| Icon Button | Aksi berbasis ikon |

Aturan:

- Gunakan label yang jelas.
- Hindari tombol dengan ikon saja tanpa tooltip atau accessible label.
- Tampilkan loading state ketika proses berjalan.
- Nonaktifkan tombol ketika aksi tidak dapat dilakukan.
- Gunakan confirmation dialog untuk tindakan destruktif.

## 5.2 Cards

Card digunakan untuk:

- Ringkasan KPI.
- Informasi produk.
- Informasi stok.
- Ringkasan prediksi.
- Konten dashboard.

Card harus memiliki hierarki informasi yang jelas.

Hindari terlalu banyak card yang tidak memberikan nilai informasi.

## 5.3 Tables

Tabel digunakan untuk informasi yang membutuhkan perbandingan data.

Ketentuan:

- Header kolom jelas.
- Alignment angka konsisten.
- Mendukung pencarian jika relevan.
- Mendukung filter jika relevan.
- Pagination untuk data besar.
- Empty state.
- Loading state.
- Responsive presentation.

Jangan memaksakan seluruh tabel desktop ke layar smartphone.

## 5.4 Forms

Form harus:

- Memiliki label yang jelas.
- Memiliki placeholder yang membantu.
- Menampilkan validasi.
- Menjelaskan field wajib.
- Mengelompokkan field yang berkaitan.
- Menampilkan pesan error di dekat field terkait.

Hindari form panjang tanpa pengelompokan.

## 5.5 Modal and Dialog

Gunakan modal untuk interaksi singkat seperti:

- Konfirmasi.
- Tambah data sederhana.
- Edit informasi sederhana.
- Detail ringkas.

Gunakan halaman tersendiri untuk form kompleks.

Dialog destruktif harus menjelaskan dampak tindakan.

## 5.6 Badges

Badge digunakan untuk status seperti:

- Active.
- Inactive.
- Paid.
- Pending.
- Cancelled.
- Available.
- Low Stock.
- Critical Stock.

Warna badge harus konsisten dengan semantic color system.

## 5.7 Notifications

Jenis notifikasi:

- Success.
- Error.
- Warning.
- Information.

Notifikasi harus memberikan informasi yang jelas tentang hasil tindakan.

Hindari pesan teknis yang tidak dipahami pengguna operasional.

---

# 6. POS INTERFACE DESIGN

## 6.1 POS Design Objectives

POS merupakan antarmuka paling penting untuk kegiatan kasir.

Prioritas desain:

1. Kecepatan transaksi.
2. Kemudahan pemilihan produk.
3. Kejelasan keranjang.
4. Kemudahan pembayaran.
5. Minimalisasi kesalahan input.
6. Kemudahan pencetakan struk.

## 6.2 POS Desktop Layout

Layout konseptual:

+-----------------------------------------------+
| Topbar / Session Information                  |
+-------------------------------+---------------+
| Product Search                |               |
| Category Filter               |   Cart        |
|                               |               |
| Product Grid                  |   Order Items |
|                               |               |
|                               |   Subtotal    |
|                               |   Discount    |
|                               |   Total       |
|                               |               |
|                               |   Checkout    |
+-------------------------------+---------------+

Katalog produk berada di sisi kiri.

Keranjang dan ringkasan pembayaran berada di sisi kanan.

## 6.3 POS Tablet Layout

Tablet menggunakan layout yang disesuaikan dengan orientasi layar.

Landscape:
- Katalog produk dan keranjang ditampilkan berdampingan.
- Keranjang tetap mudah diakses.
- Checkout selalu terlihat.

Portrait:
- Katalog menjadi area utama.
- Keranjang dapat menggunakan drawer atau panel terpisah.
- Ringkasan total dan tombol checkout mudah diakses.

## 6.4 POS Mobile Layout

Mobile menggunakan:

- Product grid.
- Search.
- Category filter.
- Cart drawer atau bottom sheet.
- Checkout action yang mudah dijangkau.

Jangan memaksakan layout desktop pada smartphone.

## 6.5 Product Card

Informasi produk:

- Nama produk.
- Harga.
- Gambar jika tersedia.
- Status ketersediaan.

Product card harus mudah dipilih melalui layar sentuh.

Produk yang tidak tersedia harus memiliki visual status yang jelas dan tidak dapat ditambahkan ke keranjang.

## 6.6 Cart Interface

Keranjang menampilkan:

- Nama produk.
- Kuantitas.
- Harga satuan.
- Subtotal item.
- Catatan pesanan jika digunakan.
- Total transaksi.

Pengguna dapat:

- Menambah jumlah.
- Mengurangi jumlah.
- Menghapus item.
- Mengubah catatan.

Perubahan keranjang harus memberikan respons visual yang jelas.

## 6.7 Checkout Interface

Checkout menampilkan:

- Subtotal.
- Diskon jika tersedia.
- Total pembayaran.
- Metode pembayaran.
- Nominal uang diterima untuk pembayaran tunai.
- Uang kembalian.

Metode pembayaran awal menggunakan pencatatan manual.

Jangan menampilkan integrasi payment gateway sebagai fitur aktif sebelum benar-benar diimplementasikan.

## 6.8 Transaction Feedback

Setelah transaksi berhasil:

- Tampilkan konfirmasi.
- Tampilkan nomor transaksi.
- Sediakan pilihan cetak struk thermal.
- Sediakan pilihan menyimpan atau membuka struk PDF.
- Sediakan tindakan transaksi baru.

Jika pencetakan gagal, transaksi yang sudah berhasil tidak boleh dianggap gagal atau diulang secara otomatis.

---

# 7. ADMIN DASHBOARD

## 7.1 Dashboard Objectives

Dashboard menyediakan gambaran kondisi operasional coffee shop.

Informasi harus relevan dengan kebutuhan owner dan admin.

## 7.2 KPI Cards

KPI yang direncanakan:

- Total Sales.
- Total Transactions.
- Average Transaction Value.
- Low Stock Ingredients.
- Critical Stock Ingredients.
- Daily Ingredient Consumption.

KPI harus menggunakan periode dan konteks waktu yang jelas.

## 7.3 Charts

Visualisasi yang dapat digunakan:

- Sales Trend.
- Top Selling Products.
- Ingredient Consumption.
- Stock Movement.
- Actual vs Predicted Demand.

Gunakan grafik hanya jika dapat membantu pengguna memahami informasi.

Jangan menggunakan grafik dekoratif tanpa tujuan analitis.

## 7.4 Dashboard Filtering

Dashboard dapat menyediakan filter:

- Hari ini.
- Minggu ini.
- Bulan ini.
- Rentang tanggal tertentu.

Semua data harus menggunakan definisi hari operasional yang konsisten.

---

# 8. INVENTORY INTERFACE

## 8.1 Ingredient Overview

Informasi utama:

- Nama bahan baku.
- Kategori.
- Stok aktual.
- Satuan.
- Minimum stock.
- Safety stock.
- Status persediaan.

## 8.2 Stock Status

Status persediaan:

- Available.
- Low Stock.
- Critical Stock.
- Out of Stock.

Status harus dihitung berdasarkan aturan inventaris yang berlaku, bukan hanya warna visual.

## 8.3 Ingredient Form

Field yang direncanakan:

- Ingredient name.
- Ingredient category.
- Unit.
- Current stock.
- Minimum stock.
- Lead time.
- Core ingredient classification.
- ANN prediction eligibility.
- Supplier association jika diperlukan.

Field harus menyesuaikan kebutuhan data yang telah ditentukan dalam spesifikasi sistem.

## 8.4 Stock Movement Interface

Tampilkan:

- Tanggal.
- Bahan baku.
- Jenis pergerakan.
- Kuantitas.
- Stok sebelum.
- Stok sesudah.
- Referensi transaksi.
- Pengguna.
- Keterangan.

Informasi harus dapat ditelusuri.

---

# 9. ANN AND FORECASTING INTERFACE

## 9.1 Forecasting Dashboard

Halaman prediksi menampilkan:

- Bahan baku target.
- Periode prediksi.
- Predicted demand.
- Actual demand jika tersedia.
- Prediction history.
- Model version.
- Evaluation metrics.

## 9.2 Prediction Visualization

Visualisasi dapat mencakup:

- Actual vs Predicted Demand.
- Historical Consumption.
- Forecast Trend.
- Prediction Error.

Grafik harus memiliki label, satuan, dan periode yang jelas.

## 9.3 Model Information

Tampilkan informasi:

- Model version.
- Dataset version.
- Training date.
- MAE.
- RMSE.
- Model status.

Informasi teknis tidak boleh dicampur secara berlebihan dengan tampilan operasional kasir.

## 9.4 Procurement Recommendation

Informasi rekomendasi:

- Current stock.
- Predicted demand.
- Safety stock.
- Reorder point.
- Lead time.
- Estimated stock coverage.
- Recommended purchase quantity.

Rekomendasi harus mudah dipahami owner atau admin.

Tidak boleh membuat pengguna mengira bahwa prediksi ANN merupakan angka stok aktual.

---

# 10. REPORTING INTERFACE

## 10.1 Report Categories

Laporan meliputi:

- Sales Report.
- Transaction Report.
- Product Performance Report.
- Ingredient Consumption Report.
- Inventory Report.
- Stock Movement Report.
- Purchasing Report.
- Expense Report.
- ANN Prediction Report.
- Model Evaluation Report.
- Procurement Recommendation Report.

## 10.2 Report Layout

Setiap laporan harus memiliki:

- Judul laporan.
- Periode.
- Tanggal pembuatan.
- Ringkasan informasi.
- Tabel atau grafik jika diperlukan.
- Keterangan satuan.
- Informasi sumber data jika relevan.

## 10.3 Export Interface

Sistem menyediakan:

- PDF untuk dokumentasi dan pencetakan.
- XLSX atau format spreadsheet jika diperlukan untuk analisis data.

PDF harus memiliki layout yang sesuai untuk dokumen, bukan sekadar screenshot halaman aplikasi.

Struk transaksi dan laporan operasional harus memiliki template yang berbeda.

---

# 11. PRINTER AND RECEIPT INTERFACE

## 11.1 Printer Output

Sistem menyediakan dua jalur output:

1. Thermal printer.
2. PDF.

## 11.2 Thermal Receipt

Struk thermal harus menampilkan:

- Nama usaha.
- Informasi transaksi.
- Nomor transaksi.
- Tanggal dan waktu.
- Daftar produk.
- Kuantitas.
- Harga.
- Subtotal.
- Diskon jika tersedia.
- Total pembayaran.
- Metode pembayaran.
- Nominal diterima dan kembalian jika tunai.

## 11.3 PDF Receipt

Struk PDF harus mempertahankan informasi transaksi yang sama dengan struk thermal.

Ukuran dan format final menyesuaikan kebutuhan printer dan dokumentasi digital.

## 11.4 Cash Drawer

Cash drawer yang digunakan saat ini terhubung ke thermal printer.

Antarmuka dapat menyediakan pengaturan terkait pencetakan dan pembukaan drawer apabila perangkat mendukungnya.

Mekanisme aktual menunggu identifikasi merek, model, koneksi, dan protokol printer.

---

# 12. ACCESSIBILITY AND USABILITY

## 12.1 Accessibility

Perhatikan:

- Kontras teks dan background.
- Ukuran font.
- Label input.
- Navigasi keyboard.
- Focus indicator.
- Accessible name pada tombol.
- Struktur heading.
- Informasi tidak hanya dibedakan melalui warna.

## 12.2 Touch Interaction

Karena tablet menjadi perangkat utama kasir:

- Target sentuh harus nyaman digunakan.
- Jarak antartombol harus memadai.
- Hindari kontrol yang terlalu kecil.
- Tombol aksi utama harus mudah ditemukan.
- Interaksi tidak boleh mengandalkan hover saja.

## 12.3 Error Prevention

Desain harus mengurangi kemungkinan kesalahan.

Contoh:

- Konfirmasi sebelum membatalkan transaksi.
- Konfirmasi sebelum menghapus data.
- Validasi jumlah pembayaran.
- Peringatan ketika stok tidak mencukupi.
- Pesan kesalahan yang spesifik.
- Pencegahan double submission.

---

# 13. UI STATES

Setiap komponen interaktif harus memiliki state yang sesuai.

## 13.1 Loading State

Tampilkan ketika sistem sedang mengambil atau memproses data.

## 13.2 Empty State

Tampilkan ketika belum ada data.

Sediakan penjelasan dan tindakan berikutnya jika relevan.

## 13.3 Error State

Tampilkan ketika terjadi kegagalan.

Pesan harus menjelaskan masalah dengan bahasa yang dapat dipahami pengguna.

## 13.4 Success State

Tampilkan setelah tindakan berhasil.

## 13.5 Disabled State

Tampilkan ketika suatu aksi tidak dapat digunakan.

Berikan alasan apabila status disabled dapat membingungkan pengguna.

## 13.6 Confirmation State

Gunakan konfirmasi untuk tindakan yang berdampak besar atau tidak mudah dibatalkan.

---

# 14. COMPONENT REUSABILITY

Gunakan reusable Blade components untuk elemen yang digunakan berulang.

Contoh konseptual:

resources/views/components/
- button.blade.php
- input.blade.php
- select.blade.php
- textarea.blade.php
- card.blade.php
- badge.blade.php
- modal.blade.php
- alert.blade.php
- table.blade.php
- pagination.blade.php
- page-header.blade.php
- empty-state.blade.php
- loading-state.blade.php

Struktur akhir menyesuaikan kebutuhan dan konvensi proyek.

Jangan membuat komponen untuk setiap elemen kecil jika tidak memberikan manfaat nyata.

Komponen harus memiliki:

- Tanggung jawab yang jelas.
- API penggunaan yang konsisten.
- Styling yang seragam.
- Dukungan responsive jika relevan.

---

# 15. ICONOGRAPHY AND VISUAL ASSETS

## 15.1 Icon Library

Gunakan satu library ikon secara konsisten.

Rekomendasi: Lucide Icons.

Jangan mencampur banyak gaya ikon dalam satu aplikasi.

## 15.2 Icon Usage

Ikon digunakan untuk:

- Navigasi.
- Aksi.
- Status.
- Informasi.
- Kontrol antarmuka.

Ikon harus mudah dikenali.

Hindari penggunaan emoji sebagai pengganti ikon antarmuka.

## 15.3 Images

Gambar produk harus:

- Memiliki kualitas memadai.
- Menggunakan rasio konsisten.
- Tidak merusak layout.
- Memiliki fallback ketika tidak tersedia.

Gambar tidak boleh menjadi elemen wajib apabila tidak diperlukan untuk operasional.

---

# 16. ANTI-SLOP AND HUMAN-CENTERED DESIGN RULES

Antarmuka tidak boleh terlihat seperti hasil generasi otomatis yang tidak melalui proses desain.

Ketentuan:

1. Hindari layout dashboard generik yang dipenuhi card tanpa tujuan.
2. Hindari gradient berlebihan.
3. Hindari penggunaan shadow pada setiap elemen.
4. Hindari border dan divider yang tidak diperlukan.
5. Hindari dekorasi yang mengganggu keterbacaan.
6. Hindari ukuran heading yang tidak proporsional.
7. Hindari terlalu banyak warna.
8. Hindari ikon yang tidak konsisten.
9. Hindari tombol dengan label ambigu.
10. Hindari penggunaan animasi yang tidak memberikan manfaat.
11. Hindari tabel yang terlalu padat tanpa hierarki informasi.
12. Hindari penggunaan modal untuk setiap tindakan.
13. Hindari komponen yang hanya mempercantik tampilan tetapi tidak membantu pekerjaan.
14. Jangan meniru template UI secara mentah.
15. Setiap halaman harus dirancang berdasarkan kebutuhan penggunanya.

Prioritaskan pengalaman penggunaan yang alami, efisien, dan konsisten.

---

# 17. UI PERFORMANCE

Antarmuka harus tetap responsif pada perangkat dengan kemampuan terbatas.

Ketentuan:

- Hindari JavaScript yang tidak diperlukan.
- Hindari library UI berukuran besar tanpa alasan.
- Optimalkan ukuran gambar.
- Gunakan pagination untuk data besar.
- Hindari memuat seluruh data sekaligus.
- Minimalkan animasi berat.
- Hindari rendering ulang yang tidak diperlukan.
- Gunakan lazy loading untuk konten yang sesuai.

Performa antarmuka harus diperiksa pada tablet kasir.

---

# 18. UI/UX IMPLEMENTATION RULES FOR AI

DeepSeek atau AI coding assistant wajib mengikuti ketentuan berikut:

1. Baca dokumen ini sebelum membuat atau mengubah antarmuka.
2. Periksa komponen yang sudah tersedia sebelum membuat komponen baru.
3. Gunakan design system yang sama pada seluruh modul.
4. Jangan mengubah warna utama tanpa persetujuan.
5. Jangan membuat layout berbeda tanpa alasan UX yang jelas.
6. Jangan menghilangkan fitur yang sudah berjalan demi perubahan visual.
7. Jangan mengubah business logic ketika hanya diminta memperbaiki UI.
8. Jangan menambahkan dependency UI tanpa alasan teknis.
9. Pastikan tampilan tablet menjadi prioritas untuk modul POS.
10. Uji responsive layout sebelum menyatakan pekerjaan selesai.
11. Pastikan seluruh tombol dan kontrol benar-benar berfungsi.
12. Jangan menggunakan data palsu sebagai data operasional aktual.
13. Pertahankan konsistensi komponen.
14. Jangan melakukan redesign seluruh aplikasi ketika hanya diminta mengubah satu bagian.
15. Laporkan bagian yang belum dapat diuji.

Setiap perubahan UI harus menjaga integritas fungsi aplikasi.

---

# 19. UI/UX ACCEPTANCE CRITERIA

Implementasi UI dianggap memenuhi pedoman apabila:

- [ ] Identitas visual konsisten.
- [ ] Aksen biru digunakan secara konsisten.
- [ ] Tipografi mudah dibaca.
- [ ] Navigasi mudah dipahami.
- [ ] Desktop berfungsi dengan baik.
- [ ] Tablet berfungsi dengan baik.
- [ ] Mobile berfungsi dengan baik.
- [ ] POS nyaman digunakan melalui tablet.
- [ ] Tombol dan form memiliki state yang jelas.
- [ ] Tabel memiliki tampilan responsive.
- [ ] Pesan error dapat dipahami.
- [ ] Loading dan empty state tersedia.
- [ ] Dialog tindakan penting memiliki konfirmasi.
- [ ] Tidak terdapat horizontal overflow yang tidak disengaja.
- [ ] Tidak terdapat komponen dekoratif berlebihan.
- [ ] Seluruh interaksi utama telah diuji.
- [ ] Tidak ada regresi fungsi akibat perubahan UI.

---

# 20. FINAL DESIGN PRINCIPLES

Seumpama POS harus memiliki antarmuka yang:

- Profesional.
- Modern.
- Bersih.
- Konsisten.
- Responsif.
- Mudah digunakan.
- Efisien untuk operasional.
- Nyaman digunakan pada tablet.
- Memiliki identitas visual biru.
- Tidak berlebihan secara visual.

Prinsip utama:

**Desain yang baik bukan desain yang paling ramai atau paling dekoratif, melainkan desain yang membantu pengguna menyelesaikan pekerjaan dengan lebih cepat, tepat, dan nyaman.**

END OF UI_UX_GUIDELINES.md