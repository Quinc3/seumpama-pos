# DATABASE_DESIGN.md

# DATABASE DESIGN — SEUMPAMA POS

## 1. DOCUMENT OVERVIEW

### 1.1 Purpose

Dokumen ini mendefinisikan rancangan struktur basis data untuk aplikasi Seumpama POS.

Database dirancang untuk mendukung seluruh kebutuhan sistem, meliputi:

- Point of Sale (POS).
- Product and Category Management.
- Ingredient Management.
- Recipe / Bill of Materials (BOM).
- Inventory Management.
- Stock Movement.
- Supplier Management.
- Purchasing and Procurement.
- Sales and Payment.
- Customer Management.
- Discount and Promotion.
- Expense Management.
- Reporting and Analytics.
- Weather Data Integration.
- Dataset Management.
- Artificial Neural Network (ANN).
- Stock Forecasting.
- Safety Stock and Reorder Point.
- User, Role, and Permission Management.
- Attendance Management (optional).
- System Configuration.

Database harus menjaga konsistensi data transaksi dan persediaan serta menyediakan histori yang dapat digunakan untuk pembentukan dataset penelitian.

### 1.2 Database Design Principles

1. **Data Integrity:** Setiap relasi penting harus memiliki foreign key dan constraint yang sesuai.
2. **Normalization:** Data operasional dipisahkan berdasarkan tanggung jawab entitas.
3. **Historical Tracking:** Perubahan stok, resep, transaksi, dan model ANN harus memiliki histori.
4. **Modular Structure:** Tabel setiap modul memiliki batas tanggung jawab yang jelas.
5. **Research Integrity:** Data aktual, data hasil perhitungan, data simulasi, dan hasil prediksi tidak boleh tercampur.
6. **Scalability:** Struktur database memungkinkan penambahan fitur tanpa mengubah seluruh sistem.
7. **Transactional Consistency:** Proses transaksi penjualan dan pengurangan stok harus konsisten.
8. **Auditability:** Aktivitas penting dapat ditelusuri berdasarkan pengguna dan waktu.

---

## 2. DATABASE TECHNOLOGY

### 2.1 Recommended Database

- Database Engine: MySQL 8+ atau MariaDB yang kompatibel.
- ORM: Laravel Eloquent.
- Schema Management: Laravel Migrations.
- Database Access: Laravel sebagai pemilik utama akses database.
- Python Service: Mengakses data ANN melalui antarmuka yang ditentukan Laravel.

Versi database final mengikuti hasil pemeriksaan environment sebelum implementasi.

### 2.2 Data Type Standards

| Data | Recommended Type |
|---|---|
| Primary Key | BIGINT UNSIGNED |
| Foreign Key | BIGINT UNSIGNED |
| Money | DECIMAL(15,2) |
| Ingredient Quantity | DECIMAL(15,4) |
| Temperature | DECIMAL(7,3) |
| Rainfall | DECIMAL(10,3) |
| Percentage | DECIMAL(8,4) |
| Date | DATE |
| Time | TIME |
| Timestamp | DATETIME |
| Boolean | BOOLEAN |
| Configuration | JSON where appropriate |

Ketentuan:

- Jangan menggunakan FLOAT untuk nominal uang.
- Kuantitas bahan baku menggunakan DECIMAL agar presisi perhitungan terjaga.
- Semua timestamp operasional menggunakan zona waktu Asia/Jakarta.
- Primary key dan foreign key harus memiliki tipe yang sama.
- Kolom yang sering digunakan untuk pencarian dan relasi harus memiliki indeks.

### 2.3 Naming Convention

- Nama tabel menggunakan bentuk plural dan snake_case.
- Nama kolom menggunakan snake_case.
- Primary key menggunakan `id`.
- Foreign key menggunakan format `{entity}_id`.
- Tabel pivot menggunakan nama relasi yang jelas.
- Timestamp standar menggunakan `created_at` dan `updated_at`.
- Penghapusan logis menggunakan `deleted_at` hanya pada entitas yang memang memerlukan soft delete.

---

## 3. DATABASE DOMAIN STRUCTURE

Database dibagi menjadi beberapa kelompok berdasarkan domain bisnis.

| Domain | Responsibility |
|---|---|
| Core & Access | Pengguna, role, permission, konfigurasi |
| Product | Produk menu dan kategori |
| Inventory | Bahan baku, satuan, pergerakan stok |
| Recipe | Komposisi bahan baku |
| POS & Sales | Keranjang, transaksi, pembayaran |
| Customer | Data pelanggan |
| Procurement | Supplier, pembelian, penerimaan barang |
| Promotion | Diskon dan promosi |
| Expense | Pengeluaran operasional |
| Weather | Pengumpulan data cuaca |
| Dataset | Pengolahan dan penyimpanan dataset |
| ANN | Training, model, evaluasi, prediksi |
| Forecasting | Safety stock dan rekomendasi pengadaan |
| Attendance | Kehadiran karyawan (optional) |
| Audit | Riwayat aktivitas sistem |

---

## 4. CORE AND ACCESS CONTROL

### 4.1 `users`

Menyimpan seluruh akun pengguna aplikasi.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | User ID |
| name | VARCHAR(150) | Nama pengguna |
| email | VARCHAR(255) UNIQUE | Email |
| password | VARCHAR(255) | Password hash |
| phone | VARCHAR(30) NULL | Nomor telepon |
| is_active | BOOLEAN | Status akun |
| email_verified_at | TIMESTAMP NULL | Verifikasi email |
| remember_token | VARCHAR(100) NULL | Remember token |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 4.2 `roles`

Menyimpan definisi role.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Role ID |
| name | VARCHAR(100) UNIQUE | Nama role |
| description | TEXT NULL | Deskripsi |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Role awal:

- Owner
- Admin
- Cashier
- Barista

### 4.3 `permissions`

Menyimpan daftar permission sistem.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Permission ID |
| name | VARCHAR(150) UNIQUE | Permission identifier |
| description | TEXT NULL | Deskripsi |

Contoh:

