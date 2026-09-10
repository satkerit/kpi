<?php

declare(strict_types=1);

namespace App\Domains\HumanResource\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $fillable = ['name', 'code', 'head_id', 'is_active'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'division_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'head_id');
    }
}
