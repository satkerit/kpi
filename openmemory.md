# OpenMemory Guide - KPI 360 System

## User Defined Namespaces

- organization
- master_kpi
- evaluation

## Overview

Sistem Penilaian KPI 360 Derajat berbasis Laravel & MySQL sesuai SK Direksi No 016/SK-Dir/BSB.02/XII/2024.
Mendukung penilaian vertikal (P1: Atasan→Bawahan, P3: Bawahan→Atasan), horizontal (P2: Rekan Sejawat), dan SELF (Diri Sendiri).
Framework: Laravel 11+, PHP 8.4, MySQL prod / SQLite :memory: test.

## Architecture

- Modular Domain: `app/Domains/{HumanResource,MasterKpi,Evaluation,AccessControl}`
- Service Layer: `KpiCalculationService` (weighted formula), `AssignmentGeneratorService`, `QuestionnaireAssignmentService`, `RuleBasedAssignmentService`
- Strategy Pattern: `StrategyFactory` → P1/P2/P3Strategy + `SelfEvaluationStrategy`
- RBAC: custom pivot `user_role`, trait `HasRoles` — guard `web` provider `users` model `User::class`
- Auth: `User extends Authenticatable` — hanya akun login. `Employee` standalone HR data.

## Employee vs User (KRITIS)

- `employees` tabel mandiri — TIDAK extend `Authenticatable`, TIDAK kait `user_role`.
- `employees.user_id` nullable FK ke `users` — boleh null (pegawai belum punya akun).
- `User::employee()` HasOne ke Employee.
- **Kuesioner OTP: pegawai TIDAK wajib punya akun login.** Alur: NIK → OTP → sesi kuesioner (marker session, TANPA `Auth::loginUsingId`).
- Sesi kuesioner: `questionnaire.employee_id` + `questionnaire.expires_at` (2 jam) + `questionnaire.bind` (SHA256 IP+UA) disimpan di session.
- Middleware `ResolveKpiEmployee` (`kpi.employee`): resolusi dari sesi OTP (prioritas) atau fallback akun login biasa.
- Import pegawai: pisah `User::firstOrCreate` + `Employee::updateOrCreate(user_id)`.
- Validasi controller: semua `exists:employees,id` — BUKAN `exists:users,id`.
- **Trap RBAC**: method/relasi role (`getRoleSlugs`, `roles`, `syncRoles`) hanya ada di `User` (trait `HasRoles`) — BUKAN di `Employee`. Di view pegawai selalu lewat `$employee->user?->...` + eager load `user.roles:id,name,slug`.

### Middleware

- `CheckRole` (`role`): cek `user->hasRole(...$roles)` — hanya untuk rute admin (web guard).
- `ResolveKpiEmployee` (`kpi.employee`): resolusi pegawai penilai. Jalur 1: sesi OTP (marker session TTL 2 jam, terikat IP+UA via SHA256). Jalur 2: fallback `Auth::user()->employee`. Hasil: `request->attributes->set('kpi_employee', $employee)`.
- Rute kuesioner + evaluations pakai `kpi.employee` (bukan `auth`) sehingga pegawai tanpa akun login bisa mengisi KPI.

### HumanResource Domain

- `Employee`: standalone, fillable HR fields (`name`, `email`, `nik`, `phone`, `office_id`, `division_id`, `department_id`, `position_id`, `direct_supervisor_id`, `manager_id`, `is_active`), relasi user/office/division/department/position/supervisor/manager.
- `Office`: type enum `head_office|branch|kpo|kas`, `branch_code` wajib bila kas.
- `Division`, `Department`: `parent_id` FK self-referencing hierarchy, `category` (`operasional`, `bisnis`).
- `Position`: `level` integer untuk hierarki.

### MasterKpi Domain

- `KpiPeriod`: period penilaian, status `draft|active|closed`. Kolom `settings` JSON untuk config 360 dinamis.
    - `KpiPeriod::DEFAULT_SETTINGS`: `min_raters_per_group=3`, `max_peers_per_evaluatee=6`, `allow_not_observed=true`.
    - Helper `$period->setting('key')`: baca settings JSON dengan fallback ke DEFAULT_SETTINGS.
- `KpiEvaluatorWeight`: bobot per `(period_id, evaluator_type)` — P1/P2/P3/SELF, upsert per periode.
- `RatingScale`: skala predikat per `period_id` — label/min_value/max_value/color/predicate/sort_order.
- `KpiCriteria` + `KpiSubcriteria`: kriteria dengan `evaluator_type` enum P1/P2/P3/SELF dan `weight`.

### Evaluation Domain

- `KpiAssignment`: `evaluator_type` enum P1/P2/P3/SELF, FK evaluatee+evaluator ke `employees`.
- `KpiScore`: `raw_score` (nullable = "Tidak Diamati"), `weighted_score`, `comment` (umpan balik kualitatif).
- `KpiResult`: `score_p1/p2/p3/score_self/final_score/predicate` per pegawai per periode.
- `EvaluationRule`: rule berbasis jabatan untuk generate assignment otomatis.