- `pos.access`
- `pos.create_transaction`
- `inventory.view`
- `inventory.manage`
- `ann.view`
- `ann.train`
- `reports.view`
- `users.manage`

### 4.4 `role_user`

Pivot relasi pengguna dengan role.

| Column | Type | Description |
|---|---|---|
| user_id | BIGINT FK | User |
| role_id | BIGINT FK | Role |

Unique constraint: `(user_id, role_id)`.

### 4.5 `permission_role`

Pivot relasi role dengan permission.

| Column | Type | Description |
|---|---|---|
| role_id | BIGINT FK | Role |
| permission_id | BIGINT FK | Permission |

Unique constraint: `(role_id, permission_id)`.

### 4.6 `business_settings`

Menyimpan konfigurasi bisnis.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| setting_key | VARCHAR(150) UNIQUE | Nama konfigurasi |
| setting_value | TEXT NULL | Nilai konfigurasi |
| value_type | VARCHAR(30) | Tipe nilai |
| description | TEXT NULL | Keterangan |
| updated_by | BIGINT FK NULL | Pengguna terakhir |

Contoh konfigurasi:

- Nama coffee shop.
- Alamat.
- Zona waktu.
- Mata uang.
- Batas hari operasional.
- Pengaturan struk.

### 4.7 `business_schedules`

Menyimpan jadwal operasional mingguan.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| day_of_week | TINYINT UNIQUE | Hari (0–6) |
| opening_time | TIME | Jam buka |
| closing_time | TIME | Jam tutup |
| closing_day_offset | TINYINT | Pergeseran hari penutupan |
| is_open | BOOLEAN | Status operasional |

Data awal:

| Hari | Opening | Closing | Offset |
|---|---|---|---|
| Minggu | 15:00 | 23:00 | 0 |
| Senin | 15:00 | 23:00 | 0 |
| Selasa | 15:00 | 23:00 | 0 |
| Rabu | 15:00 | 23:00 | 0 |
| Kamis | 15:00 | 23:00 | 0 |
| Jumat | 15:00 | 00:00 | 1 |
| Sabtu | 15:00 | 00:00 | 1 |

Catatan: Penutupan pukul 24:00 direpresentasikan sebagai pukul 00:00 pada hari berikutnya dengan `closing_day_offset = 1`.

### 4.8 `business_sessions`

Menyimpan sesi operasional aktual.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Session ID |
| business_date | DATE UNIQUE | Tanggal operasional |
| opened_at | DATETIME | Waktu mulai |
| closed_at | DATETIME NULL | Waktu selesai |
| status | ENUM | open, closed |
| opened_by | BIGINT FK | Pengguna pembuka |
| closed_by | BIGINT FK NULL | Pengguna penutup |
| notes | TEXT NULL | Keterangan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Tujuan:

- Mengelompokkan transaksi berdasarkan hari operasional.
- Mengakomodasi transaksi yang melewati tengah malam.
- Menjadi dasar agregasi dataset harian.
- Menyediakan batas waktu operasional yang aktual.

`business_date` menjadi identitas hari operasional, bukan tanggal kalender dari timestamp transaksi.

---

## 5. PRODUCT AND CATEGORY MANAGEMENT

### 5.1 `categories`

Menyimpan kategori produk menu.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Category ID |
| name | VARCHAR(150) | Nama kategori |
| slug | VARCHAR(180) UNIQUE | Slug |
| description | TEXT NULL | Deskripsi |
| is_active | BOOLEAN | Status |
| sort_order | INTEGER | Urutan tampilan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 5.2 `products`

Menyimpan produk menu yang dijual.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Product ID |
| category_id | BIGINT FK | Kategori |
| sku | VARCHAR(100) UNIQUE | SKU |
| name | VARCHAR(180) | Nama produk |
| description | TEXT NULL | Deskripsi |
| selling_price | DECIMAL(15,2) | Harga jual |
| image_path | VARCHAR(255) NULL | Lokasi gambar |
| is_available | BOOLEAN | Ketersediaan |
| is_active | BOOLEAN | Status |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |
| deleted_at | TIMESTAMP NULL | Soft delete |

Ketentuan:

- Produk menu berbeda dengan bahan baku.
- Produk yang pernah digunakan dalam transaksi tidak boleh dihapus secara permanen.
- Harga historis transaksi harus disimpan pada detail transaksi.

---

## 6. INGREDIENT AND UNIT MANAGEMENT

### 6.1 `units`

Menyimpan satuan pengukuran.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Unit ID |
| name | VARCHAR(80) | Nama satuan |
| symbol | VARCHAR(20) UNIQUE | Simbol |
| unit_type | ENUM | weight, volume, count |
| is_active | BOOLEAN | Status |

Contoh:

| Name | Symbol | Type |
|---|---|---|
| Gram | g | weight |
| Kilogram | kg | weight |
| Milliliter | ml | volume |
| Liter | L | volume |
| Pieces | pcs | count |
| Pack | pack | count |

### 6.2 `ingredients`

Menyimpan seluruh bahan baku dan perlengkapan inventaris.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Ingredient ID |
| sku | VARCHAR(100) UNIQUE | Kode bahan |
| name | VARCHAR(180) | Nama bahan |
| category | VARCHAR(100) NULL | Kategori bahan |
| unit_id | BIGINT FK | Satuan stok dasar |
| ingredient_type | ENUM | core, non_core |
| current_stock | DECIMAL(15,4) | Stok terkini |
| minimum_stock | DECIMAL(15,4) NULL | Stok minimum |
| safety_stock | DECIMAL(15,4) NULL | Stok pengaman |
| lead_time_days | DECIMAL(8,2) NULL | Lead time pemasok |
| is_prediction_target | BOOLEAN | Target ANN |
| is_active | BOOLEAN | Status |
| notes | TEXT NULL | Keterangan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |
| deleted_at | TIMESTAMP NULL | Soft delete |

Ketentuan:

