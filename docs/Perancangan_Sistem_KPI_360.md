# Perancangan Sistem Informasi KPI 360 Derajat
**Tech Stack:** Laravel 13, MySQL, Tailwind CSS
**Arsitektur:** MVC (Model-View-Controller), Object-Oriented Programming (OOP), Modular Design

---

## 1. Konsep Dasar Penerapan MVC & OOP
Sistem ini mematuhi standar desain perangkat lunak modern untuk memastikan kodenya mudah dirawat (*maintainable*) dan dikembangkan (*scalable*).

*   **Model (M):** Menggunakan fitur Eloquent ORM Laravel. Model akan merepresentasikan entitas bisnis secara *Object-Oriented*. Model difokuskan untuk menangani *relationship*, *mutators/accessors*, dan *query scopes*.
*   **View (V):** Dibangun menggunakan Blade Templating Engine. View akan dipecah menjadi komponen-komponen kecil (*Reusable Blade Components*) yang di-styling menggunakan Tailwind CSS.
*   **Controller (C):** Bertindak sebagai perantara murni (*traffic controller*). Controller hanya akan menerima *Request*, memanggil *Service Layer* (tempat *business logic* berada), dan mengembalikan *Response* atau *View*. Ini adalah prinsip *Fat Model/Service, Skinny Controller*.
*   **OOP Principles:** 
    *   *Encapsulation*: Variabel dan metode kelas disembunyikan menggunakan modifier `private/protected`.
    *   *Inheritance*: Penggunaan kelas induk (*BaseController*, *BaseService*).
    *   *Polymorphism*: Penggunaan *Interface* untuk mendefinisikan kontrak fungsi, seperti `EvaluatorInterface` untuk perhitungan tipe penilai yang berbeda (P1, P2, P3).

---

## 2. Struktur Direktori Modular (Domain-Driven)
Daripada mencampur semua logika di `app/Http/Controllers`, sistem menggunakan pola modular berbasis domain bisnis.

```text
app/
├── Domains/
│   ├── HumanResource/
│   │   ├── Models/ (Employee, Department, Position)
│   │   ├── Controllers/
│   │   └── Services/
│   ├── MasterKpi/
│   │   ├── Models/ (KpiPeriod, KpiCriteria)
│   │   ├── Controllers/
│   │   └── Services/
│   └── Evaluation/
│       ├── Models/ (KpiEvaluatorMap, KpiScore, KpiResult)
│       ├── Controllers/
│       ├── Services/ (ScoreCalculationService)
│       └── Interfaces/ (EvaluatorStrategyInterface)
├── View/
│   └── Components/ (Reusable Tailwind components like DataTable, Button, Modal)
```

---

## 3. Desain Database (MySQL)

| Tabel | Keterangan | Relasi |
| :--- | :--- | :--- |
| `employees` | Data karyawan | `1:N` dengan `kpi_evaluators` |
| `kpi_periods` | Periode Penilaian (Q1 2026, dst) | `1:N` dengan `kpi_evaluators` |
| `kpi_criteria` | Kriteria (Kedisiplinan, dll) | `1:N` dengan `kpi_scores` |
| `kpi_evaluators` | *Mapping* Penilai (Atasan, Rekan, Bawahan) | `N:1` dengan `employees`, `kpi_periods` |
| `kpi_scores` | Nilai mentah (skala 1-10) per kriteria | `N:1` dengan `kpi_evaluators`, `kpi_criteria` |
| `kpi_results` | Nilai akhir (rata-rata) & predikat | `N:1` dengan `employees`, `kpi_periods` |

---

## 4. Penerapan Clean Code & OOP (Contoh Kode)

### A. Interface & Strategy Pattern (OOP - Polymorphism)
Karena aturan pembobotan Atasan (P1), Rekan (P2), dan Bawahan (P3) berbeda, kita menggunakan *Strategy Pattern* agar kodenya bersih dan menghilangkan struktur `if-else` yang bersarang.

```php
namespace App\Domains\Evaluation\Interfaces;

interface EvaluatorStrategyInterface 
{
    public function calculateWeightedScore(array $rawScores): float;
}
```

### B. Service Layer (Pemisahan Business Logic)
Service ini menangani logika inti tanpa bergantung pada Request HTTP, membuatnya sangat *reusable* (bisa dipanggil dari Web Controller, API, atau Artisan Command).

```php
namespace App\Domains\Evaluation\Services;

use App\Domains\Evaluation\Models\KpiResult;
use App\Domains\Evaluation\Interfaces\EvaluatorStrategyInterface;

class KpiCalculationService 
{
    // Dependency Injection (OOP)
    public function __construct(
        protected EvaluatorStrategyInterface $evaluatorStrategy
    ) {}

    public function processFinalScore(int $evaluateeId, int $periodId): KpiResult 
    {
        // 1. Ambil data nilai mentah dari database
        // 2. Kalkulasi nilai menggunakan strategi (P1/P2/P3)
        // 3. Simpan ke database dalam DB::transaction()
    }
}
```

### C. Skinny Controller (MVC)
Controller sangat bersih, hanya merouting HTTP Request ke Service.

```php
namespace App\Domains\Evaluation\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Evaluation\Services\KpiCalculationService;
use Illuminate\Http\Request;

class EvaluationResultController extends Controller 
{
    public function store(Request $request, KpiCalculationService $service) 
    {
        // Validasi input
        $validated = $request->validate([
            'evaluatee_id' => 'required|exists:employees,id',
            'period_id' => 'required|exists:kpi_periods,id',
        ]);

        // Eksekusi Service
        $result = $service->processFinalScore($validated['evaluatee_id'], $validated['period_id']);

        return redirect()->route('kpi.index')->with('success', 'Nilai KPI berhasil dihitung.');
    }
}
```

---

## 5. UI/UX Reusability (Tailwind CSS + Blade)
Menerapkan *Don't Repeat Yourself* (DRY) pada bagian View. Komponen form seperti input dibuat sebagai objek modular.

**Definisi Komponen (`resources/views/components/forms/input.blade.php`):**
```html
@props(['name', 'label', 'type' => 'text', 'value' => ''])

<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $label }}
    </label>
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        value="{{ old($name, $value) }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
    >
    @error($name)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

**Penggunaan di View Utama (Modular UI):**
```html
<form action="{{ route('kpi.store') }}" method="POST">
    @csrf
    <x-forms.input name="raw_score" label="Nilai (1-10)" type="number" />
    <x-buttons.primary type="submit">Simpan Nilai</x-buttons.primary>
</form>
```
