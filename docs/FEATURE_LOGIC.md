# FEATURE_LOGIC.md

# SEUMPAMA POS — Feature Logic Specification v2

**Status:** APPROVED (2026-11-19)  
**Scope:** 3 fitur tambahan pasca Phase 8  
**Estimasi:** Split 2 hari  
**Revisi dari v1:** Refund policy, bias disclosure, cancel flow, KDS retention

---

## OVERVIEW

3 concern yang diangkat:

1. **Promo Integration Full** — Info promo tersebar ke semua touchpoint
2. **Custom Request Notes** — Notes per item, tanpa modifier system (V1)
3. **Kitchen Display System (KDS)** — Layar status tracking untuk barista/dapur

---

## CONCERN 1 — PROMO INTEGRATION FULL (4 LAYER)

### 1.1 Recap Implementasi Phase 9.5

Sudah done:

- CRUD admin `/promotions`
- POS checkout modal: dropdown promo + auto-discount
- Snapshot di `order_promotions`
- `orders.discount_amount` + `orders.total_amount` di-update

Belum:

- Order detail, receipt thermal/PDF, order index, sales report

### 1.2 Layer 1 — Order Detail (`/orders/{id}`)

Card "Promosi" (jika `$order->promotion` ada):

- Nama promo (snapshot `promotion_name`)
- Tipe diskon (percentage/fixed) + value
- Nominal diskon (`discount_amount`)

Card "Pembayaran" ditambah:

- Subtotal
- Diskon (-Rp, hijau) + nama promo
- Total

**Eager load:** `OrderController::show()` load `'promotion'`

### 1.3 Layer 2 — Receipt Thermal + PDF

Baris struk setelah Subtotal:

```
Diskon (Promo X) ..... -Rp 3.600
```

Sebelum TOTAL:

```
Promo ............ Nama Promo
```

**Eager load:** `receipt()` + `pdf()` load `'promotion'`

### 1.4 Layer 3 — Order Index (`/orders`)

Kolom baru **"Promo"**:

- Ada promo: nama (badge hijau)
- Tidak ada: "—"

Filter dropdown: Dengan Promo / Tanpa Promo.

**Eager load:** `index()` load `'promotion'`

### 1.5 Layer 4 — Reports (Sales Report)

Section "**Penjualan dengan Promo**" di `/reports/sales`:

KPI:

- Total transaksi dengan promo
- Total diskon diberikan
- Persentase vs total transaksi

Table breakdown per promo:
| Promo | Transaksi | Total Diskon | Revenue |

Query: join `order_promotions` + `orders` (paid only), filter by business_date.

Empty state: "Belum ada transaksi dengan promo di periode ini."

### 1.6 Data Flow — Snapshot Principle

```
User pilih promo → checkout
   ↓
OrderService::checkout(promotion_id)
   ↓
Validate + calculate discount
   ↓
DB::transaction:
   ├─ Order::create (discount_amount, total_amount)
   ├─ OrderItem::create
   ├─ OrderPromotion::create (snapshot: name, type, value, amount)
   └─ OrderPayment::create
```

**Snapshot = source of truth.** Promo edit/delete setelah transaksi tidak mengubah histori.

### 1.7 REFUND + PROMO POLICY (REVISI dari v1) ⭐

**Aturan final (OD-7 = A):**

**Refund amount = `order_payments.amount` (jumlah yang customer bayar, sudah setelah diskon).**

| Skenario                   | Hitungan                       |
| -------------------------- | ------------------------------ |
| Subtotal                   | Rp 36.000                      |
| Diskon 10%                 | -Rp 3.600                      |
| Customer bayar             | Rp 32.400                      |
| **Refund**                 | **Rp 32.400**                  |
| Customer kehilangan diskon | -Rp 3.600 (bukan dikembalikan) |

**Alasan pilihan A:**

- Standar industri retail
- Mencegah abuse: customer tidak bisa apply promo → refund → dapat cash lebih
- Simpel akuntansi: reversal = amount_paid exactly

**Komunikasi ke customer:**

- Tulis di struk: "Diskon tidak dapat diuangkan kembali"
- Terms & conditions: "Promo hangus saat refund"

**Teknis implementasi:**

- Field baru `order_promotions.refunded_at` (nullable) — audit trail
- `OrderService::refund()` tetap reversal stock + payment
- Snapshot promo **tidak dihapus**, hanya mark `refunded_at`
- Log ke `activity_logs`: "Promo X hangus saat refund order Y"

**Future enhancement (out of V1):**