1. Daftar bahan baku bersifat dinamis.
2. Bahan baru dapat ditambahkan tanpa perubahan struktur database.
3. Bahan core dan non-core tetap masuk inventaris.
4. Hanya bahan yang memenuhi kriteria penelitian yang menjadi target ANN.
5. Bahan seperti air, sedotan, cup, dan tissue dapat dikelola sebagai non-core.
6. Produk seperti Americano tidak boleh dimasukkan sebagai bahan baku.
7. `is_prediction_target` tidak boleh otomatis bernilai true hanya karena `ingredient_type = core`.

`current_stock` merupakan nilai stok terkini yang harus selalu konsisten dengan histori pergerakan stok.

### 6.3 `ingredient_suppliers`

Pivot relasi bahan baku dengan pemasok.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| ingredient_id | BIGINT FK | Bahan baku |
| supplier_id | BIGINT FK | Pemasok |
| supplier_sku | VARCHAR(100) NULL | SKU pemasok |
| purchase_unit_id | BIGINT FK | Satuan pembelian |
| conversion_factor | DECIMAL(15,4) | Konversi ke satuan dasar |
| purchase_price | DECIMAL(15,2) NULL | Harga beli |
| lead_time_days | DECIMAL(8,2) NULL | Lead time |
| is_preferred | BOOLEAN | Pemasok utama |

Satu bahan baku dapat memiliki beberapa pemasok.

---

## 7. RECIPE AND BILL OF MATERIALS

### 7.1 `recipes`

Menyimpan identitas resep produk.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Recipe ID |
| product_id | BIGINT FK | Produk |
| version | INTEGER | Versi resep |
| name | VARCHAR(180) | Nama resep |
| effective_from | DATETIME | Mulai berlaku |
| effective_until | DATETIME NULL | Akhir berlaku |
| is_active | BOOLEAN | Status |
| created_by | BIGINT FK | Pembuat |
| created_at | TIMESTAMP | Waktu dibuat |

Unique constraint: `(product_id, version)`.

### 7.2 `recipe_items`

Menyimpan komposisi bahan baku.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| recipe_id | BIGINT FK | Resep |
| ingredient_id | BIGINT FK | Bahan baku |
| quantity | DECIMAL(15,4) | Jumlah penggunaan |
| unit_id | BIGINT FK | Satuan takaran |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Unique constraint: `(recipe_id, ingredient_id)`.

### 7.3 Recipe Rules

- Satu produk dapat mempunyai beberapa versi resep.
- Satu resep dapat menggunakan banyak bahan baku.
- Satu bahan baku dapat digunakan oleh banyak resep.
- Takaran resep harus dapat dikonversi ke satuan dasar inventaris.
- Perubahan resep tidak boleh mengubah transaksi historis.
- Setiap transaksi harus menyimpan snapshot komposisi bahan baku yang digunakan.

### 7.4 `sale_ingredient_usages`

Menyimpan snapshot pemakaian bahan baku dari transaksi penjualan.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| order_item_id | BIGINT FK | Detail penjualan |
| ingredient_id | BIGINT FK | Bahan baku |
| recipe_id | BIGINT FK NULL | Versi resep |
| quantity_per_unit | DECIMAL(15,4) | Takaran per produk |
| sold_quantity | DECIMAL(15,4) | Jumlah produk terjual |
| total_quantity | DECIMAL(15,4) | Total pemakaian |
| unit_id | BIGINT FK | Satuan dasar |
| created_at | TIMESTAMP | Waktu dibuat |

Tabel ini penting untuk menjaga histori pemakaian bahan baku berdasarkan resep yang berlaku ketika transaksi dilakukan.

Perhitungan:

`Total Usage = Quantity per Unit × Sold Quantity`

Data ini menjadi salah satu sumber utama pembentukan dataset ANN.

---

## 8. INVENTORY MANAGEMENT

### 8.1 `inventory_movements`

Mencatat seluruh perubahan stok bahan baku.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Movement ID |
| ingredient_id | BIGINT FK | Bahan baku |
| business_session_id | BIGINT FK NULL | Sesi operasional |
| movement_type | ENUM | purchase_in, sale_out, adjustment_in, adjustment_out, waste, return_in, return_out |
| quantity | DECIMAL(15,4) | Jumlah perubahan |
| stock_before | DECIMAL(15,4) | Stok sebelumnya |
| stock_after | DECIMAL(15,4) | Stok setelah perubahan |
| reference_type | VARCHAR(100) NULL | Jenis referensi |
| reference_id | BIGINT NULL | ID referensi |
| notes | TEXT NULL | Keterangan |
| created_by | BIGINT FK | Pengguna |
| created_at | TIMESTAMP | Waktu perubahan |

Aturan:

- Setiap perubahan stok wajib menghasilkan inventory movement.
- Movement tidak boleh dihapus untuk memperbaiki kesalahan.
- Koreksi dilakukan melalui movement pembalik atau adjustment baru.
- Perubahan stok harus dilakukan dalam database transaction.
- Referensi transaksi harus dapat ditelusuri.

### 8.2 `stock_opnames`

Menyimpan sesi pemeriksaan stok fisik.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| reference_number | VARCHAR(100) UNIQUE | Nomor opname |
| status | ENUM | draft, submitted, approved, cancelled |
| opname_date | DATETIME | Tanggal pemeriksaan |
| created_by | BIGINT FK | Pembuat |
| approved_by | BIGINT FK NULL | Penyetuju |
| notes | TEXT NULL | Keterangan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 8.3 `stock_opname_items`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| stock_opname_id | BIGINT FK | Sesi opname |
| ingredient_id | BIGINT FK | Bahan baku |
| system_quantity | DECIMAL(15,4) | Stok sistem |
| physical_quantity | DECIMAL(15,4) | Stok fisik |
| difference | DECIMAL(15,4) | Selisih |
| notes | TEXT NULL | Keterangan |

Opname yang disetujui menghasilkan inventory movement adjustment.

---

## 9. POS AND SALES MANAGEMENT

### 9.1 `payment_methods`

Menyimpan metode pembayaran.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| name | VARCHAR(100) | Nama metode |
| code | VARCHAR(50) UNIQUE | Kode |
| is_active | BOOLEAN | Status |

