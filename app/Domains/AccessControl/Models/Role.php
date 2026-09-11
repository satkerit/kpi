<?php

declare(strict_types=1);

namespace App\Domains\AccessControl\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => User::bumpRbacVersion());
        static::deleted(fn () => User::bumpRbacVersion());
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role', 'role_id', 'user_id');
    }

    /**
     * Sinkronkan permission berdasarkan slug.
     *
     * @param  array<int, string>  $permissionSlugs
     */
    public function syncPermissions(array $permissionSlugs): void
    {
        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $this->permissions()->sync($permissionIds);
    }
}
