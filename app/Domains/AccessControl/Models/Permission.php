<?php

declare(strict_types=1);

namespace App\Domains\AccessControl\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => User::bumpRbacVersion());
        static::deleted(fn () => User::bumpRbacVersion());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
