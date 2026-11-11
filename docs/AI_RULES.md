
# AI_RULES.md

# SEUMPAMA POS
## AI Coding Assistant Rules

**Status:** Active  
**Project:** Seumpama POS  
**Purpose:** Menjadi pedoman kerja AI coding assistant selama proses pengembangan Seumpama POS.

---

# 1. General Principles

AI coding assistant bertindak sebagai software development assistant yang membantu implementasi proyek berdasarkan dokumentasi dan kondisi source code aktual.

Prinsip utama:

1. Follow project documentation.
2. Inspect before modifying.
3. Understand before implementing.
4. Prefer minimal and maintainable solutions.
5. Preserve existing working functionality.
6. Avoid unnecessary complexity.
7. Never invent business rules or technical decisions.
8. Report implementation results honestly.

AI tidak boleh mengubah arah proyek berdasarkan preferensi atau asumsi pribadi.

---

# 2. Documentation Authority

Seluruh dokumentasi proyek harus dibaca sebagai satu kesatuan.

## 2.1 Project-Level Authority

`PROJECT_MASTER.md` merupakan sumber otoritas tertinggi untuk keputusan umum proyek, termasuk:

- Project scope.
- Technology stack.
- System architecture.
- Keputusan teknis yang telah dikunci.
- Batasan dan prinsip utama proyek.

Tidak ada dokumen lain yang boleh mengubah keputusan terkunci dalam `PROJECT_MASTER.md` tanpa persetujuan eksplisit.

## 2.2 Domain-Specific Authority

Setiap dokumen menjadi acuan utama untuk detail dalam domain masing-masing, selama tidak bertentangan dengan keputusan terkunci dalam `PROJECT_MASTER.md`.

| Dokumen | Otoritas |
|---|---|
| `PROJECT_SPEC.md` | Functional requirements dan business rules |
| `DATABASE_DESIGN.md` | Database schema, relationships, constraints, dan data integrity |
| `ANN_SPECIFICATION.md` | Dataset, features, preprocessing, model, training, dan evaluation ANN |
| `INTEGRATION_SPECIFICATION.md` | Arsitektur dan alur integrasi antar-komponen |
| `API_CONTRACT.md` | Endpoint, request, response, validation, dan error contract |
| `UI_UX_GUIDELINES.md` | Design system, interface, responsive behavior, dan UI/UX |
| `TESTING_STRATEGY.md` | Testing levels, test scenarios, dan acceptance testing |
| `DEPLOYMENT_GUIDE.md` | Environment, installation, deployment, dan recovery |

## 2.3 Execution and Project Tracking

Dokumen berikut mengatur pelaksanaan pekerjaan, bukan mengubah keputusan atau kebutuhan sistem.

| Dokumen | Tanggung Jawab |
|---|---|
| `IMPLEMENTATION_PLAN.md` | Urutan fase, task, deliverables, dan implementation workflow |
| `AI_RULES.md` | Aturan kerja AI coding assistant |
| `PROGRESS.md` | Status implementasi, pekerjaan selesai, dan pekerjaan tertunda |

`PROGRESS.md` tidak boleh digunakan sebagai sumber untuk mengubah business rules, architecture, atau technical specifications.

## 2.4 Conflict Resolution Rules

Jika ditemukan perbedaan antar-dokumen:

1. Periksa apakah keputusan terkait sudah dikunci dalam `PROJECT_MASTER.md`.
2. Gunakan dokumen domain terkait sebagai acuan detail apabila tidak bertentangan dengan keputusan tersebut.
3. Jangan menyelesaikan konflik dengan membuat asumsi sendiri.
4. Jangan menganggap nilai atau konfigurasi berstatus TBD, Draft, atau Provisional sebagai keputusan final.
5. Laporkan konflik dan dokumen yang terdampak sebelum mengubah keputusan.
6. Perubahan yang disetujui harus disinkronkan ke seluruh dokumen terkait.

## 2.5 Unresolved Decisions

Informasi yang belum ditetapkan harus tetap ditandai sebagai TBD, Draft, atau Provisional sesuai statusnya.

AI coding assistant dilarang mengubah keputusan yang belum ditetapkan menjadi implementasi final berdasarkan asumsi pribadi.

Jika keputusan tersebut dibutuhkan untuk melanjutkan pekerjaan, hentikan bagian implementasi yang bergantung padanya dan minta klarifikasi.

---

# 3. Technology Constraints

Gunakan technology stack yang telah ditetapkan dalam `PROJECT_MASTER.md`.

Teknologi utama proyek:

- Backend: Laravel.
- Frontend: Laravel Blade.
- Styling: Tailwind CSS.
- Interactive UI: Alpine.js.
- Database: MySQL/MariaDB sesuai keputusan proyek.
- Machine Learning Service: Python.
- ANN Framework: mengikuti `ANN_SPECIFICATION.md`.

Ketentuan:

1. Laravel merupakan aplikasi utama dan pemilik data operasional.
2. Python digunakan untuk kebutuhan dataset processing, ANN training, evaluation, dan prediction.
3. Komunikasi Laravel dan Python mengikuti `INTEGRATION_SPECIFICATION.md` dan `API_CONTRACT.md`.
4. Jangan mengubah framework atau teknologi utama tanpa persetujuan.
5. Jangan memperkenalkan SPA framework, microservices, atau arsitektur kompleks yang tidak diperlukan.
6. Jangan menambahkan dependency baru jika fitur dapat diselesaikan menggunakan kemampuan yang sudah tersedia.
7. Periksa kompatibilitas dependency dengan versi teknologi proyek sebelum menambahkannya.

---

# 4. Mandatory Development Workflow

Sebelum melakukan implementasi, ikuti urutan berikut.

## Step 1 — Understand the Request

- Pahami tujuan task.
- Identifikasi masalah yang ingin diselesaikan.
- Tentukan hasil yang diharapkan.
- Jangan langsung menulis kode sebelum memahami kebutuhan.

## Step 2 — Read Relevant Documentation

Baca dokumentasi yang berkaitan dengan task.

Minimal periksa:

- `PROJECT_MASTER.md`
- `PROJECT_SPEC.md`
- Dokumen teknis terkait task.
- `AI_RULES.md`
- `PROGRESS.md` jika berkaitan dengan status pekerjaan.

Jangan membaca atau mengubah seluruh dokumentasi tanpa kebutuhan yang jelas.

## Step 3 — Inspect Existing Code

Periksa source code sebelum melakukan perubahan.

Identifikasi:

- Struktur direktori.
- File yang berkaitan.
- Existing implementation.
- Routes dan middleware.
- Models dan relationships.
- Controllers dan services.
- Database migrations.
- Existing components.
- Dependencies dan package versions.
- Existing tests.

Jangan membuat file atau struktur baru sebelum memeriksa apakah fungsi serupa sudah tersedia.

## Step 4 — Identify Dependencies and Risks

Sebelum implementasi, tentukan:

- File yang akan diubah.
- File yang mungkin terdampak.
- Dependency antarfitur.
- Potensi regression.
- Risiko database.
- Risiko keamanan.
- Kebutuhan perubahan dokumentasi.

Untuk task sederhana, analisis cukup singkat dan proporsional.

## Step 5 — Implement Minimal Changes

Implementasikan perubahan sekecil mungkin untuk memenuhi kebutuhan.

Ketentuan:

1. Jangan mengubah file yang tidak berkaitan.
2. Jangan melakukan refactoring besar di luar scope task.
3. Jangan menghapus fitur yang sudah berjalan tanpa alasan dan persetujuan.
4. Jangan mengganti nama file, class, method, route, atau struktur yang sudah berjalan tanpa kebutuhan.
5. Jangan menambahkan abstraction hanya untuk membuat kode terlihat lebih kompleks.
6. Jangan menduplikasi fungsi yang sudah tersedia.
7. Pertahankan konsistensi dengan pola implementasi proyek.

## Step 6 — Verify the Implementation

Setelah implementasi:

- Jalankan pemeriksaan yang relevan.
- Periksa error.
- Jalankan test yang berkaitan.
- Periksa kemungkinan regression.
- Pastikan perubahan sesuai acceptance criteria.

Jangan mengklaim fitur berhasil jika belum diverifikasi.

## Step 7 — Report the Result

Gunakan format laporan pada bagian Communication Rules.

## Step 8 — Update Documentation

Perbarui `PROGRESS.md` setelah pekerjaan yang bermakna selesai.

Perbarui dokumen teknis lain hanya jika memang terdapat perubahan keputusan, spesifikasi, atau perilaku sistem.

Jangan mengubah dokumentasi hanya untuk mencatat perubahan kecil pada setiap baris kode.

---

# 5. Confirmation Requirements

AI boleh melakukan perubahan rutin dalam scope task tanpa meminta konfirmasi berulang.

Namun, wajib meminta persetujuan sebelum:

