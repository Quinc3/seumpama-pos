# PROGRESS.md

# SEUMPAMA POS

## Project Development Progress

| Informasi             | Keterangan                                    |
| --------------------- | --------------------------------------------- |
| Project               | Seumpama POS                                  |
| Project Type          | Web-Based POS and Inventory Management System |
| Object                | Coffee Shop Seumpama Bunga                    |
| Location              | Tigaraksa, Kabupaten Tangerang, Banten        |
| Last Updated          | 2026-11-11                                    |
| Timezone              | Asia/Jakarta                                  |
| Overall Status        | PHASE 6 COMPLETE                              |
| Implementation Status | PHASE 7 READY                                 |

---

# 1. Project Overview

Seumpama POS merupakan aplikasi berbasis web untuk membantu operasional Coffee Shop Seumpama Bunga dalam mengelola transaksi penjualan, persediaan bahan baku, pembelian, laporan operasional, serta prediksi kebutuhan bahan baku menggunakan Artificial Neural Network (ANN).

Sistem terdiri dari:

- Laravel sebagai aplikasi utama.
- Laravel Blade sebagai frontend.
- Tailwind CSS untuk styling.
- Alpine.js untuk interaksi frontend.
- MySQL/MariaDB sebagai database.
- Python sebagai ANN Service.
- REST API untuk komunikasi Laravel dan Python.

Laravel menjadi pemilik utama data operasional. Python bertanggung jawab atas pemrosesan dataset, training, evaluasi, dan prediksi ANN.

Detail proyek mengikuti `PROJECT_MASTER.md` dan dokumen spesifikasi terkait.

---

# 2. Current Project Status

<badge>PHASE 2 COMPLETE — READY FOR PHASE 3</badge>

Phase 0 (Preparation), Phase 1 (Application Foundation), dan Phase 2 (Master Data Management) selesai per 2026-11-11. Seluruh master data CRUD (Unit, Category, Ingredient, Product, Supplier, Customer, User) sudah berfungsi dengan permission control. Phase 3 (Recipe & Inventory) siap dimulai.

Kondisi implementasi:

- ✅ Source code aplikasi (Laravel) sudah diimplementasikan (Phase 1 + Phase 2).
- ✅ Database migrations sudah diimplementasikan (users, RBAC, units, categories, ingredients, products, suppliers, customers).
- ✅ Backend modules: auth, RBAC, 7 master data module.
- ✅ Frontend modules: layout, components (button, input, select, file-input, password-input, alert, badge, card, icon), 7 modul CRUD.
- ⏳ POS transaction flow belum diimplementasikan (Phase 4).
- ⏳ Recipe/BOM belum diimplementasikan (Phase 3).
- ⏳ Inventory management (stock movement, opname) belum diimplementasikan (Phase 3).
- ⏳ Purchasing & goods receipt belum diimplementasikan (Phase 3).
- ✅ Python ANN Service skeleton sudah diimplementasikan (health check only).
- ⏳ ANN model belum dilatih atau dievaluasi (Phase 7).
- ✅ Integrasi Laravel dan Python sudah diverifikasi (health check).
- ⏳ Deployment belum dilakukan (Phase 11).

Dokumentasi yang sudah disusun tidak boleh dianggap sebagai bukti bahwa fitur terkait sudah selesai diimplementasikan.

---

# 3. Project Documentation

Dokumentasi proyek terdiri dari 13 file Markdown:

| No. | Document                       | Responsibility                                                                                         |
| --- | ------------------------------ | ------------------------------------------------------------------------------------------------------ |
| 1   | `PROJECT_MASTER.md`            | Master project reference                                                                               |
| 2   | `PROJECT_SPEC.md`              | Functional requirements dan business rules                                                             |
| 3   | `DATABASE_DESIGN.md`           | Database schema dan relationships                                                                      |
| 4   | `ANN_SPECIFICATION.md`         | ANN dan machine learning specification                                                                 |
| 5   | `UI_UX_GUIDELINES.md`          | UI/UX guidelines                                                                                       |
| 6   | `IMPLEMENTATION_PLAN.md`       | Development phases dan implementation workflow                                                         |
| 7   | `AI_RULES.md`                  | AI coding assistant rules                                                                              |
| 8   | `TESTING_STRATEGY.md`          | Testing strategy                                                                                       |
| 9   | `INTEGRATION_SPECIFICATION.md` | Integration architecture dan workflow                                                                  |
| 10  | `DEPLOYMENT_GUIDE.md`          | Deployment dan environment configuration                                                               |
| 11  | `API_CONTRACT.md`              | Laravel-Python API contract                                                                            |
| 12  | `PROGRESS.md`                  | Project development progress                                                                           |
| 13  | `ENVIRONMENT_AUDIT.md`         | Verified development environment (Windows 10, PHP 8.2.12, MariaDB 10.4.32, Python 3.12.8, strict mode) |

Seluruh dokumentasi harus disimpan dalam direktori dokumentasi proyek dan diperiksa konsistensinya sebelum implementasi dimulai.

---

# 4. Confirmed Project Decisions

Keputusan berikut merupakan dasar perencanaan proyek. Detail implementasinya tetap mengikuti dokumen spesifikasi masing-masing.

## 4.1 Technology Stack

| Component            | Technology                |
| -------------------- | ------------------------- |
| Backend              | Laravel 12.72             |
| Frontend             | Laravel Blade             |
| Styling              | Tailwind CSS v4           |
| Frontend Interaction | Alpine.js                 |
| Database             | MariaDB 10.4.32 (strict)  |
| Machine Learning     | Python 3.12 + FastAPI     |
| ML Model             | Artificial Neural Network |
| Communication        | REST API                  |
| PDF Library          | barryvdh/laravel-dompdf   |
| Timezone             | Asia/Jakarta              |

## 4.2 Application Scope

Fitur utama yang direncanakan:

- Authentication dan role-based authorization.
- User management.
- Product dan category management.
- Ingredient management.
- Recipe dan Bill of Materials (BOM).
- Inventory management.
- Stock movement.
- Stock opname.
- Supplier management.
- Purchasing dan goods receiving.
- Point of Sale.
- Transaction management.
- Operational reports.
- Weather data integration.
- Dataset preparation.
- ANN training dan evaluation.
- Demand prediction.
- Safety stock dan reorder point.
- Stock replenishment recommendation.

Rincian scope dan acceptance criteria mengikuti `PROJECT_SPEC.md`.

## 4.3 Operational Business Day

Jadwal operasional yang menjadi dasar perencanaan:

| Day             | Opening | Closing |
| --------------- | ------- | ------- |
| Sunday–Thursday | 15:00   | 23:00   |
| Friday–Saturday | 15:00   | 24:00   |

Sistem menggunakan operational business day, bukan sekadar pergantian tanggal kalender.

Detail pengelolaan business session mengikuti spesifikasi proyek.

## 4.4 ANN Scope

Tujuan ANN adalah memprediksi kebutuhan harian bahan baku inti berdasarkan data historis dan fitur pendukung.

Dasar perhitungan target awal:

`Theoretical Consumption = Quantity Sold × Recipe Quantity`

Ketentuan:

- Produk menu berbeda dengan bahan baku.
- Bahan baku dapat diklasifikasikan sebagai core dan non-core.
- Bahan baku target ANN ditentukan melalui konfigurasi.
- Prediksi dilakukan berdasarkan operational business day.
- Prediksi bukan pengganti stok aktual.
- ANN tidak melakukan pembelian atau perubahan stok otomatis.

Detail mengikuti `ANN_SPECIFICATION.md`.

---

# 5. Implementation Phase Tracking

**Acuan tunggal jumlah, nama, dan urutan fase adalah `IMPLEMENTATION_PLAN.md`.**

Total fase: 12 fase, mulai Phase 0 sampai Phase 11.