- Owner bisa override refund amount manual
- Butuh permission `pos.refund_full` (Owner only)
- Catat di PROGRESS.md sebagai open decision

### 1.8 Edge Cases

| Case                            | Handling                                                           |
| ------------------------------- | ------------------------------------------------------------------ |
| Promo dihapus setelah transaksi | Snapshot `promotion_name` tetap tampil, `$order->promotion` = null |
| Promo diedit setelah transaksi  | Snapshot value tetap (historis benar)                              |
| Multi-promo 1 order             | V1 tidak support. Future via `order_promotions` multiple rows      |
| Refund dengan promo             | Refund = amount_paid. Diskon hangus.                               |

---

## CONCERN 2 — CUSTOM REQUEST NOTES (Notes Only)

### 2.1 Scope Decision

**V1: Notes only, NO modifier system.**

- Notes = free-text per item di POS cart
- Tidak auto-adjust stock / recipe
- Barista baca notes di kitchen ticket
- Modifier system = future enhancement

### 2.2 Data Model (Existing)

- `cart_items.notes` (Phase 4) ✅
- `order_items.notes` (Phase 4) ✅

**No migration needed.**

### 2.3 UI POS Cart

Setiap item cart tambah tombol **"Notes"** (icon pencil).

- Klik → inline input / small modal
- Text field max 150 char
- Placeholder: "Less sugar, extra shot, no ice..."
- Save → update via API

**API baru:** `PATCH /pos/cart/items/{id}/notes`

**Acceptance:**

- Notes persist setelah refresh cart
- Kosongkan notes → null

### 2.4 Checkout Propagation

`OrderService::checkout()`:

- Ambil `cart_item.notes` → simpan ke `order_items.notes`

**Acceptance:**

- Order item dengan notes → `order_items.notes` terisi
- Tanpa notes → null

### 2.5 Kitchen Ticket Display

Notes ditampilkan **bold/highlight kuning** per item di kitchen ticket (lihat Concern 3).

### 2.6 SYSTEMATIC BIAS DISCLOSURE (REVISI dari v1) ⭐

**Logic stock (TIDAK berubah):**

- `sale_ingredient_usages` tetap catat konsumsi **teoritis normal** (sesuai BOM)
- Notes **tidak** auto-adjust

**Impact:**

| Data                          | Bisa Direkonsiliasi?                     |
| ----------------------------- | ---------------------------------------- |
| Stock fisik (`current_stock`) | ✅ Via stock opname                      |
| Dataset ANN historis          | ❌ **TIDAK** — systematic bias permanent |

**Contoh kasus:**

- Resep: 20g gula
- Customer minta "less sugar" → aktual 10g
- DB `sale_ingredient_usages`: **20g** (theoretical)
- Stock fisik berkurang **10g**
- Selisih 10g = **noise permanent** di dataset, tidak bisa di-rekonsiliasi tanpa modifier system

**Implikasi skripsi (honest):**

- Dataset ANN memiliki **systematic bias** untuk item dengan custom request
- Besar bias = frekuensi custom request × (theoretical − actual)
- **Stock opname TIDAK memperbaiki bias ini** (hanya koreksi stok fisik)
- **Future enhancement:** modifier system untuk auto-adjust ingredient usage

**Kalimat jujur untuk skripsi:**

> _"Custom requests introduce systematic bias in theoretical consumption that stock opname cannot resolve. Modifier system is required for accurate consumption tracking. For MVP skripsi, this is documented as limitation with recommendation for future enhancement."_

**Ukur frekuensi untuk justify "acceptable noise":**

```php
// Tinker command
$totalItems = \App\Models\OrderItem::count();
$withNotes = \App\Models\OrderItem::whereNotNull('notes')->where('notes', '!=', '')->count();
echo "Custom request rate: " . round(($withNotes / max(1, $totalItems)) * 100, 2) . "%";
```

- < 5% → noise minimal, acceptable
- 5-20% → noise moderate, disclaimer wajib
- > 20% → noise signifikan, butuh modifier system

### 2.7 Out of Scope V1

**Modifier system** (ditulis di "Saran Pengembangan" skripsi):

- Master data `modifiers` (name + recipe adjustment)
- Order item modifiers
- Auto-adjust stock based on modifier

---

## CONCERN 3 — KITCHEN DISPLAY SYSTEM (KDS)

### 3.1 Scope Decision

**Screen display + status tracking + kitchen ticket print fallback.**

### 3.2 Data Model

**Table baru: `order_item_status_logs`** (append-only):