Contoh:

- Cash
- QRIS
- Transfer

### 9.2 `orders`

Menyimpan transaksi penjualan.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Order ID |
| order_number | VARCHAR(100) UNIQUE | Nomor transaksi |
| business_session_id | BIGINT FK | Sesi operasional |
| user_id | BIGINT FK | Kasir |
| customer_id | BIGINT FK NULL | Pelanggan |
| subtotal | DECIMAL(15,2) | Subtotal |
| discount_amount | DECIMAL(15,2) | Total diskon |
| tax_amount | DECIMAL(15,2) | Pajak jika berlaku |
| total_amount | DECIMAL(15,2) | Total akhir |
| status | ENUM | draft, pending, paid, cancelled, refunded |
| notes | TEXT NULL | Catatan |
| paid_at | DATETIME NULL | Waktu pembayaran |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 9.3 `order_items`

Menyimpan detail produk dalam transaksi.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| order_id | BIGINT FK | Transaksi |
| product_id | BIGINT FK | Produk |
| recipe_id | BIGINT FK NULL | Versi resep |
| product_name | VARCHAR(180) | Snapshot nama |
| quantity | DECIMAL(12,4) | Jumlah |
| unit_price | DECIMAL(15,2) | Harga satuan |
| discount_amount | DECIMAL(15,2) | Diskon item |
| subtotal | DECIMAL(15,2) | Subtotal |
| created_at | TIMESTAMP | Waktu dibuat |

Snapshot nama, harga, dan resep diperlukan agar transaksi historis tetap akurat meskipun data produk atau resep berubah.

### 9.4 `order_payments`

Menyimpan pembayaran transaksi.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| order_id | BIGINT FK | Transaksi |
| payment_method_id | BIGINT FK | Metode pembayaran |
| amount | DECIMAL(15,2) | Nominal pembayaran |
| reference_number | VARCHAR(150) NULL | Referensi pembayaran |
| status | ENUM | pending, completed, failed, refunded |
| paid_at | DATETIME NULL | Waktu pembayaran |
| created_at | TIMESTAMP | Waktu dibuat |

Struktur ini memungkinkan satu transaksi menggunakan lebih dari satu metode pembayaran.

### 9.5 `carts`

Menyimpan keranjang transaksi sementara.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Cart ID |
| user_id | BIGINT FK | Pemilik keranjang |
| business_session_id | BIGINT FK | Sesi operasional |
| cart_name | VARCHAR(100) NULL | Nama keranjang |
| status | ENUM | active, converted, abandoned |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 9.6 `cart_items`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| cart_id | BIGINT FK | Keranjang |
| product_id | BIGINT FK | Produk |
| quantity | DECIMAL(12,4) | Jumlah |
| notes | TEXT NULL | Catatan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Keranjang belum mengurangi stok aktual. Pengurangan stok baru dilakukan ketika transaksi berhasil dibayar dan dikonfirmasi sesuai aturan transaksi.

### 9.7 Transaction Integrity Rules

Proses pembayaran wajib menggunakan database transaction.

Urutan proses:

1. Validasi keranjang.
2. Validasi produk dan ketersediaan bahan baku.
3. Membuat order.
4. Membuat order items.
5. Menyimpan pembayaran.
6. Menyimpan snapshot pemakaian bahan baku.
7. Membuat inventory movements.
8. Memperbarui current stock.
9. Mengubah status transaksi menjadi paid.
10. Commit transaksi.

Jika salah satu proses gagal, seluruh perubahan harus di-rollback.

Pembatalan atau refund setelah pembayaran tidak boleh sekadar menghapus order. Sistem harus mencatat pembatalan dan melakukan pengembalian stok melalui movement yang sesuai.

---

## 10. CUSTOMER MANAGEMENT

### 10.1 `customers`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Customer ID |
| name | VARCHAR(150) | Nama |
| phone | VARCHAR(30) NULL | Nomor telepon |
| email | VARCHAR(255) NULL | Email |
| notes | TEXT NULL | Catatan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Pelanggan dapat bersifat opsional pada transaksi POS.

---

## 11. SUPPLIER AND PROCUREMENT

### 11.1 `suppliers`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Supplier ID |
| name | VARCHAR(180) | Nama pemasok |
| contact_person | VARCHAR(150) NULL | Kontak |
| phone | VARCHAR(30) NULL | Telepon |
| email | VARCHAR(255) NULL | Email |
| address | TEXT NULL | Alamat |
| is_active | BOOLEAN | Status |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 11.2 `purchase_orders`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Purchase ID |
| purchase_number | VARCHAR(100) UNIQUE | Nomor pembelian |
| supplier_id | BIGINT FK | Supplier |
| status | ENUM | draft, submitted, approved, partially_received, received, cancelled |
| purchase_date | DATE | Tanggal pembelian |
| expected_date | DATE NULL | Estimasi kedatangan |
| total_amount | DECIMAL(15,2) | Total |
| created_by | BIGINT FK | Pembuat |
| approved_by | BIGINT FK NULL | Penyetuju |
| notes | TEXT NULL | Keterangan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 11.3 `purchase_order_items`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| purchase_order_id | BIGINT FK | Purchase order |
| ingredient_id | BIGINT FK | Bahan baku |
| quantity | DECIMAL(15,4) | Jumlah |
| unit_id | BIGINT FK | Satuan pembelian |
| conversion_factor | DECIMAL(15,4) | Konversi ke satuan dasar |
| unit_price | DECIMAL(15,2) | Harga |
| subtotal | DECIMAL(15,2) | Subtotal |

### 11.4 `goods_receipts`

Menyimpan penerimaan barang.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| receipt_number | VARCHAR(100) UNIQUE | Nomor penerimaan |
| purchase_order_id | BIGINT FK | Purchase order |
| received_by | BIGINT FK | Penerima |
| received_at | DATETIME | Waktu penerimaan |
| notes | TEXT NULL | Keterangan |
| created_at | TIMESTAMP | Waktu dibuat |