| Phase | Name                                 | Status      |
| ----- | ------------------------------------ | ----------- |
| 0     | Project Preparation                  | COMPLETED   |
| 1     | Application Foundation               | COMPLETED   |
| 2     | Master Data Management               | COMPLETED   |
| 3     | Recipe and Inventory                 | COMPLETED   |
| 4     | POS                                  | COMPLETED   |
| 5     | Operational Reports                  | COMPLETED   |
| 6     | Weather and Dataset Preparation      | COMPLETED   |
| 7     | Python ANN Integration               | IN PROGRESS |
| 8     | Forecasting and Stock Recommendation | NOT STARTED |
| 9     | UI Refinement                        | NOT STARTED |
| 10    | Testing and Validation               | NOT STARTED |
| 11    | Deployment and Documentation         | NOT STARTED |

## 5.1 Phase Status Definitions

| Status      | Meaning                                |
| ----------- | -------------------------------------- |
| NOT STARTED | Belum mulai dikerjakan                 |
| IN PROGRESS | Sedang dikerjakan                      |
| BLOCKED     | Tidak dapat dilanjutkan karena kendala |
| COMPLETED   | Sudah selesai dan diverifikasi         |

Ketentuan:

1. Penomoran fase tidak boleh berbeda dari `IMPLEMENTATION_PLAN.md`.
2. Nama fase tidak boleh diubah secara sepihak.
3. Status fase harus berdasarkan pekerjaan aktual.
4. Fase hanya boleh dinyatakan COMPLETED jika deliverables dan acceptance criteria telah terpenuhi.
5. Dokumentasi yang selesai tidak otomatis berarti implementasi fitur selesai.
6. Perubahan implementation plan harus disinkronkan ke dokumen ini.

---

# 6. Current Phase Details

## Phase 0 — Project Preparation

**Status: COMPLETED (2026-11-11)**

Tujuan:

Menyelesaikan persiapan proyek sebelum memulai implementasi aplikasi.

### Activities

- [x] Menentukan project scope awal.
- [x] Menentukan technology stack awal.
- [x] Menyusun project master document.
- [x] Menyusun functional specification.
- [x] Menyusun database design.
- [x] Menyusun ANN specification.
- [x] Menyusun UI/UX guidelines.
- [x] Menyusun implementation plan.
- [x] Menyusun AI coding assistant rules.
- [x] Menyusun testing strategy.
- [x] Menyusun integration specification.
- [x] Menyusun deployment guide.
- [x] Menyusun API contract.
- [x] Memastikan seluruh dokumen tersimpan pada direktori proyek.
- [x] Melakukan consistency audit seluruh dokumentasi.
- [x] Mengidentifikasi dan menyelesaikan keputusan yang masih unresolved.
- [ ] Memastikan kesiapan business data. _(non-blocking, dikerjakan paralel Phase 2–3)_
- [x] Menetapkan baseline implementasi.
- [x] Menyelesaikan persiapan environment development.

**Completion Criteria:**

- Seluruh dokumentasi tersedia dan konsisten.
- Keputusan penting proyek telah ditetapkan atau ditandai sebagai unresolved.
- Tidak ada konflik spesifikasi yang menghambat implementasi.
- Development environment siap digunakan.
- Persiapan Phase 0 telah diverifikasi.

Catatan: Checklist penyusunan dokumen menunjukkan dokumen telah disusun, bukan berarti seluruh isi telah diaudit atau disetujui final.

---

## Phase 1 — Application Foundation

**Status: COMPLETED (2026-11-11)**

### Deliverables Verified

- Laravel 12.72 bootstrap + MySQL migration.
- Manual auth: login, logout, password eye toggle, `is_active` check.
- RBAC: 13 permissions, 4 roles (Owner/Admin/Cashier/Barista), middleware `permission:`, Blade directive `@permission`.
- Application layout: sidebar + topbar, permission-based navigation, mobile drawer (Alpine).
- Reusable Blade components: button, input, alert, badge, card, icon (Lucide).
- Python FastAPI skeleton: `/api/v1/health`, request ID middleware, Bearer auth prep.
- Laravel↔Python health check: `AnnServiceClient`, `/health/ann` endpoint, failure isolation verified.

### Non-blocking Notes