| Column             | Type             | Description                                  |
| ------------------ | ---------------- | -------------------------------------------- |
| id                 | BIGINT PK        |                                              |
| order_item_id      | BIGINT FK        |                                              |
| status             | ENUM             | pending, preparing, ready, served, cancelled |
| changed_by         | BIGINT FK users  |                                              |
| cancel_reason_code | VARCHAR(50) NULL | (khusus cancelled)                           |
| cancel_notes       | TEXT NULL        | (khusus cancelled)                           |
| auto_generated     | BOOLEAN          | true kalau dari system (refund)              |
| created_at         | TIMESTAMP        |                                              |

**Kolom baru di `order_items`:**

| Column            | Type                                               | Default   |
| ----------------- | -------------------------------------------------- | --------- |
| current_status    | ENUM(pending, preparing, ready, served, cancelled) | 'pending' |
| status_updated_at | DATETIME NULL                                      |           |

### 3.3 Status Flow + CANCELLATION 3 JALUR (REVISI) ⭐

**Normal flow:**

```
pending → preparing → ready → served
```

**Aturan transisi:**

- Tidak bisa skip: pending langsung ke ready (harus lewat preparing)
- Tidak bisa mundur: served → preparing
- Setiap transisi INSERT `order_item_status_logs`

**CANCELLATION 3 JALUR:**

#### Jalur 1 — Manual Barista (KDS)

| Status Item | Permission                                                | Reason Wajib           |
| ----------- | --------------------------------------------------------- | ---------------------- |
| `pending`   | `pos.access`                                              | ✅ Reason code + notes |
| `preparing` | `pos.access`                                              | ✅ Reason code + notes |
| `ready`     | **`pos.void_transaction`** (admin only)                   | ✅ Reason code + notes |
| `served`    | ❌ **Tidak bisa dari KDS** (hanya admin via order detail) | —                      |

#### Jalur 2 — Auto Cancel via Refund

- Trigger: `OrderService::refund()`
- Item `pending`/`preparing`/`ready` → `cancelled`
- `auto_generated = true`, `cancel_reason_code = 'order_refunded'`
- **Item `served` tidak di-cancel** (sudah diantar customer)

#### Jalur 3 — Admin Override

- Owner/Admin bisa cancel apapun via `/orders/{id}` (bukan KDS)
- Require reason + notes
- Log di `activity_logs` + `order_item_status_logs`

**Reason codes (dropdown):**

```
- customer_request   (customer ubah pikiran)
- out_of_stock       (bahan habis)
- input_error        (salah input kasir)
- equipment_issue    (mesin rusak)
- quality_issue      (kualitas tidak sesuai)
- order_refunded     (auto, dari refund)
- other              (free text di notes)
```

### 3.4 KDS Interface (`/kitchen`)

**Full-screen kanban 4 kolom:**

```
┌─────────────┬──────────────┬─────────────┬───────────┐
│  PENDING    │  PREPARING   │   READY     │  SERVED   │
│  (baru)     │  (dikerjakan)│  (siap)     │  (filter 30m)│
├─────────────┼──────────────┼─────────────┼───────────┤
│ Order #123  │ Order #120   │ Order #118  │ (today's) │
│ 2× Espresso │ 1× Cappuccino│ 1× Latte    │           │
│ [Start]     │ [Ready]      │ [Served]    │           │
│ [Cancel]    │ [Cancel]     │ [Cancel]*   │           │
│             │              │ *admin only │           │
└─────────────┴──────────────┴─────────────┴───────────┘
```

**Header KDS (info counter):**

```
Today: 45 orders | 12 pending | 3 preparing | 5 ready | 45 served (total)
```

**Kartu order (grouped by order_id):**

- Nomor order (besar)
- Item list dengan quantity
- **Notes per item** (highlight kuning kalau ada)
- Elapsed time (misal "5 menit lalu", berubah merah kalau > 15 menit)
- Tombol aksi: Start / Ready / Served / Cancel (sesuai status)
- Cancel disabled untuk barista di status ready

**Filter view:**

- By default: today's business_session
- Kolom "Served": hanya `status_updated_at >= now() - 30 min`
- Sort: pending (oldest first), preparing (oldest first), ready (newest first)

**Auto-refresh:** polling 10 detik (GET `/kitchen/items`).

**Sound notification:** opsional, browser Notification API saat ada pending baru.

### 3.5 KDS RETENTION — Filter vs Delete (REVISI) ⭐

**Prinsip:** Data tidak pernah dihapus dari DB. Hanya filtering di KDS view.