### 11.5 `goods_receipt_items`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| goods_receipt_id | BIGINT FK | Penerimaan |
| purchase_order_item_id | BIGINT FK | Detail pembelian |
| ingredient_id | BIGINT FK | Bahan baku |
| received_quantity | DECIMAL(15,4) | Jumlah diterima |
| unit_id | BIGINT FK | Satuan |
| base_quantity | DECIMAL(15,4) | Jumlah satuan dasar |

Penerimaan barang menghasilkan inventory movement bertipe `purchase_in`.

Lead time aktual dapat dihitung dari tanggal pemesanan sampai tanggal penerimaan.

---

## 12. PROMOTION AND DISCOUNT

### 12.1 `promotions`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Promotion ID |
| name | VARCHAR(150) | Nama promosi |
| code | VARCHAR(100) NULL | Kode promosi |
| discount_type | ENUM | percentage, fixed |
| discount_value | DECIMAL(15,2) | Nilai diskon |
| start_at | DATETIME | Mulai |
| end_at | DATETIME | Berakhir |
| minimum_purchase | DECIMAL(15,2) NULL | Minimum transaksi |
| maximum_discount | DECIMAL(15,2) NULL | Batas diskon |
| is_active | BOOLEAN | Status |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 12.2 `promotion_products`

| Column | Type | Description |
|---|---|---|
| promotion_id | BIGINT FK | Promosi |
| product_id | BIGINT FK | Produk |

### 12.3 `order_promotions`

Menyimpan snapshot promosi yang digunakan dalam transaksi.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| order_id | BIGINT FK | Transaksi |
| promotion_id | BIGINT FK | Promosi |
| discount_amount | DECIMAL(15,2) | Nominal diskon |

---

## 13. EXPENSE MANAGEMENT

### 13.1 `expense_categories`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| name | VARCHAR(120) | Kategori |
| description | TEXT NULL | Deskripsi |
| is_active | BOOLEAN | Status |

### 13.2 `expenses`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Expense ID |
| expense_category_id | BIGINT FK | Kategori |
| business_session_id | BIGINT FK NULL | Sesi operasional |
| description | TEXT | Keterangan |
| amount | DECIMAL(15,2) | Nominal |
| expense_date | DATE | Tanggal |
| payment_method_id | BIGINT FK NULL | Metode pembayaran |
| created_by | BIGINT FK | Pengguna |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

---

## 14. WEATHER DATA MANAGEMENT

### 14.1 `weather_providers`

Menyimpan konfigurasi penyedia data cuaca.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| name | VARCHAR(120) | Nama provider |
| provider_code | VARCHAR(80) UNIQUE | Kode |
| base_url | VARCHAR(255) | Endpoint |
| is_active | BOOLEAN | Status |
| configuration | JSON NULL | Konfigurasi non-secret |

API key tidak disimpan dalam tabel ini. Secret disimpan melalui environment variable atau mekanisme secret management yang sesuai.

### 14.2 `weather_observations`

Menyimpan data cuaca berdasarkan waktu pengamatan.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| provider_id | BIGINT FK | Provider |
| observed_at | DATETIME | Waktu observasi |
| latitude | DECIMAL(10,7) | Latitude |
| longitude | DECIMAL(10,7) | Longitude |
| temperature | DECIMAL(7,3) NULL | Suhu °C |
| rainfall | DECIMAL(10,3) NULL | Curah hujan mm |
| weather_condition | VARCHAR(100) NULL | Kondisi cuaca |
| raw_data | JSON NULL | Respons asli provider |
| created_at | TIMESTAMP | Waktu disimpan |

Unique constraint disesuaikan dengan provider, lokasi, dan waktu pengamatan.

### 14.3 `weather_daily_summaries`

Menyimpan agregasi cuaca per hari operasional.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| business_date | DATE UNIQUE | Hari operasional |
| average_temperature | DECIMAL(7,3) NULL | Rata-rata suhu |
| minimum_temperature | DECIMAL(7,3) NULL | Suhu minimum |
| maximum_temperature | DECIMAL(7,3) NULL | Suhu maksimum |
| total_rainfall | DECIMAL(10,3) NULL | Total curah hujan |
| dominant_condition | VARCHAR(100) NULL | Cuaca dominan |
| observation_count | INTEGER | Jumlah observasi |
| data_completeness | DECIMAL(5,2) | Persentase kelengkapan |
| source_status | ENUM | complete, partial, missing |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Agregasi mengikuti sesi operasional coffee shop, bukan rentang waktu kalender 00:00–23:59.

Data cuaca yang tidak tersedia harus ditandai sebagai missing atau partial. Sistem tidak boleh membuat nilai cuaca fiktif.

---

## 15. DATASET MANAGEMENT

### 15.1 `dataset_versions`

Menyimpan versi dataset penelitian.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Dataset ID |
| version | VARCHAR(50) UNIQUE | Versi dataset |
| dataset_name | VARCHAR(150) | Nama |
| start_date | DATE | Awal periode |
| end_date | DATE | Akhir periode |
| total_records | INTEGER | Jumlah baris |
| status | ENUM | draft, validated, approved, archived |
| feature_schema | JSON | Definisi fitur |
| validation_summary | JSON NULL | Hasil validasi |
| created_by | BIGINT FK | Pembuat |
| created_at | TIMESTAMP | Waktu dibuat |

### 15.2 `dataset_records`

Menyimpan data observasi untuk setiap bahan baku dan hari operasional.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Record ID |
| dataset_version_id | BIGINT FK | Versi dataset |
| ingredient_id | BIGINT FK | Bahan baku |
| business_date | DATE | Hari operasional |
| sales_quantity | DECIMAL(15,4) | Total produk terjual atau fitur agregat |
| historical_demand | DECIMAL(15,4) NULL | Pemakaian historis |
| rolling_mean_3d | DECIMAL(15,4) NULL | Rata-rata 3 hari |
| temperature | DECIMAL(7,3) NULL | Suhu |
| rainfall | DECIMAL(10,3) NULL | Curah hujan |
| day_of_week | TINYINT | Hari dalam minggu |
| actual_demand | DECIMAL(15,4) | Target aktual |
| data_quality | ENUM | valid, partial, invalid |
| created_at | TIMESTAMP | Waktu dibuat |

