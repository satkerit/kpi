# Rencana Implementasi Sistem KPI 360 Derajat

## Informasi Proyek & Arsitektur

- **Framework:** Laravel 13
- **Database:** MySQL
- **CSS / UI:** Tailwind CSS & Blade Components
- **Pola Desain:** Domain-Driven Modular Design (`app/Domains/`), Service Layer, Strategy Pattern (`EvaluatorStrategyInterface`)
- **SK Rujukan:** SK Direksi Nomor 016/SK-Dir/BSB.02/XII/2024
- **Karakteristik Penilaian:**
    - Lintas kantor dan lintas divisi (vertikal & horizontal).
    - Hubungan penilai-dinilai **langsung (maksimal 1 tingkat)**:
        - **P1 (Atasan Langsung):** 1 tingkat di atas dinilai (vertikal).
        - **P2 (Rekan Sejawat):** Setara/selevel dalam fungsi/proyek (horizontal).
        - **P3 (Bawahan Langsung):** 1 tingkat di bawah dinilai (vertikal).
    - **Kuantitas Penilai Dinamis & Kardinalitas:**
        - Tidak ada batasan kuota statis per kantor/cabang.
        - **One-to-Many Atasan ke Bawahan:** 1 atasan langsung dapat menilai banyak bawahan (P1), dan setiap bawahan menilai atasan tersebut (P3).
        - **Many-to-Many Rekan Sejawat:** 1 karyawan dapat memiliki dan dinilai oleh banyak rekan sejawat setara (P2).
        - Agregasi skor akhir mengakomodasi rata-rata nilai dari multi-penilai untuk kategori P2 dan P3.

---

## Formula & Perhitungan Nilai (Sesuai SK)

1. **Subkriteria:** `Nilai Tertimbang = (Nilai Input × Bobot) / 100`
2. **Total per Tipe Penilai:**
    - Total P1 = Sum(Nilai Tertimbang Kriteria Atasan) [10 Subkriteria]
    - Total P2 = Sum(Nilai Tertimbang Kriteria Rekan) [7 Subkriteria]
    - Total P3 = Sum(Nilai Tertimbang Kriteria Bawahan) [3 Subkriteria]
3. **Nilai Akhir:** `(Rata-rata P1 + Rata-rata P2 + Rata-rata P3) / 3`
4. **Kategori / Predikat:**
    - `> 9.5` : Sangat Baik
    - `> 7.5` s/d `9.5` : Baik
    - `> 5.5` s/d `7.5` : Cukup Baik
    - `> 3.5` s/d `5.5` : Buruk
    - `≤ 3.5` : Sangat Buruk

---

## Roadmap & Checklist Implementasi

### Fase 1: Fondasi & Basis Data

- [x] Inisialisasi struktur modul `app/Domains/{HumanResource, MasterKpi, Evaluation}`
- [x] Buat migration tabel organisasi:
    - [x] `offices` (kantor pusat / cabang)
    - [x] `divisions` (divisi / unit kerja)
    - [x] `positions` (level jabatan untuk validasi relasi 1 tingkat)
    - [x] `employees` (biodata, NIK, office_id, division_id, position_id, direct_supervisor_id)
- [x] Buat migration tabel master KPI:
    - [x] `kpi_periods` (nama, semester, tahun, tgl_mulai, tgl_selesai, status)
    - [x] `kpi_criteria` (nama kriteria, kategori)
    - [x] `kpi_subcriteria` (nama subkriteria, bobot, evaluator_type: P1/P2/P3)
- [x] Buat migration tabel transaksi & penilaian:
    - [x] `kpi_assignments` (period_id, evaluatee_id, evaluator_id, evaluator_type, status)
    - [x] `kpi_scores` (assignment_id, subcriteria_id, raw_score, weighted_score)
    - [x] `kpi_results` (period_id, evaluatee_id, score_p1, score_p2, score_p3, final_score, predicate)