| Data                         | Behavior                                    |
| ---------------------------- | ------------------------------------------- |
| `order_items.current_status` | **Never deleted** — 'served' stays 'served' |
| `order_item_status_logs`     | **Never deleted** — append-only audit       |
| KDS "Served" column          | Filter: 30 min terakhir only                |
| KDS header counter           | **Total served today** (bukan filtered)     |
| Order detail                 | Show **all items** any status               |
| Reports (future)             | Full historical query accessible            |

**Purpose of 30-min filter:**

- UX: kolom served tidak penuh seharian (info selesai, tidak urgent)
- Screen real estate: fokus pending + preparing + ready
- Historical data tetap accessible via `/orders` + future reports

**Tombol "View All Today" di KDS header** → redirect ke `/orders?from=today`.

### 3.6 Print Kitchen Ticket (Fallback)

**Route:** `/orders/{id}/kitchen-ticket`

**Template:**

- Header: **"KITCHEN TICKET"** (bukan STRUK)
- Nomor order (besar)
- Waktu order
- **Tanpa harga**
- Item list dengan quantity + **notes (bold/highlight)**
- Footer: order notes kalau ada

**Trigger:** Manual via tombol "Print Kitchen Ticket" di order show (OD-1 = A).

### 3.7 Dashboard Integration

Card baru "**Kitchen Queue**" di `/dashboard`:

- Pending count
- Preparing count
- Average prep time (7 hari terakhir, dari `status_logs`)

Link "Open KDS" → `/kitchen`.

### 3.8 Permission

| Fitur                                                | Permission                  |
| ---------------------------------------------------- | --------------------------- |
| KDS view                                             | `pos.access`                |
| Update status (pending → preparing → ready → served) | `pos.access`                |
| Cancel `pending`/`preparing`                         | `pos.access`                |
| Cancel `ready`                                       | `pos.void_transaction`      |
| Cancel `served`                                      | Admin via order detail only |
| Kitchen ticket print                                 | `pos.access`                |
| Dashboard kitchen queue                              | `reports.view`              |

### 3.9 Data Flow

```
Checkout success
   ↓
OrderItem created: current_status='pending', status_updated_at=now
   ↓
INSERT order_item_status_logs (status='pending', changed_by=system, auto_generated=true)
   ↓
KDS polling /kitchen/items every 10s
   ↓
Barista klik "Start Preparing"
   ↓
PATCH /kitchen/items/{id}/status {status: 'preparing'}
   ↓
Transaction:
   ├─ UPDATE order_items.current_status
   ├─ UPDATE order_items.status_updated_at
   └─ INSERT order_item_status_logs
   ↓
Repeat until 'served'
```

### 3.10 Edge Cases

| Case                                     | Handling                                  |
| ---------------------------------------- | ----------------------------------------- |
| Order refunded saat item preparing       | Auto-cancel items pending/preparing/ready |
| Item ready > 1 jam tidak served          | Highlight merah di KDS (aging warning)    |
| Barista tutup tab                        | Status tetap di DB                        |
| Multiple barista concurrent              | Transaction lock on status update         |
| Order dengan multiple items mixed status | Per item status independent               |
| Polling conflict                         | Read-only GET, write via POST — no race   |

### 3.11 Acceptance Criteria

- KDS accessible, auto-refresh 10s
- Kanban 4 kolom (pending, preparing, ready, served)
- Header counter: today total + per-status count
- Card order: nomor, items, notes, elapsed time, tombol aksi
- Status transisi per item works (pending → preparing → ready → served)
- **Cancel 3 jalur works** dengan reason code + notes
- **Auto-cancel saat refund works** (mark `auto_generated=true`)
- **Served filtered 30 min di view, DB intact**
- Kitchen ticket print works
- Dashboard kitchen queue terisi
- Permission granularity: barista vs admin for cancel `ready`

---

## CROSS-CUTTING IMPACTS

### A. Dataset ANN

- Promo: no impact
- Notes: systematic bias (disclosed)
- KDS: no impact (status tracking only)

### B. Reports

- Sales: promo section (Layer 4)
- Kitchen: future (Phase 10 optional)

### C. Sidebar Updates

- Analytics group: tambah "**Kitchen Display**" → `/kitchen` (permission `pos.access`)

---

## OPEN DECISIONS (FINAL)

