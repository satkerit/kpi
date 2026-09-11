<?php

use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\EmployeeAdminController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\PeriodConfigController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\UserAccountController;
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
    Route::get('/kpi', [KpiDashboardController::class, 'index'])
        ->middleware('permission:kpi.view')
        ->name('kpi.index');
    Route::get('/kpi/report', [KpiReportController::class, 'index'])
        ->middleware('permission:kpi.view')
        ->name('kpi.report');
    Route::post('/kpi/calculate-all', [EvaluationResultController::class, 'calculateAll'])
        ->middleware('permission:kpi.manage')
        ->name('kpi.calculateAll');
    Route::get('/kpi/export-csv', [KpiDashboardController::class, 'exportCsv'])
        ->middleware('permission:kpi.view')
        ->name('kpi.exportCsv');
    Route::post('/kpi/calculate', [EvaluationResultController::class, 'store'])
        ->middleware('permission:kpi.evaluate')
        ->name('kpi.calculate');
    Route::post('/assignments', [AssignmentManagementController::class, 'assign'])
        ->middleware('permission:assignments.manage')
        ->name('assignments.assign');

    Route::prefix('periods')->name('periods.')->middleware('permission:kpi.manage')->group(function () {
        Route::get('/', [KpiPeriodController::class, 'index'])->name('index');
        Route::get('/create', [KpiPeriodController::class, 'create'])->name('create');
        Route::post('/', [KpiPeriodController::class, 'store'])->name('store');
        Route::get('/{period}/edit', [KpiPeriodController::class, 'edit'])->whereNumber('period')->name('edit');
        Route::put('/{period}', [KpiPeriodController::class, 'update'])->whereNumber('period')->name('update');
        Route::delete('/{period}', [KpiPeriodController::class, 'destroy'])->whereNumber('period')->name('destroy');
    });

    Route::prefix('criteria')->name('criteria.')->middleware('permission:kpi.manage')->group(function () {
        Route::get('/', [KpiCriteriaController::class, 'index'])->name('index');
        Route::post('/', [KpiCriteriaController::class, 'storeCriteria'])->name('store');
        Route::put('/{criterion}', [KpiCriteriaController::class, 'updateCriteria'])->whereNumber('criterion')->name('update');
        Route::delete('/{criterion}', [KpiCriteriaController::class, 'destroyCriteria'])->whereNumber('criterion')->name('destroy');

        Route::post('/subcriteria', [KpiCriteriaController::class, 'storeSubcriteria'])->name('subcriteria.store');
        Route::put('/subcriteria/{subcriteria}', [KpiCriteriaController::class, 'updateSubcriteria'])->whereNumber('subcriteria')->name('subcriteria.update');
        Route::delete('/subcriteria/{subcriteria}', [KpiCriteriaController::class, 'destroySubcriteria'])->whereNumber('subcriteria')->name('subcriteria.destroy');
    });

    Route::prefix('assignments')->name('assignments.')->middleware('permission:assignments.manage')->group(function () {
        Route::get('/', [AssignmentManagementController::class, 'index'])->name('index');
        Route::post('/generate', [AssignmentManagementController::class, 'generate'])->name('generate');
        Route::post('/generate-peers', [AssignmentManagementController::class, 'generatePeers'])->name('generatePeers');
        Route::delete('/{assignment}', [AssignmentManagementController::class, 'destroy'])->whereNumber('assignment')->name('destroy');
    });

    /*
    |-----------------------------------------------------------------------
    | Admin: Manajemen Pegawai, User, Role & Permission
    | Prioritas mengikuti gate `permission:*` yang di-set lewat UI role.
    | Menambah/melepas permission di module "Role" langsung mengubah akses.
    |-----------------------------------------------------------------------
    */

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('employees', EmployeeAdminController::class)
            ->parameters(['employees' => 'employee'])
            ->except(['show'])
            ->middleware('permission:employees.view');

        Route::resource('offices', OfficeController::class)
            ->except(['show'])
            ->middleware('permission:offices.view');

        Route::resource('divisions', DivisionController::class)
            ->except(['show'])
            ->middleware('permission:divisions.view');

        Route::resource('positions', PositionController::class)
            ->except(['show'])
            ->middleware('permission:positions.view');

        Route::resource('users', UserAccountController::class)
            ->except(['show'])
            ->middleware('permission:users.view');

        Route::resource('roles', RoleController::class)
            ->except(['show'])
            ->middleware('permission:roles.view');

        Route::resource('permissions', PermissionController::class)
            ->except(['show'])
            ->middleware('permission:permissions.view');

        // Konfigurasi Pengaturan Sistem (System Setup)
        Route::get('system-settings', [SystemSettingController::class, 'show'])
            ->middleware('permission:settings.manage')
            ->name('system-settings.show');
        Route::post('system-settings', [SystemSettingController::class, 'save'])
            ->middleware('permission:settings.manage')
            ->name('system-settings.save');

        // Konfigurasi email pengirim OTP (SMTP Gmail)
        Route::get('mail-settings', [MailSettingController::class, 'show'])
            ->middleware('permission:settings.manage')
            ->name('mail-settings.show');
        Route::post('mail-settings', [MailSettingController::class, 'save'])
            ->middleware('permission:settings.manage')
            ->name('mail-settings.save');
        Route::post('mail-settings/test', [MailSettingController::class, 'test'])
            ->middleware('permission:settings.manage')
            ->name('mail-settings.test');

        // Konfigurasi bobot & skala per periode
        Route::get('periods/{period}/config', [PeriodConfigController::class, 'show'])
            ->middleware('permission:kpi.manage')
            ->whereNumber('period')
            ->name('periods.config');
        Route::post('periods/{period}/config/weights', [PeriodConfigController::class, 'saveWeights'])
            ->middleware('permission:kpi.manage')
            ->whereNumber('period')
            ->name('periods.config.weights');
        Route::post('periods/{period}/config/scales', [PeriodConfigController::class, 'saveScales'])
            ->middleware('permission:kpi.manage')
            ->whereNumber('period')
            ->name('periods.config.scales');
        Route::delete('periods/{period}/config/scales/{scale}', [PeriodConfigController::class, 'destroyScale'])
            ->middleware('permission:kpi.manage')
            ->whereNumber('period')
            ->whereNumber('scale')
            ->name('periods.config.scales.destroy');
        Route::post('periods/{period}/config/settings', [PeriodConfigController::class, 'saveSettings'])
            ->middleware('permission:kpi.manage')
            ->whereNumber('period')
            ->name('periods.config.settings');

        // Template and Import Routes
        Route::get('import/template/{type}', [ImportExportController::class, 'template'])
            ->middleware('permission:employees.edit,offices.manage')
            ->name('import.template');
        Route::post('import/{type}', [ImportExportController::class, 'import'])
            ->middleware('permission:employees.edit,offices.manage')
            ->name('import.store');
    });
});
