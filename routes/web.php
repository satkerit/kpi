<?php

use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\EmployeeAdminController;
use App\Http\Controllers\Admin\EvaluationRuleController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\PeriodConfigController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\QuestionnaireController;
use App\Http\Controllers\EvaluationFormController;
use App\Http\Controllers\EvaluationResultController;
use App\Http\Controllers\KpiCriteriaController;
use App\Http\Controllers\KpiDashboardController;
use App\Http\Controllers\KpiPeriodController;
use App\Http\Controllers\KpiReportController;
use Illuminate\Support\Facades\Route;

/*
|---------------------------------------------------------------------------
| Authentication Routes
|---------------------------------------------------------------------------
|
| Login / Logout using session-based authentication (web guard).
| Guests are redirected to login; authenticated users are sent to dashboard.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');

    // Alur Kuesioner via OTP (Guest)
    // Rate limit: maks 5 request OTP / IP / menit; maks 20 verifikasi / IP / menit
    Route::get('/kuesioner', [QuestionnaireController::class, 'start'])->name('questionnaire.start');
    Route::post('/kuesioner/request-otp', [QuestionnaireController::class, 'requestOtp'])
        ->middleware('throttle:5,1')
        ->name('questionnaire.otp.request');
    Route::get('/kuesioner/verifikasi', [QuestionnaireController::class, 'showVerify'])->name('questionnaire.verify.form');
    Route::post('/kuesioner/verifikasi', [QuestionnaireController::class, 'verify'])
        ->middleware('throttle:20,1')
        ->name('questionnaire.verify');
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

/*
|---------------------------------------------------------------------------
| Root Redirection
|---------------------------------------------------------------------------
|
| Root URL redirects to login for guests, or dashboard for authenticated users.
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('kpi.index')
        : redirect()->route('login');
})->name('home');

/*
|---------------------------------------------------------------------------
| Kuesioner KPI (akses via sesi OTP — pegawai TIDAK wajib punya akun login)
|---------------------------------------------------------------------------
|
| Middleware `kpi.employee` meresolusi pegawai penilai dari sesi OTP
| (atau fallback akun login). Tanpa identitas valid → dialihkan ke /kuesioner.
*/

Route::middleware('kpi.employee')->group(function () {
    Route::get('/kuesioner/form', [QuestionnaireController::class, 'form'])->name('questionnaire.form');
    Route::post('/kuesioner/submit-all', [QuestionnaireController::class, 'submitAll'])->name('questionnaire.submitAll');

    Route::prefix('evaluations')->name('evaluations.')->group(function () {
        Route::get('/{assignment}/form', [EvaluationFormController::class, 'show'])
            ->whereNumber('assignment')
            ->name('form');

        Route::post('/{assignment}/submit', [EvaluationFormController::class, 'submit'])
            ->whereNumber('assignment')
            ->name('submit');
    });
});

/*
|---------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|---------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/kpi', [KpiDashboardController::class, 'index'])->name('kpi.index');
    Route::get('/kpi/report', [KpiReportController::class, 'index'])->name('kpi.report');
    Route::post('/kpi/calculate-all', [EvaluationResultController::class, 'calculateAll'])->name('kpi.calculateAll');
    Route::get('/kpi/export-csv', [KpiDashboardController::class, 'exportCsv'])->name('kpi.exportCsv');
    Route::post('/kpi/calculate', [EvaluationResultController::class, 'store'])->name('kpi.calculate');
    Route::post('/assignments', [AssignmentController::class, 'assign'])->name('assignments.assign');

    Route::prefix('periods')->name('periods.')->group(function () {
        Route::get('/', [KpiPeriodController::class, 'index'])->name('index');
        Route::get('/create', [KpiPeriodController::class, 'create'])->name('create');
        Route::post('/', [KpiPeriodController::class, 'store'])->name('store');
        Route::get('/{period}/edit', [KpiPeriodController::class, 'edit'])->whereNumber('period')->name('edit');
        Route::put('/{period}', [KpiPeriodController::class, 'update'])->whereNumber('period')->name('update');
        Route::delete('/{period}', [KpiPeriodController::class, 'destroy'])->whereNumber('period')->name('destroy');
    });

    Route::prefix('criteria')->name('criteria.')->group(function () {
        Route::get('/', [KpiCriteriaController::class, 'index'])->name('index');
        Route::post('/subcriteria', [KpiCriteriaController::class, 'storeSubcriteria'])->name('subcriteria.store');
        Route::put('/subcriteria/{subcriteria}', [KpiCriteriaController::class, 'updateSubcriteria'])->whereNumber('subcriteria')->name('subcriteria.update');
        Route::delete('/subcriteria/{subcriteria}', [KpiCriteriaController::class, 'destroySubcriteria'])->whereNumber('subcriteria')->name('subcriteria.destroy');
    });

    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [AssignmentManagementController::class, 'index'])->name('index');
        Route::post('/generate', [AssignmentManagementController::class, 'generate'])->name('generate');
        Route::post('/generate-peers', [AssignmentManagementController::class, 'generatePeers'])->name('generatePeers');
        Route::delete('/{assignment}', [AssignmentManagementController::class, 'destroy'])->whereNumber('assignment')->name('destroy');
    });

    /*
    |-----------------------------------------------------------------------
    | Admin: Manajemen Pegawai, User, Role & Permission
    |-----------------------------------------------------------------------
    */

    Route::prefix('admin')->name('admin.')->middleware('role:super-admin,admin-hr')->group(function () {
        Route::resource('employees', EmployeeAdminController::class)
            ->parameters(['employees' => 'employee'])
            ->except(['show']);

        Route::resource('offices', OfficeController::class)->except(['show']);
        Route::resource('divisions', DivisionController::class)->except(['show']);
        Route::resource('positions', PositionController::class)->except(['show']);

        Route::resource('users', UserAccountController::class)
            ->only(['index', 'edit', 'update', 'destroy']);

        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('permissions', PermissionController::class)->except(['show']);

        Route::resource('evaluation-rules', EvaluationRuleController::class)->except(['show']);
        Route::post('evaluation-rules/generate', [EvaluationRuleController::class, 'generate'])->name('evaluation-rules.generate');

        // Konfigurasi email pengirim OTP (SMTP Gmail)
        Route::get('mail-settings', [MailSettingController::class, 'show'])->name('mail-settings.show');
        Route::post('mail-settings', [MailSettingController::class, 'save'])->name('mail-settings.save');
        Route::post('mail-settings/test', [MailSettingController::class, 'test'])->name('mail-settings.test');

        // Konfigurasi bobot & skala per periode
        Route::get('periods/{period}/config', [PeriodConfigController::class, 'show'])->name('periods.config');
        Route::post('periods/{period}/config/weights', [PeriodConfigController::class, 'saveWeights'])->name('periods.config.weights');
        Route::post('periods/{period}/config/scales', [PeriodConfigController::class, 'saveScales'])->name('periods.config.scales');
        Route::delete('periods/{period}/config/scales/{scale}', [PeriodConfigController::class, 'destroyScale'])->name('periods.config.scales.destroy');
        Route::post('periods/{period}/config/settings', [PeriodConfigController::class, 'saveSettings'])->name('periods.config.settings');

        // Template and Import Routes
        Route::get('import/template/{type}', [ImportExportController::class, 'template'])->name('import.template');
        Route::post('import/{type}', [ImportExportController::class, 'import'])->name('import.store');
    });
});