- Register publik disabled (user hanya dibuat oleh Admin/Owner via seed atau UI users.manage di Phase 2).
- Python Bearer auth helper sudah ada tapi belum diaktifkan di endpoint manapun — akan dipakai di Phase 7 (training/prediction).

---

## Phase 2 — Master Data Management

**Status: COMPLETED (2026-11-11)**

### Deliverables Verified

- **Unit Management (2a)** — CRUD, `weight/volume/count` enum, symbol unique, seed 6 unit default (g, kg, ml, L, pcs, pack).
- **Category Management (2b)** — CRUD, auto-slug (duplikat → `slug-1`, `slug-2`), sort_order, permission split view/manage.
- **Ingredient Management (2c)** — CRUD, FK ke Unit (restrict delete), `core/non_core` classification, `is_prediction_target` toggle (auto-disabled untuk non_core), current_stock / minimum_stock / safety_stock / lead_time_days, soft delete, filter + search.
- **Product Management (2d)** — CRUD, FK ke Category (restrict delete), image upload dengan preview + drag-and-drop, soft delete, filter (search + kategori).
- **Supplier Management (2e)** — CRUD, contact info, search.
- **Customer Management (2f)** — CRUD, contact info, search.
- **User Management UI (2g)** — CRUD, single role assignment, password reset, self-protection (tidak bisa hapus/nonaktifkan sendiri), Owner protection (Owner tidak bisa dihapus).

### Reusable Components Added

- `x-select` — dropdown dengan error state.
- `x-file-input` — dashed drop zone dengan preview, drag-and-drop, remove option.
- `x-password-input` — password field dengan eye toggle (independen per field).
- `x-button` — extended dengan `tag="a"` support untuk link button.

### Non-blocking Notes

- Ingredient `current_stock` di-set saat create saja. Perubahan selanjutnya via Stock Movement (Phase 3) — sesuai `DATABASE_DESIGN.md` §6.2.
- Product image upload opsional, maks 2MB, JPG/PNG/WebP.
- Permission `customers.manage` belum ada — customer pakai `reports.view` sementara.
- Register publik tetap tidak ada. User creation hanya via admin (`users.manage`).

---

## Phase 3 — Recipe and Inventory

**Status: COMPLETED (2026-11-13)**

### Deliverables Verified

- **Recipe Management (3a)** — CRUD resep per produk, dynamic rows ingredient, unit auto-follow, guard 1 aktif per produk. Seeder 11 bahan unik + 6 resep sample.
- **Recipe Versioning (3b)** — edit membuat versi baru, arsipkan versi lama (`is_active=false`, `effective_until` terisi), version history timeline, guard read-only untuk arsip.
- **Inventory Movement (3c)** — `StockService` central (atomic + `lockForUpdate`), stock in/out dengan negative warning, movement immutable, riwayat dengan filter (bahan/tipe/tanggal).
- **Stock Opname (3d)** — draft → approve workflow, auto-create `adjustment_in`/`adjustment_out` movement saat approve, cancel tanpa efek, reference auto (`OPN-YYYYMMDD-XXX`), skip movement jika selisih 0.
- **Purchasing + Goods Receipt (3e)** — PO lifecycle (`draft → approved → partially_received → received | cancelled`), partial receive dengan outstanding tracking, auto stock-in via StockService dengan reference `goods_receipt`, receive ke-2 untuk sisa, reference auto (`PO-YYYYMMDD-XXX`, `GR-YYYYMMDD-XXX`).

### Non-blocking Notes

- Business session FK di `inventory_movements.business_session_id` belum ada — akan di-constraint di Phase 4 setelah `business_sessions` dibuat.
- Unit conversion (purchase unit ≠ base unit) **belum diimplementasi** di 3e — qty selalu dalam base unit ingredient. Akan dipertimbangkan jika butuh multi-unit di masa depan.
- Harga PO per base unit (misal Rp 150/gram). Bisa di-refine di Phase 9 (UI) atau Phase 11 (production refinement).

---

## Phase 4 — Point of Sale

**Status: COMPLETED (2026-11-13)**