Unique constraint: `(dataset_version_id, ingredient_id, business_date)`.

Catatan:

- Struktur fitur final mengikuti `ANN_SPECIFICATION.md`.
- `actual_demand` berasal dari pemakaian bahan baku yang dihitung melalui data transaksi dan resep.
- `sales_quantity` tidak boleh diasumsikan sebagai jumlah pemakaian bahan baku.
- Dataset harus mempertahankan urutan waktu.
- Dataset 3–6 bulan merupakan target awal pengumpulan data, bukan jaminan kecukupan model ANN.

### 15.3 Dataset Integrity

Setiap dataset harus dapat ditelusuri ke:

- Data transaksi sumber.
- Versi resep yang digunakan.
- Data pemakaian bahan baku.
- Data cuaca.
- Periode operasional.
- Aturan preprocessing.
- Versi dataset.

Dataset yang telah digunakan dalam training tidak boleh berubah secara diam-diam. Perubahan data menghasilkan versi dataset baru.

---

## 16. ARTIFICIAL NEURAL NETWORK MANAGEMENT

### 16.1 `ann_models`

Menyimpan metadata model ANN.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Model ID |
| model_name | VARCHAR(150) | Nama model |
| version | VARCHAR(50) | Versi |
| algorithm | VARCHAR(80) | MLP / ANN |
| architecture | JSON | Struktur jaringan |
| feature_schema | JSON | Fitur input |
| target_definition | JSON | Definisi output |
| model_path | VARCHAR(255) | Lokasi file model |
| scaler_path | VARCHAR(255) NULL | Lokasi scaler |
| status | ENUM | draft, candidate, active, archived |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

### 16.2 `ann_training_runs`

Menyimpan setiap proses training.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Training ID |
| ann_model_id | BIGINT FK | Model |
| dataset_version_id | BIGINT FK | Dataset |
| training_started_at | DATETIME | Mulai training |
| training_completed_at | DATETIME NULL | Selesai training |
| training_status | ENUM | queued, running, completed, failed |
| hyperparameters | JSON | Parameter training |
| train_records | INTEGER | Jumlah data training |
| validation_records | INTEGER | Jumlah data validasi |
| test_records | INTEGER | Jumlah data testing |
| train_metrics | JSON NULL | Evaluasi training |
| validation_metrics | JSON NULL | Evaluasi validasi |
| test_metrics | JSON NULL | Evaluasi testing |
| error_message | TEXT NULL | Informasi error |
| created_at | TIMESTAMP | Waktu dibuat |

### 16.3 `ann_model_ingredients`

Menyimpan target bahan baku untuk setiap model.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| ann_model_id | BIGINT FK | Model |
| ingredient_id | BIGINT FK | Bahan baku |
| output_index | INTEGER | Posisi output |
| evaluation_metrics | JSON NULL | Evaluasi per bahan |
| created_at | TIMESTAMP | Waktu dibuat |

### 16.4 `ann_predictions`

Menyimpan hasil prediksi.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Prediction ID |
| ann_model_id | BIGINT FK | Model |
| training_run_id | BIGINT FK NULL | Training run |
| dataset_version_id | BIGINT FK | Dataset |
| ingredient_id | BIGINT FK | Bahan baku |
| prediction_date | DATE | Tanggal target |
| prediction_generated_at | DATETIME | Waktu prediksi |
| predicted_demand | DECIMAL(15,4) | Hasil prediksi |
| actual_demand | DECIMAL(15,4) NULL | Aktual setelah tersedia |
| mae | DECIMAL(15,4) NULL | Error MAE |
| rmse | DECIMAL(15,4) NULL | Error RMSE |
| status | ENUM | generated, evaluated, invalid |
| created_at | TIMESTAMP | Waktu dibuat |

Prediksi harus menyimpan identitas model dan dataset yang digunakan agar hasilnya dapat direproduksi.

### 16.5 ANN Rules

- Laravel menjadi pemilik data bisnis.
- Python bertanggung jawab atas preprocessing, training, evaluasi, dan inference.
- File model ANN disimpan pada storage, bukan sebagai binary langsung dalam tabel.
- Database menyimpan metadata dan lokasi file model.
- Model aktif harus memiliki histori evaluasi.
- Training baru tidak otomatis menggantikan model aktif.
- Hasil prediksi tidak boleh langsung mengubah stok aktual.

---

## 17. FORECASTING AND STOCK RECOMMENDATION

### 17.1 `stock_forecasts`

Menyimpan hasil perhitungan kebutuhan persediaan.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | Forecast ID |
| ingredient_id | BIGINT FK | Bahan baku |
| ann_prediction_id | BIGINT FK | Referensi prediksi |
| forecast_date | DATE | Tanggal perhitungan |
| current_stock | DECIMAL(15,4) | Stok aktual saat perhitungan |
| predicted_demand | DECIMAL(15,4) | Prediksi kebutuhan |
| average_daily_demand | DECIMAL(15,4) | Rata-rata kebutuhan |
| demand_std_dev | DECIMAL(15,4) NULL | Standar deviasi |
| lead_time_days | DECIMAL(8,2) | Lead time |
| service_level | DECIMAL(5,2) | Target layanan |
| z_score | DECIMAL(8,4) | Nilai Z |
| safety_stock | DECIMAL(15,4) | Stok pengaman |
| reorder_point | DECIMAL(15,4) | Titik pemesanan ulang |
| estimated_coverage_days | DECIMAL(10,2) NULL | Estimasi cakupan stok |
| recommended_quantity | DECIMAL(15,4) | Rekomendasi pembelian |
| status | ENUM | normal, warning, critical |
| created_at | TIMESTAMP | Waktu dibuat |

### 17.2 Forecasting Rules

Sistem menggunakan pendekatan:

Safety Stock:

`SS = Z × σd × √L`

Reorder Point:

`ROP = Average Daily Demand × L + SS`