1. Mengubah project architecture.
2. Mengubah technology stack.
3. Mengubah database design secara signifikan.
4. Mengubah business rules.
5. Mengubah project scope.
6. Menghapus data atau melakukan destructive database operations.
7. Menambahkan dependency besar atau service baru.
8. Mengubah authentication atau authorization model.
9. Mengubah kontrak integrasi yang berdampak pada komponen lain.
10. Melakukan perubahan besar yang tidak diminta.

Untuk bugfix, UI adjustment, penambahan CRUD, validasi, dan pekerjaan rutin lain yang sudah jelas spesifikasinya, AI dapat langsung mengimplementasikan.

Jangan meminta konfirmasi untuk setiap keputusan teknis kecil yang sudah dapat ditentukan dari dokumentasi dan pola source code yang ada.

---

# 6. Code Quality and Architecture

Gunakan prinsip:

- KISS (Keep It Simple, Stupid).
- DRY (Don't Repeat Yourself).
- Separation of Concerns.
- Single Responsibility.
- Readability.
- Maintainability.

Ketentuan:

1. Gunakan naming yang jelas dan konsisten.
2. Hindari function atau method yang terlalu panjang jika dapat dipisahkan secara logis.
3. Hindari duplikasi business logic.
4. Gunakan Laravel conventions secara wajar.
5. Gunakan database relationships secara tepat.
6. Pisahkan business logic jika memang diperlukan.
7. Jangan membuat service, repository, interface, trait, helper, atau abstraction tanpa alasan yang jelas.
8. Jangan menerapkan design pattern hanya karena ingin menggunakan pattern tersebut.
9. Jangan membuat generic system untuk kebutuhan yang hanya muncul satu kali.
10. Jangan melakukan premature optimization.

Modular architecture tidak berarti setiap fitur harus memiliki service, repository, atau abstraction sendiri.

Prioritaskan solusi yang mudah dipahami, diuji, dan dipelihara.

---

# 7. Database and Migration Safety

Database merupakan bagian kritis dari sistem.

Ketentuan:

1. Gunakan migration untuk perubahan struktur database.
2. Pastikan foreign key dan relationships sesuai desain.
3. Gunakan tipe data yang sesuai.
4. Gunakan DECIMAL untuk nilai uang dan kuantitas yang membutuhkan presisi.
5. Gunakan transaction untuk operasi yang membutuhkan atomicity.
6. Hindari query yang menyebabkan N+1 problem.
7. Gunakan index berdasarkan kebutuhan query.
8. Jangan mengubah struktur database secara sepihak.
9. Jangan menghapus kolom atau tabel yang masih digunakan.

Dilarang menjalankan perintah destructive berikut tanpa persetujuan eksplisit:

- `php artisan migrate:fresh`
- `php artisan db:wipe`
- Penghapusan tabel.
- Penghapusan atau reset data produksi.
- Perubahan database yang berpotensi menyebabkan kehilangan data.

Sebelum migration dijalankan, periksa dampaknya terhadap data dan fitur yang sudah ada.

---

# 8. POS and Inventory Integrity

Integritas transaksi dan stok merupakan prioritas utama.

Ketentuan wajib:

1. Cart tidak mengurangi stok.
2. Stok dikurangi ketika transaksi berhasil diselesaikan sesuai business rules.
3. Proses transaksi harus menggunakan database transaction.
4. Pencatatan order, order items, payment, ingredient usage, dan inventory movement harus konsisten.
5. Hindari double stock deduction.
6. Perhatikan concurrent transactions agar stok tidak menjadi tidak konsisten.
7. Setiap perubahan stok harus memiliki inventory movement.
8. Perubahan stok tidak boleh dilakukan dengan mengubah angka stok tanpa mekanisme pencatatan.
9. Pembatalan atau refund harus mengikuti business rules dan memiliki pencatatan reversal yang sesuai.
10. Data transaksi historis tidak boleh berubah akibat perubahan harga atau recipe saat ini.

Jangan mengasumsikan aturan refund, negative stock, payment, atau cancellation jika belum ditentukan dalam dokumentasi.

---

# 9. Security and Authorization

Keamanan harus diterapkan di backend, bukan hanya frontend.

Ketentuan:

1. Gunakan authentication yang sesuai dengan sistem.
2. Terapkan authorization pada route dan backend logic.
3. Validasi seluruh input dari pengguna.
4. Jangan mempercayai data yang berasal dari browser.
5. Lindungi endpoint sensitif.
6. Gunakan CSRF protection sesuai mekanisme Laravel.
7. Jangan mengekspos credentials atau API keys.
8. Jangan menyimpan secret di source code.
9. Hindari SQL injection dan mass assignment vulnerabilities.
10. Batasi akses berdasarkan role dan permission.
11. Catat aktivitas penting sesuai kebutuhan audit trail.

Menyembunyikan tombol atau menu bukan pengganti backend authorization.

---

# 10. UI/UX Implementation Rules

Seluruh implementasi UI harus mengikuti `UI_UX_GUIDELINES.md`.

Ketentuan:

1. Gunakan design system yang konsisten.
2. Prioritaskan usability dan functional clarity.
3. Gunakan responsive layout.
4. Pastikan interface nyaman digunakan pada perangkat tablet.
5. Gunakan reusable Blade components jika memang diperlukan.
6. Hindari UI yang terlalu padat.
7. Hindari elemen dekoratif yang tidak memiliki fungsi.
8. Gunakan icon library yang telah ditentukan.
9. Jangan menggunakan emoji sebagai pengganti icon UI.
10. Gunakan bahasa interface yang jelas dan natural.

Setiap elemen interaktif harus memiliki fungsi nyata.

Contoh:

- Button harus menjalankan aksi.
- Form harus memiliki validasi.
- Search harus melakukan pencarian.
- Filter harus mengubah hasil.
- Pagination harus berfungsi.
- Modal harus dapat dibuka dan ditutup.
- Notification harus merepresentasikan hasil operasi.
- Loading state harus digunakan saat proses asynchronous.

Jangan membuat tombol atau elemen UI yang hanya terlihat berfungsi tetapi tidak memiliki implementasi.

Hindari dashboard generik yang hanya berisi kumpulan card dan chart tanpa manfaat operasional.

---

# 11. ANN and Machine Learning Rules

Implementasi ANN wajib mengikuti:

- `ANN_SPECIFICATION.md`
- `INTEGRATION_SPECIFICATION.md`
- `API_CONTRACT.md`
- `DATABASE_DESIGN.md`

Ketentuan:

1. Jangan membuat dataset palsu dan menganggapnya sebagai data aktual.
2. Bedakan zero consumption dengan missing data.
3. Perhatikan operational business day.
4. Gunakan historical data sesuai definisi dataset.
5. Hindari data leakage.
6. Gunakan chronological split untuk time-series evaluation.
7. Fit scaler hanya pada training data.
8. Gunakan baseline comparison.
9. Evaluasi model menggunakan MAE dan RMSE.
10. Simpan metadata model dan training.
11. Gunakan model versioning.
12. Jangan otomatis mengaktifkan model baru tanpa evaluasi dan persetujuan.
13. Jangan membiarkan kegagalan Python service menghentikan operasional POS.
14. Jangan mengubah prediksi menjadi keputusan pembelian otomatis.
15. Jangan mengubah stok aktual berdasarkan hasil prediksi ANN.

Prediksi ANN merupakan estimasi kebutuhan bahan baku, bukan pengganti data stok aktual.

AI tidak boleh mengarang feature, target, model configuration, atau evaluation threshold yang belum ditetapkan.

---

# 12. Integration Rules

Laravel merupakan pemilik utama data operasional.

Python ANN Service hanya bertanggung jawab atas proses machine learning sesuai kontrak integrasi.

Ketentuan:

1. Gunakan API contract yang telah disepakati.
2. Jangan mengubah request atau response format secara sepihak.
3. Validasi data sebelum dikirim ke service eksternal.
4. Tangani timeout dan connection failure.
5. Jangan membiarkan Python service menjadi single point of failure untuk POS.
6. Gunakan logging untuk kegagalan integrasi.
7. Hindari pengiriman data yang tidak diperlukan.
8. Jangan memberikan akses langsung Python ke database Laravel tanpa persetujuan dan perubahan arsitektur yang disetujui.
9. Pastikan perubahan kontrak API dikoordinasikan dengan seluruh komponen terkait.

---

# 13. Debugging and Error Handling

Saat menemukan error:

1. Identifikasi error sebenarnya.
2. Periksa stack trace dan log.
3. Telusuri penyebab utama.
4. Periksa hubungan dengan perubahan terakhir.
5. Implementasikan perbaikan yang paling kecil dan tepat.
6. Jalankan test yang relevan.
7. Pastikan tidak menimbulkan regression.

Dilarang:

- Menebak penyebab error tanpa pemeriksaan.
- Menutupi error dengan menghapus validasi.
- Menggunakan workaround yang merusak integritas data.
- Mengubah banyak file yang tidak berkaitan.
- Mengulang perbaikan yang sama tanpa mengevaluasi hasil sebelumnya.
- Mengklaim error selesai tanpa verifikasi.

---

# 14. Testing Requirements

Testing wajib mengikuti `TESTING_STRATEGY.md`.

Ketentuan:

1. Buat atau perbarui test sesuai perubahan fitur.
2. Prioritaskan business logic dan data integrity.
3. Test authorization dan validation.
4. Test alur transaksi POS.
5. Test inventory movement dan stock calculation.
6. Test integrasi Laravel-Python.
7. Test failure handling.
8. Test responsive UI sesuai kebutuhan.
9. Jangan menghapus test hanya untuk membuat test suite berhasil.
10. Jangan melewati test yang gagal tanpa menjelaskan alasannya.

Jika test tidak dapat dijalankan karena keterbatasan environment, laporkan secara eksplisit.

---

# 15. Performance and Resource Awareness

Implementasi harus mempertimbangkan resource environment proyek.

Ketentuan:

1. Hindari dependency yang terlalu berat tanpa alasan.
2. Hindari query database yang tidak efisien.
3. Gunakan pagination untuk data berjumlah besar.
4. Gunakan queue untuk proses berat jika diperlukan.
5. Hindari proses training ANN pada request POS.
6. Gunakan asynchronous processing untuk pekerjaan yang membutuhkan waktu lama.
7. Jangan mengasumsikan tersedia GPU.
8. Pertimbangkan keterbatasan hardware development environment.

Jangan melakukan optimasi berlebihan sebelum terdapat masalah performa yang nyata.

---

# 16. Progress Documentation

Setelah pekerjaan bermakna selesai, perbarui `PROGRESS.md`.

Informasi yang perlu dicatat:

- Fase yang sedang dikerjakan.
- Task yang selesai.
- Task yang belum selesai.
- File atau modul yang berubah.
- Hasil verifikasi.
- Kendala yang ditemukan.
- Next step.

Ketentuan:

1. Jangan menandai task selesai jika belum diverifikasi.
2. Jangan mengklaim fitur selesai hanya karena file sudah dibuat.
3. Bedakan Planned, In Progress, Completed, Blocked, dan Not Started.
4. Jangan mengubah status fase lain tanpa bukti.
5. Pastikan status di `PROGRESS.md` konsisten dengan `IMPLEMENTATION_PLAN.md`.

---

# 17. Communication Rules

Setiap selesai mengerjakan task, gunakan format laporan berikut:

## Completed
Jelaskan pekerjaan yang berhasil diselesaikan.

## Files Changed
Daftar file yang dibuat, diubah, atau dihapus.

## Verification
Jelaskan pemeriksaan dan test yang telah dijalankan beserta hasilnya.

## Notes
Jelaskan kendala, risiko, atau keputusan yang masih membutuhkan persetujuan.

## Next Step
Sebutkan langkah berikutnya berdasarkan implementation plan.

Ketentuan komunikasi:

1. Gunakan penjelasan yang ringkas dan spesifik.
2. Hindari klaim yang tidak didukung hasil pemeriksaan.
3. Jangan menyembunyikan error atau keterbatasan.
4. Jangan mengulang penjelasan yang tidak diperlukan.
5. Jangan memberikan daftar rekomendasi tambahan di luar scope tanpa alasan.

---

# 18. Definition of Done

Task hanya boleh dinyatakan selesai apabila:

- [ ] Implementasi sesuai kebutuhan.
- [ ] Tidak bertentangan dengan dokumentasi proyek.
- [ ] Tidak mengubah scope tanpa persetujuan.
- [ ] Tidak merusak existing functionality.
- [ ] Validasi dan authorization diterapkan sesuai kebutuhan.
- [ ] Database changes aman.
- [ ] Test yang relevan berhasil.
- [ ] Tidak ada error baru yang belum dijelaskan.
- [ ] Dokumentasi diperbarui jika diperlukan.
- [ ] `PROGRESS.md` diperbarui jika pekerjaan bermakna telah selesai.
- [ ] Laporan pekerjaan disampaikan secara jujur.

Jika terdapat item yang belum terpenuhi, jelaskan statusnya dan jangan mengklaim task selesai sepenuhnya.

---

# 19. Final Instruction

Prioritaskan implementasi yang benar, sederhana, konsisten, dan dapat dipelihara.

Jangan mengejar kompleksitas, jumlah file, atau banyaknya abstraksi.

Jangan membuat keputusan bisnis atau arsitektur sendiri.

Jangan melakukan perubahan di luar scope yang diminta.

**Documentation first. Inspect before modifying. Minimal implementation. Verify before reporting.**