- [x] Buat Model & Relasi Eloquent untuk seluruh entitas
- [x] Buat Seeder Master Data (Kriteria, Subkriteria, & Bobot sesuai Lampiran SK) — seeding tereksekusi untuk SQLite dev

### Fase 2: Autentikasi & Validasi Relasi 1 Tingkat

- [ ] Setup autentikasi & RBAC (Role-Based Access Control: Admin, HR, Pegawai)
- [x] Implementasi aturan validasi relasi organisasi (Rule Objects / Service Validator):
    - [x] Validasi Atasan Langsung (P1: tepat 1 tingkat di atas)
    - [x] Validasi Bawahan Langsung (P3: tepat 1 tingkat di bawah)
    - [x] Validasi Rekan Sejawat (P2: level setara, lintas/sama divisi & kantor) — via P1/P2/P3 Strategies
- [ ] Middleware & Authorization Policies untuk akses form penilaian

### Fase 3: Modul Master Data & Penugasan Penilai (Dynamic Assignment)

- [ ] Fitur CRUD Periode Penilaian KPI
- [ ] Fitur CRUD Master Kriteria, Subkriteria, dan Bobot
- [ ] Fitur Manajemen Hubungan & Penugasan Penilai (`kpi_assignments`):
    - [ ] Penugasan dinamis (jumlah penilai tidak dibatasi kuota statis)
    - [ ] Search & Select karyawan lintas kantor dan divisi dengan filter relasi valid
    - [ ] Generate otomatis penugasan P1 dan P3 berdasarkan struktur hirarki jabatan
    - [ ] Custom mapping penilai P2 rekan kerja sejawat

### Fase 4: Core Engine - Form Penilaian & Kalkulasi Strategy Pattern

- [x] Implementasi Interface `EvaluatorStrategyInterface`
- [x] Implementasi Strategi Kalkulasi Konkret:
    - [x] `SuperiorEvaluationStrategy` (P1 - 10 Subkriteria)
    - [x] `PeerEvaluationStrategy` (P2 - 7 Subkriteria)
    - [x] `SubordinateEvaluationStrategy` (P3 - 3 Subkriteria)
- [x] Implementasi Service Layer `KpiCalculationService`:
    - [x] Agregasi skor per tipe evaluator
    - [x] Kalkulasi rata-rata per role jika terdapat multiple evaluators
    - [x] Eksekusi formula SK dalam `DB::transaction()`
    - [x] Penentuan predikat kinerja otomatis
    - [x] UI Form Penilaian (Tailwind CDN, dynamic subcriteria per P1/P2/P3)
- [x] Routes `/kpi`, `/assignments`, `/evaluations/{id}/form|submit` — `php artisan route:list` ✅

### Fase 5: Rekapitulasi, Dashboard, & Pelaporan

- [ ] Dashboard Progres Penilaian (Pantau penyelesaian per kantor & divisi)
- [ ] Halaman Rekapitulasi Nilai Akhir Karyawan
- [ ] Filter rekapitulasi multi-dimensi:
    - [ ] Filter per Kantor Cabang/Pusat
    - [ ] Filter per Divisi
    - [ ] Filter per Periode
- [ ] Export Laporan Rekapitulasi ke Excel & PDF (Format Berita Acara / Lampiran SK)
- [ ] Tampilan Rincian Lembar Evaluasi Individual (Rekap Nilai P1, P2, P3 & Tanda Tangan)

### Fase 6: Quality Assurance, Security, & Deployment

- [ ] Unit Test untuk kalkulasi skor dan penentuan predikat
- [ ] Feature Test alur pengisian form dan proteksi data penilaian
- [ ] Optimasi query (Eager loading hubungan bertingkat untuk mencegah N+1 problem)
- [ ] Audit trail & logging perubahan nilai / status evaluasi
- [ ] Final checking kesesuaian output dengan Lampiran SK