### Deliverables Verified

- **Business Session + Payment Methods (4a)** — `business_schedules` (7 hari, closing 02:00 Jumat/Sabtu untuk buffer closing), `business_sessions` auto via `BusinessDateResolver` (cross-midnight), `payment_methods` (Cash/QRIS/Transfer).
- **POS Interface + Cart (4b)** — Full-screen POS, product grid dengan filter kategori + search, cart via fetch API (add/update/remove/clear), `item_count` = total quantity, cart persist per user per session.
- **Checkout + Payment + Stock Deduction (4c)** — `OrderService::checkout()` atomic, snapshot `order_items`, `sale_ingredient_usages` per ingredient usage, stock `sale_out` via `StockService`, payment validation (cash boleh overpay, non-cash exact), order auto-number `ORD-YYYYMMDD-XXX`, cart → converted.
- **Receipt + PDF (4d)** — Receipt thermal-friendly 80mm HTML + CSS print, PDF via `barryvdh/laravel-dompdf`, `business_settings` table (shop info + footer), print + download buttons.
- **Refund (4e)** — Full refund only, permission `pos.refund` (Owner/Admin), reversal stock via `return_in` (per `sale_ingredient_usages`), status → `refunded`, payment → `refunded`, notes audit trail, modal overlay.

### Non-blocking Notes

- Void sebelum bayar = cart abandoned (no order). No separate endpoint needed.
- Partial refund belum didukung (v1 full refund).
- `sale_ingredient_usages` tidak dihapus saat refund — snapshot historis dipertahankan. **Dataset ANN query di Phase 6 harus `WHERE orders.status != 'refunded'`**.
- Refund tidak create payment row baru — status change di original payment row (audit via order notes).
- Settings UI (edit shop_name, address, dll via browser) belum ada — akan dibuat di Phase 11 (Deployment & Documentation) atau Phase 9 (UI Refinement).

---

## Phase 6 — Weather and Dataset Preparation

**Status: COMPLETED (2026-11-14)**

### Deliverables Verified

- **Weather Integration (6a)** — Open-Meteo adapter via interface (`WeatherProviderInterface` + `OpenMeteoProvider`), hourly observations, business-date aggregation per operating hours, manual sync UI (idempotent), offline-safe error handling. Koordinat Seumpama: `-6.275405946900061, 106.48096423376577`.
- **Dataset Generation (6b)** — aggregate `sale_ingredient_usages` + weather per business_date per ingredient, feature engineering (`lag_d1/d2/d3/d7`, `rolling_mean_3d/7d`, calendar), versioned snapshot immutable, `data_quality` (valid/partial), UI list + create + detail + records table with filter.

### Feature Schema Final (v1)

| Feature              | Source                                                  | Nullable?                  |
| -------------------- | ------------------------------------------------------- | -------------------------- |
| `actual_demand`      | SUM `sale_ingredient_usages.total_quantity` (paid only) | No (0 if no tx)            |
| `lag_d1/d2/d3/d7`    | Consumption N days back                                 | Yes (null if out of range) |
| `rolling_mean_3d/7d` | Avg consumption in window                               | Yes (null if incomplete)   |
| `temperature`        | `weather_daily_summaries.average_temperature`           | Yes                        |
| `rainfall`           | `weather_daily_summaries.total_rainfall`                | Yes                        |
| `day_of_week`        | Carbon dayOfWeek (0=Sun, 6=Sat)                         | No                         |
| `is_weekend`         | day_of_week ∈ {5, 6} (Jumat/Sabtu)                      | No                         |

### Non-blocking Notes

- **Dataset siap struktur, belum siap training meaningful.** Data transaksi saat ini: 2 paid orders (14 hari), target ANN butuh minimum 3 bulan per `ANN_SPECIFICATION.md` §5.2. Phase 7 pipeline akan ditest dengan data existing untuk verifikasi, tapi **evaluasi model tidak meaningful sampai data aktual 3+ bulan tersedia**.
- Holiday indicator & weather condition encoding **belum termasuk** di schema v1 — bisa ditambah di iterasi berikutnya setelah ada data.
- Weather data saat ini dari Open-Meteo forecast API (`past_days`), bukan historical API. Untuk data >92 hari ke belakang, perlu endpoint berbeda. Catat sebagai concern Phase 7/8.
- Dataset refresh: buat version baru (immutable). Dataset lama tetap ada untuk audit.