| OD   | Topic                     | Decision                                    |
| ---- | ------------------------- | ------------------------------------------- |
| OD-1 | Auto-print kitchen ticket | **A — Manual**                              |
| OD-2 | KDS polling interval      | **A — 10 detik**                            |
| OD-3 | KDS grouping              | **A — Per order**                           |
| OD-4 | Sound notification        | **A — Include, opsional**                   |
| OD-5 | Cancel flow               | **B+C — KDS + Order Detail + Auto Refund**  |
| OD-6 | Modifier system future    | **Ya, tulis di skripsi**                    |
| OD-7 | Refund policy             | **A — Refund = amount_paid, diskon hangus** |

---

## IMPLEMENTATION PLAN — SPLIT 2 HARI

### HARI 1 (~2.5 - 3 jam)

#### Batch 1 — Promo Full (90-120 menit)

| Step | Task                                                       | Estimasi |
| ---- | ---------------------------------------------------------- | -------- |
| 1    | Fix Order fillable + OrderService closure use + eager load | 15 min   |
| 2    | Order detail: promo card + payment summary                 | 20 min   |
| 3    | Receipt thermal + PDF: verify + test                       | 10 min   |
| 4    | Order index: promo column + filter                         | 15 min   |
| 5    | Sales report: promo section + breakdown                    | 30 min   |
| 6    | Test end-to-end + fix bug minor                            | 30 min   |

#### Batch 2 — Notes (45-60 menit)

| Step | Task                                            | Estimasi |
| ---- | ----------------------------------------------- | -------- |
| 1    | API endpoint `PATCH /pos/cart/items/{id}/notes` | 10 min   |
| 2    | POS cart UI: notes input per item               | 15 min   |
| 3    | Propagate notes ke `order_items.notes`          | 10 min   |
| 4    | Display notes di order detail                   | 5 min    |
| 5    | Test end-to-end                                 | 15 min   |

**Commit hari 1:** `feat(phase-9.5a+b): promo full integration + custom request notes`

### HARI 2 (~3-4 jam)

#### Batch 3 — KDS (180-240 menit)

| Step | Task                                                          | Estimasi |
| ---- | ------------------------------------------------------------- | -------- |
| 1    | Migration: `order_item_status_logs` + 2 kolom `order_items`   | 15 min   |
| 2    | Models: `OrderItemStatusLog` + update `OrderItem`             | 10 min   |
| 3    | `KitchenController`: index, updateStatus, cancel              | 25 min   |
| 4    | Routes + permission middleware                                | 10 min   |
| 5    | KDS view: kanban 4 kolom + polling + filter                   | 45 min   |
| 6    | Cancel modal + reason codes                                   | 20 min   |
| 7    | Auto-cancel saat refund (integrasi ke `OrderService::refund`) | 15 min   |
| 8    | Kitchen ticket print template + route                         | 20 min   |
| 9    | Dashboard kitchen queue card                                  | 10 min   |
| 10   | Test end-to-end (checkout → KDS → flow → refund)              | 45 min   |

#### Batch 4 — Documentation + E2E (30-45 menit)

| Step | Task                                          | Estimasi |
| ---- | --------------------------------------------- | -------- |
| 1    | Update PROGRESS.md                            | 10 min   |
| 2    | Update PROJECT_SPEC.md (business rules)       | 10 min   |
| 3    | Full regression test (POS, promo, notes, KDS) | 20 min   |

**Commit hari 2:** `feat(phase-9.5c+d): kitchen display system + documentation`

---

## TOTAL ESTIMASI REALISTIS

- **Hari 1:** 2.5 - 3 jam
- **Hari 2:** 3 - 4 jam
- **Total:** 5.5 - 7 jam

**Bukan 2.5-3 jam seperti v1.** Buffer untuk debug, edge case testing, UI minor fix.

---

## APPENDIX — Data Access for Skripsi

### Query custom request rate (justify noise level)

```sql
SELECT
    COUNT(*) AS total_items,
    SUM(CASE WHEN notes IS NOT NULL AND notes != '' THEN 1 ELSE 0 END) AS with_notes,
    ROUND(SUM(CASE WHEN notes IS NOT NULL AND notes != '' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) AS pct
FROM order_items;
```

### Query prep time average (future report)

```sql
SELECT
    AVG(TIMESTAMPDIFF(SECOND,
        (SELECT created_at FROM order_item_status_logs WHERE order_item_id = oi.id AND status='pending' LIMIT 1),
        (SELECT created_at FROM order_item_status_logs WHERE order_item_id = oi.id AND status='ready' LIMIT 1)
    )) AS avg_prep_seconds
FROM order_items oi
WHERE oi.current_status = 'served';
```

---

**Status: APPROVED — Ready for Batch 1 Implementation**

```

```