Catatan: Rumus tersebut menggunakan asumsi kebutuhan harian independen dan lead time tetap. Implementasi harus mengikuti metode final pada `ANN_SPECIFICATION.md` dan aturan forecasting yang disetujui.

Hasil forecasting tidak menggantikan stok aktual dan tidak melakukan pengadaan otomatis.

---

## 18. ATTENDANCE MANAGEMENT (OPTIONAL)

Modul kehadiran dirancang terpisah agar dapat diaktifkan apabila dibutuhkan.

### 18.1 `attendance_records`

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| user_id | BIGINT FK | Karyawan |
| business_session_id | BIGINT FK NULL | Sesi operasional |
| check_in_at | DATETIME | Waktu masuk |
| check_out_at | DATETIME NULL | Waktu keluar |
| status | ENUM | present, late, absent |
| notes | TEXT NULL | Keterangan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Fitur ini tidak menjadi dependensi POS maupun ANN.

---

## 19. AUDIT LOG

### 19.1 `activity_logs`

Menyimpan aktivitas penting pengguna.

| Column | Type | Description |
|---|---|---|
| id | BIGINT PK | ID |
| user_id | BIGINT FK NULL | Pengguna |
| action | VARCHAR(100) | Jenis aktivitas |
| subject_type | VARCHAR(150) NULL | Jenis objek |
| subject_id | BIGINT NULL | ID objek |
| old_values | JSON NULL | Nilai sebelum |
| new_values | JSON NULL | Nilai sesudah |
| ip_address | VARCHAR(45) NULL | IP |
| user_agent | TEXT NULL | Browser |
| created_at | TIMESTAMP | Waktu aktivitas |

Aktivitas yang perlu dicatat:

- Perubahan resep.
- Penyesuaian stok.
- Pembatalan transaksi.
- Refund.
- Perubahan harga.
- Perubahan permission.
- Persetujuan pembelian.
- Perubahan konfigurasi ANN.
- Aktivasi model ANN.

---

## 20. DATABASE RELATIONSHIP OVERVIEW

### 20.1 Core Relationships

- `users` memiliki relasi many-to-many dengan `roles`.
- `roles` memiliki relasi many-to-many dengan `permissions`.
- `categories` memiliki banyak `products`.
- `products` memiliki banyak `recipes`.
- `recipes` memiliki banyak `recipe_items`.
- `ingredients` memiliki banyak `recipe_items`.
- `ingredients` memiliki banyak `inventory_movements`.

### 20.2 POS Relationships

- `business_sessions` memiliki banyak `orders`.
- `users` memiliki banyak `orders`.
- `customers` memiliki banyak `orders`.
- `orders` memiliki banyak `order_items`.
- `orders` memiliki banyak `order_payments`.
- `order_items` memiliki banyak `sale_ingredient_usages`.
- `sale_ingredient_usages` terhubung dengan `ingredients`.

### 20.3 Procurement Relationships

- `suppliers` memiliki banyak `purchase_orders`.
- `purchase_orders` memiliki banyak `purchase_order_items`.
- `purchase_orders` memiliki banyak `goods_receipts`.
- `goods_receipts` memiliki banyak `goods_receipt_items`.
- `goods_receipt_items` menghasilkan `inventory_movements`.

### 20.4 ANN Relationships

- `dataset_versions` memiliki banyak `dataset_records`.
- `ingredients` memiliki banyak `dataset_records`.
- `dataset_versions` memiliki banyak `ann_training_runs`.
- `ann_models` memiliki banyak `ann_training_runs`.
- `ann_models` memiliki banyak `ann_model_ingredients`.
- `ann_models` memiliki banyak `ann_predictions`.
- `ingredients` memiliki banyak `ann_predictions`.
- `ann_predictions` dapat menjadi sumber `stock_forecasts`.

---

## 21. DATABASE FLOW

### 21.1 POS to Inventory

```text
PRODUCTS
   |
   v
RECIPES
   |
   v
RECIPE ITEMS
   |
   v
POS TRANSACTION
   |
   v
ORDER ITEMS
   |
   v
SALE INGREDIENT USAGES
   |
   v
INVENTORY MOVEMENTS
   |
   v
CURRENT STOCK
```

### 21.2 Inventory to ANN Dataset

```text
SALES TRANSACTIONS
        |
        v
SALE INGREDIENT USAGES
        |
        v
DAILY INGREDIENT CONSUMPTION
        |
        +-------------------+
        |                   |
        v                   v
 HISTORICAL DATA       WEATHER DATA
        |                   |
        +---------+---------+
                  |
                  v
             DATASET
                  |
                  v
          DATA VALIDATION
                  |
                  v
          DATASET VERSION
```

### 21.3 ANN to Forecasting

```text
DATASET VERSION
       |
       v
ANN TRAINING
       |
       v
MODEL EVALUATION
       |
       v
ACTIVE ANN MODEL
       |
       v
PREDICTION
       |
       v
STOCK FORECAST
       |
       v
SAFETY STOCK + ROP
       |
       v
PURCHASE RECOMMENDATION
```

---

## 22. INDEXING AND CONSTRAINT STRATEGY

### 22.1 Required Indexes

| Table | Index |
|---|---|
| users | email UNIQUE |
| products | sku UNIQUE, category_id, is_active |
| ingredients | sku UNIQUE, ingredient_type, is_prediction_target |
| recipes | product_id, version |
| recipe_items | recipe_id, ingredient_id |
| orders | order_number UNIQUE, business_session_id, user_id, status, created_at |
| order_items | order_id, product_id |
| inventory_movements | ingredient_id, created_at, business_session_id |
| purchase_orders | purchase_number UNIQUE, supplier_id, status |
| weather_observations | provider_id, observed_at |
| weather_daily_summaries | business_date UNIQUE |
| dataset_records | dataset_version_id, ingredient_id, business_date |
| ann_predictions | ingredient_id, prediction_date, ann_model_id |
| stock_forecasts | ingredient_id, forecast_date |

### 22.2 Foreign Key Rules