---

# 7. Business Data Readiness

Data aktual dari Coffee Shop Seumpama Bunga masih perlu dikumpulkan dan diverifikasi.

| Data                           | Status  |
| ------------------------------ | ------- |
| Final ingredient list          | PENDING |
| Core ingredient classification | PENDING |
| Recipe dan BOM                 | PENDING |
| Ingredient quantity per recipe | PENDING |
| Ingredient base units          | PENDING |
| Supplier information           | PENDING |
| Supplier purchase prices       | PENDING |
| Supplier lead times            | PENDING |
| Historical transaction data    | PENDING |
| Historical inventory data      | PENDING |
| Weather data availability      | PENDING |
| ANN prediction targets         | PENDING |

Jangan menggunakan data asumsi sebagai data aktual operasional.

Data simulasi hanya boleh digunakan untuk testing dan harus diberi identifikasi yang jelas.

---

# 8. ANN Data Readiness

Kebutuhan data ANN perlu diverifikasi sebelum training.

| Requirement                 | Status  |
| --------------------------- | ------- |
| Historical sales data       | PENDING |
| Recipe accuracy             | PENDING |
| Historical ingredient usage | PENDING |
| Core ingredient targets     | PENDING |
| Weather data availability   | PENDING |
| Operational calendar        | PENDING |
| Feature selection           | PENDING |
| Dataset validation          | PENDING |
| Dataset versioning          | PENDING |
| ANN baseline evaluation     | PENDING |

Target pengumpulan data awal:

- Minimum perencanaan: 3 bulan.
- Rekomendasi: 6 bulan.

Durasi tersebut bukan jaminan bahwa dataset sudah memadai untuk menghasilkan model ANN yang akurat.

Kelayakan dataset harus diperiksa berdasarkan jumlah observasi, kualitas data, variasi permintaan, dan kebutuhan evaluasi model.

---

# 9. Known Risks and Considerations

| Risk                              | Consideration                                                      |
| --------------------------------- | ------------------------------------------------------------------ |
| Limited historical sales data     | Kelayakan ANN perlu dievaluasi sebelum training                    |
| Inaccurate recipe data            | Perhitungan theoretical consumption dapat menyimpang               |
| Theoretical vs actual consumption | Keduanya harus dibedakan                                           |
| Missing weather data              | Missing values tidak boleh dianggap sebagai nol                    |
| ANN overfitting                   | Gunakan chronological validation dan baseline comparison           |
| Python service failure            | Operasional POS harus tetap berjalan                               |
| Incorrect inventory records       | Setiap perubahan stok harus tercatat                               |
| Inconsistent business day         | Semua laporan dan dataset harus mengikuti operational business day |
| Incomplete supplier data          | Replenishment recommendation dapat menjadi tidak akurat            |

---

# 10. Pending Decisions

Keputusan berikut masih perlu dikonfirmasi berdasarkan kebutuhan aktual dan dokumentasi teknis.

**Resolved 2026-11-11 (Phase 0):**

- ~~Negative stock policy.~~ → Allowed with warning; movement tetap dicatat.
- ~~Cancellation dan refund policy.~~ → Refund + stock reversal; admin-only; audit trail.
- ~~Python API framework.~~ → FastAPI.
- ~~Split payment.~~ → Not supported v1; schema ready.
- ~~Auth Laravel↔Python.~~ → Bearer Token statis.
- ~~PDF library.~~ → barryvdh/laravel-dompdf.
- ~~Multi-role user.~~ → Single role (v1); schema many-to-many.

**Still pending (non-blocking Phase 3):**

