<?php

declare(strict_types=1);

namespace App\Domains\AccessControl\Traits;

use App\Domains\AccessControl\Models\Permission;
use App\Domains\AccessControl\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    /**
     * Cek apakah user memiliki salah satu role (by slug).
     */
    public function hasRole(string ...$slugs): bool
    {
        if (in_array('super-admin', $this->getRoleSlugs(), true)) {
            return true;
        }

        return count(array_intersect($slugs, $this->getRoleSlugs())) > 0;
    }

    /**
     * Cek permission via role, dengan cache per-user.
     */
    public function hasPermission(string $slug): bool
    {
        if (in_array('super-admin', $this->getRoleSlugs(), true)) {
            return true;
        }

        $permissionSlugs = $this->getPermissionSlugs();

        return in_array($slug, $permissionSlugs, true);
    }

    /**
     * @return array<int, string>
     */
    public function getRoleSlugs(): array
    {
        return Cache::remember(
            "rbac.user.{$this->id}.roles",
            now()->addMinutes(30),
            fn () => $this->roles()->pluck('slug')->all(),
        );
    }

    /**
     * @return array<int, string>
     */
    public function getPermissionSlugs(): array
    {
        return Cache::remember(
            "rbac.user.{$this->id}.permissions",
            now()->addMinutes(30),
            function () {
                return $this->roles()
                    ->with('permissions')
                    ->get()
                    ->flatMap(fn (Role $role) => $role->permissions->pluck('slug'))
                    ->unique()
                    ->values()
                    ->all();
            },
        );
    }

    /**
     * Assign role berdasarkan slug (idempotent).
     */
    public function assignRole(string ...$slugs): void
    {
        $roleIds = Role::whereIn('slug', $slugs)->pluck('id');
        $this->roles()->syncWithoutDetaching($roleIds);
        $this->clearRbacCache();
    }

    /**
     * Hapus role berdasarkan slug.
     */
    public function removeRole(string ...$slugs): void
    {
        $roleIds = Role::whereIn('slug', $slugs)->pluck('id');
        $this->roles()->detach($roleIds);
        $this->clearRbacCache();
    }

    /**
     * Sinkronkan seluruh role user.
     *
     * @param  array<int, string>  $slugs
     */
    public function syncRoles(array $slugs): void
    {
        $roleIds = Role::whereIn('slug', $slugs)->pluck('id');
        $this->roles()->sync($roleIds);
        $this->clearRbacCache();
    }

    public function clearRbacCache(): void
    {
        Cache::forget("rbac.user.{$this->id}.roles");
        Cache::forget("rbac.user.{$this->id}.permissions");
    }

    /**
     * Daftar seluruh permission yang terdaftar pada sistem (untuk form).
     *
     * @return Collection<int, Permission>
     */
    public static function allPermissions()
    {
        return Permission::orderBy('module')->orderBy('slug')->get();
    }
}