## Patterns

### Formula Weighted Final Score

```
Final = Σ(avg_type × weight_type) / Σ(weight_type yang ada datanya)
```

- Bobot diambil dari `kpi_evaluator_weights` per periode (fallback 1.0 semua sama).
- Default bobot: P1=1.50, P2=1.00, P3=0.75, SELF=0.75.
- Normalisasi bobot: item "Tidak Diamati" (raw_score null) dikeluarkan, bobot dinormalisasi ulang.

### Anonymity Threshold (min_raters_per_group)

- Grup tipe evaluator hanya dihitung jika jumlah penilai `submitted` >= `min_raters_per_group`.
- SELF dikecualikan (selalu 1 penilai = diri sendiri, threshold = 1).
- Konfigurasi dinamis per periode via `kpi_periods.settings` JSON.

### P2 Peer Limit (max_peers_per_evaluatee)

- `AssignmentGeneratorService::generatePeersForPeriod()` batasi jumlah penilai P2 per pegawai.
- Jika group > limit: random sample `$maxPeers` penilai (mencegah ledakan O(n²)).
- Default: 6 penilai. Nilai 0 = tidak dibatasi.

### Opsi "Tidak Diamati" (allow_not_observed)

- `kpi_scores.raw_score` nullable — null berarti item tidak diamati.
- Pada form penilaian: checkbox "Tidak Diamati" disable input skor via JS.
- `EvaluationFormController::submit()`: simpan null, weighted_score=0.
- `KpiCalculationService`: filter `raw_score !== null` saat rata-rata per tipe.

### Skala Predikat (dinamis per periode)

- Ambil dari `rating_scales` per `period_id` — fallback hardcode bila belum ada.
- Default: Sangat Baik ≥8.5, Baik ≥7.0, Cukup Baik ≥5.5, Buruk ≥3.5, Sangat Buruk <3.5.
- Fallback sinkron dengan DefaultPeriodConfigSeeder (pakai `>=`, bukan `>`).

### Input Score Dinamis

- `EvaluationFormController::maxScoreFor()` ambil `max(max_value)` dari `rating_scales` periode.
- Validasi `max:{$maxScore}` + `<input max="{{ $maxScore }}">` — bukan hardcode 10.

### SQLite Guard (testing)

- Migrasi `170757` + `170758`: early return bila `DB::getDriverName() === 'sqlite'`.
- Migrasi create table (`kpi_assignments`, `kpi_subcriteria`, `evaluation_rules`): enum sudah include SELF.

## Admin Config 360

Route `admin.periods.config.settings` → `PeriodConfigController::saveSettings()`.
View `admin/period_config/show.blade.php`: 3 kartu — Bobot Evaluator, Pengaturan 360, Skala Penilaian.
Pengaturan 360: min_raters_per_group, max_peers_per_evaluatee, allow_not_observed (semua per periode).

## Setup Email OTP (SMTP Gmail)

Konfigur runtime via DB (bukan .env) agar admin ubah tanpa deploy.

- Model `AppSetting` (key-value + cache 300s): `get($key,$default)` / `set($key,$value)`.
- Controller `Admin\MailSettingController`: `show()`/`save()`/`test()`; const `MAIL_KEYS` (metadata UI); `applyMailConfig()` static → `Config::set()` mail.default + mail.mailers.smtp._ + mail.from._ dari DB.
- Route `admin.mail-settings.{show,save,test}` (grup admin, role super-admin/admin-hr).
- View `admin/mail_setting/show.blade.php`: form SMTP + tombol tes email. Password secret — kosongkan = jangan timpa.
- `QuestionnaireController::requestOtp()` panggil `MailSettingController::applyMailConfig()` sebelum `Mail::send()`.
- Seeder `MailSettingSeeder`: default Gmail (smtp.gmail.com:587 tls, from_name "KPI 360 System", password kosong diisi via UI).
- Nav sidebar: "Setup Email OTP".

## Seeders

- `DatabaseSeeder`: buat admin user → `KpiCriteriaSeeder` → `RbacSeeder` → `DefaultPeriodConfigSeeder` → `MailSettingSeeder`.
- `KpiCriteriaSeeder`: subcriteria P1 (10) + P2 (7) + P3 (3) + SELF (5).
- `DefaultPeriodConfigSeeder`: seed bobot + skala default untuk semua periode yang sudah ada.

## Migrasi Penting

- `2026_09_10_014738_create_app_settings_table`: tabel key-value `app_settings` (key unique, value, label, group, is_secret).
- `2026_09_09_210915_add_settings_to_kpi_periods_table`: kolom `settings` JSON nullable.
- `2026_09_09_210916_add_comment_to_kpi_scores_table`: kolom `comment` text nullable + `raw_score` nullable.
- `2026_09_09_175502_add_score_self_to_kpi_results_table`: kolom `score_self` decimal.
- `170757` + `170758`: patch FK ke employees, guard sqlite.