- Final database engine dan version → Phase 11.
- Final user-role dan permission matrix → Phase 2 (initial done, refine as needed).
- Payment details → Phase 4.
- Final ingredient units dan conversion rules → Phase 3 (saat recipe).
- Final ingredient list dan recipes → Phase 3.
- Supplier lead time → Phase 3.
- Inventory service level → Phase 8.
- ANN feature selection → Phase 7.
- ANN model configuration → Phase 7.
- ANN evaluation thresholds → Phase 7.
- Weather provider configuration → Phase 6.
- Model artifact storage → Phase 7.
- Production hosting dan infrastructure → Phase 11.
- Backup dan recovery configuration → Phase 11.

Keputusan yang belum ditetapkan tidak boleh dianggap final oleh AI coding assistant.

---

# 11. Next Actions

Phase 6 selesai. Next: Phase 7 — Python ANN Integration.

Phase 7 order:

1. **7a — Python ANN Service extension** — train endpoint (async), Keras MLP, evaluation (MAE/RMSE), model artifact storage, prediction endpoint.
2. **7b — Laravel integration** — extended AnnServiceClient, training request + polling status via queue, model metadata storage.
3. **7c — Prediction flow + UI** — generate prediction, store result, display prediction vs actual, model management UI.

Phase 7 fokus: **pipeline ANN end-to-end** (train → evaluate → predict).
Model training dengan data existing (14 hari) hanya untuk verifikasi pipeline, **bukan untuk training meaningful**. Data aktual 3+ bulan tetap perlu dikumpulkan untuk skripsi.

Phase 8 (Forecasting) setelah Phase 7 selesai.

---

# 12. Progress Update Rules

Setiap perubahan progress harus memenuhi ketentuan:

1. Update tanggal `Last Updated`.
2. Perbarui status fase berdasarkan kondisi aktual.
3. Catat task yang selesai dan sudah diverifikasi.
4. Catat kendala yang menghambat pekerjaan.
5. Jangan menandai pekerjaan sebagai selesai tanpa bukti.
6. Jangan mengubah jumlah atau urutan fase secara sepihak.
7. Pastikan perubahan tetap konsisten dengan `IMPLEMENTATION_PLAN.md`.
8. Perbarui bagian terkait saja jika tidak ada kebutuhan untuk mengubah bagian lain.

---

# 13. Changelog

| Date       | Changes                                                                                                                                                                                                                                                         |
| ---------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-11-10 | Initial progress document drafted                                                                                                                                                                                                                               |
| 2026-11-11 | Synchronized implementation phase tracking with IMPLEMENTATION_PLAN.md and updated documentation inventory                                                                                                                                                      |
| 2026-11-11 | Phase 0 completed: environment audit verified (Windows 10, PHP 8.2.12, MariaDB 10.4.32 strict, Python 3.12.8, Composer 2.11.2); git repo initialized + pushed (commit 9666905); 7 technical decisions locked                                                    |
| 2026-11-11 | Phase 1 completed: Laravel 12.72 bootstrap, manual auth, RBAC (13 permissions, 4 roles), application layout, reusable Blade components, Python FastAPI skeleton, Laravel↔Python health check (failure isolation verified)                                       |
| 2026-11-11 | Phase 2 completed: 7 master data modules (Unit, Category, Ingredient, Product, Supplier, Customer, User Management) — all CRUD with validation + permission control; added x-select, x-file-input, x-password-input components; image upload with drag-and-drop |
| 2026-11-13 | Phase 3 completed: recipe management + versioning, inventory movement (StockService atomic), stock opname draft→approve, purchasing + goods receipt with partial receive and outstanding tracking                                                               |
| 2026-11-13 | Phase 4 completed: business session + payment methods, POS interface with cart, checkout with atomic stock deduction + sale_ingredient_usages snapshot, receipt (thermal + PDF) with business settings, refund with return_in reversal                          |
| 2026-11-14 | Phase 6 completed: weather integration (Open-Meteo adapter, hourly → business-date aggregation, idempotent manual sync), dataset generation (feature engineering with lags + rolling, versioned snapshot, immutable records)                                    |

---

**Current Overall Status: PHASE 6 COMPLETE — READY FOR PHASE 7**
