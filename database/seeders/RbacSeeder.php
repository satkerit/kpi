<?php

namespace Database\Seeders;

use App\Domains\AccessControl\Models\Permission;
use App\Domains\AccessControl\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Daftar modul beserta permission-nya (slug => nama).
     *
     * @var array<string, array<string, string>>
     */
    private array $permissionMap = [
        'dashboard' => [
            'dashboard.view' => 'Lihat Dashboard',
        ],
        'employees' => [
            'employees.view' => 'Lihat Pegawai',
            'employees.create' => 'Tambah Pegawai',
            'employees.edit' => 'Ubah Pegawai',
            'employees.delete' => 'Hapus Pegawai',
        ],
        'users' => [
            'users.view' => 'Lihat User',
            'users.create' => 'Tambah User',
            'users.edit' => 'Ubah User',
            'users.delete' => 'Hapus User',
        ],
        'offices' => [
            'offices.view' => 'Lihat Kantor',
            'offices.manage' => 'Kelola Kantor',
        ],
        'divisions' => [
            'divisions.view' => 'Lihat Divisi',
            'divisions.manage' => 'Kelola Divisi',
        ],
        'departments' => [
            'departments.view' => 'Lihat Bagian',
            'departments.manage' => 'Kelola Bagian',
        ],
        'positions' => [
            'positions.view' => 'Lihat Jabatan',
            'positions.manage' => 'Kelola Jabatan',
        ],
        'roles' => [
            'roles.view' => 'Lihat Role',
            'roles.manage' => 'Kelola Role',
        ],
        'permissions' => [
            'permissions.view' => 'Lihat Permission',
            'permissions.manage' => 'Kelola Permission',
        ],
        'kpi' => [
            'kpi.view' => 'Lihat Rekap KPI',
            'kpi.evaluate' => 'Isi Evaluasi',
            'kpi.manage' => 'Kelola Periode & Kriteria',
        ],
    ];

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // Seed permissions
        foreach ($this->permissionMap as $module => $permissions) {
            foreach ($permissions as $slug => $name) {
                Permission::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'module' => $module],
                );
            }
        }

        // Role Super Admin — semua permission, proteksi sistem
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Akses penuh seluruh modul sistem', 'is_system' => true],
        );
        $superAdmin->permissions()->sync(Permission::all()->pluck('id'));

        // Role Admin HR — manajemen data kepegawaian
        $adminHr = Role::updateOrCreate(
            ['slug' => 'admin-hr'],
            ['name' => 'Admin HR', 'description' => 'Kelola data pegawai & struktur organisasi', 'is_system' => true],
        );
        $adminHr->syncPermissions([
            'dashboard.view',
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'offices.view', 'offices.manage',
            'divisions.view', 'divisions.manage',
            'departments.view', 'departments.manage',
            'positions.view', 'positions.manage',
            'kpi.view', 'kpi.manage',
        ]);

        // Role Evaluator — hanya mengisi evaluasi
        Role::updateOrCreate(
            ['slug' => 'evaluator'],
            ['name' => 'Evaluator', 'description' => 'Mengisi penilaian KPI 360 yang ditugaskan', 'is_system' => true],
        )->syncPermissions(['dashboard.view', 'kpi.view', 'kpi.evaluate']);

        // Role Viewer — hanya lihat rekap
        Role::updateOrCreate(
            ['slug' => 'viewer'],
            ['name' => 'Viewer', 'description' => 'Akses baca rekap KPI saja', 'is_system' => true],
        )->syncPermissions(['dashboard.view', 'kpi.view']);

        // Pastikan admin default memegang role super-admin
        $admin = User::where('email', 'admin@kpi360.co.id')->first();
        $admin?->assignRole('super-admin');
    }
}
