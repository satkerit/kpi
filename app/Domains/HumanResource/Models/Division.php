<?php

declare(strict_types=1);

namespace App\Domains\HumanResource\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $fillable = ['name', 'code', 'parent_id', 'head_id', 'is_active'];

    /**
     * Divisi induk (null = divisi tingkat atas).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Sub-divisi.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'division_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'division_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'head_id');
    }
}