- Gunakan foreign key pada relasi yang memiliki integritas referensial.
- Hindari cascade delete untuk transaksi, stok, dataset, dan histori ANN.
- Gunakan restrict atau soft delete untuk data master yang sudah digunakan.
- Data yang benar-benar bersifat sementara dapat menggunakan cascade delete jika aman.
- Pastikan indeks tersedia untuk kolom foreign key yang sering digunakan.

### 22.3 Unique Constraints

Contoh constraint penting:

- `users.email`
- `products.sku`
- `ingredients.sku`
- `orders.order_number`
- `purchase_orders.purchase_number`
- `dataset_versions.version`
- `(product_id, version)` pada recipes
- `(recipe_id, ingredient_id)` pada recipe_items
- `(dataset_version_id, ingredient_id, business_date)` pada dataset_records

---

## 23. DATA RETENTION AND HISTORICAL INTEGRITY

1. Transaksi penjualan tidak boleh dihapus permanen setelah pembayaran.
2. Perubahan stok harus memiliki histori.
3. Resep historis harus dipertahankan.
4. Dataset yang digunakan dalam training harus dapat direproduksi.
5. Model ANN yang pernah digunakan harus tetap memiliki metadata.
6. Hasil prediksi historis tidak boleh ditimpa oleh prediksi baru.
7. Pembatalan dan refund harus dicatat sebagai proses tersendiri.
8. Soft delete hanya digunakan untuk entitas master yang relevan.
9. Data sumber penelitian tidak boleh dimodifikasi tanpa menghasilkan jejak perubahan.

---

## 24. IMPLEMENTATION PRIORITY

Database dikembangkan secara bertahap.

| Phase | Database Scope |
|---|---|
| Phase 1 | Users, Roles, Permissions, Business Settings |
| Phase 2 | Categories, Products, Units, Ingredients |
| Phase 3 | Recipes, Recipe Items, Inventory Movements |
| Phase 4 | Business Sessions, Orders, Order Items, Payments, POS |
| Phase 5 | Customers, Suppliers, Purchasing, Goods Receipts |
| Phase 6 | Promotions, Expenses, Reports |
| Phase 7 | Weather Providers, Weather Observations, Daily Summaries |
| Phase 8 | Dataset Versions, Dataset Records |
| Phase 9 | ANN Models, Training Runs, Predictions |
| Phase 10 | Stock Forecasts, Safety Stock, Reorder Recommendations |
| Optional | Attendance, Additional Operational Modules |

Urutan final mengikuti `IMPLEMENTATION_PLAN.md`.

---

## 25. DATABASE VALIDATION CHECKLIST

Sebelum database dinyatakan siap:

### Structure
- [ ] Semua tabel memiliki primary key.
- [ ] Relasi foreign key telah ditentukan.
- [ ] Unique constraint telah ditentukan.
- [ ] Indeks untuk query penting tersedia.
- [ ] Tidak ada duplikasi fungsi tabel.

### Product and Inventory
- [ ] Produk dan bahan baku dipisahkan.
- [ ] Satuan mendukung konversi.
- [ ] Bahan core dan non-core dapat dikelola.
- [ ] Target ANN dapat ditentukan secara dinamis.
- [ ] Perubahan stok tercatat dalam inventory movements.
- [ ] Resep memiliki versioning.

### POS
- [ ] Transaksi terhubung ke business session.
- [ ] Transaksi menyimpan snapshot harga dan resep.
- [ ] Multi-payment didukung.
- [ ] Pengurangan stok berjalan secara atomik.
- [ ] Pembatalan dan refund memiliki mekanisme koreksi stok.

### Research and ANN
- [ ] Dataset memiliki versioning.
- [ ] Setiap record terhubung dengan bahan baku dan hari operasional.
- [ ] Data cuaca dapat ditelusuri.
- [ ] Training run terhubung dengan dataset.
- [ ] Model memiliki versioning.
- [ ] Prediksi memiliki referensi model.
- [ ] Data aktual dan hasil prediksi dipisahkan.

### Security
- [ ] Role dan permission terpisah.
- [ ] API secret tidak disimpan sebagai konfigurasi biasa.
- [ ] Histori aktivitas penting tersedia.
- [ ] Data transaksi dan stok tidak dapat dihapus sembarangan.

---

## 26. OPEN DATABASE DECISIONS

Hal-hal berikut masih membutuhkan verifikasi sebelum implementasi:

1. Versi final MySQL/MariaDB berdasarkan environment.
2. Apakah satu pengguna dapat memiliki lebih dari satu role.
3. Apakah sistem mendukung split payment sejak versi pertama.
4. Apakah terdapat pajak atau service charge.
5. Satuan dasar setiap bahan baku.
6. Aturan konversi satuan pembelian ke satuan inventaris.
7. Apakah stok negatif benar-benar dilarang untuk semua jenis movement.
8. Kebijakan refund dan pembatalan transaksi.
9. Format penyimpanan file model ANN.
10. Finalisasi struktur fitur dataset berdasarkan `ANN_SPECIFICATION.md`.

Keputusan yang belum disetujui tidak boleh dianggap sebagai aturan bisnis final.

---

## 27. FINAL DATABASE PRINCIPLE

Database Seumpama POS harus menjadi sumber data operasional yang konsisten dan dapat dipercaya.

Setiap transaksi penjualan menghasilkan data pemakaian bahan baku berdasarkan resep yang berlaku. Data tersebut menjadi sumber pembentukan dataset penelitian.

ANN bekerja menggunakan dataset yang telah divalidasi dan memiliki versi. Hasil prediksi selanjutnya digunakan untuk membantu perhitungan kebutuhan persediaan, safety stock, dan reorder point.

Prinsip utama:

**Jangan mengorbankan integritas data operasional demi menyederhanakan proses prediksi.**

Database harus memastikan bahwa data transaksi, pemakaian bahan baku, persediaan aktual, dataset penelitian, dan hasil prediksi memiliki hubungan yang jelas tanpa mencampurkan tanggung jawab masing-masing modul.

---

END OF DATABASE DESIGN DOCUMENT