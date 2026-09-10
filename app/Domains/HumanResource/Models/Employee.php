<?php

declare(strict_types=1);

namespace App\Domains\HumanResource\Models;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\Evaluation\Models\KpiResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'nik',
        'phone',
        'office_id',
        'division_id',
        'position_id',
        'direct_supervisor_id',
        'manager_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Akun login yang terhubung (nullable — pegawai tanpa akun tetap valid). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'direct_supervisor_id');
    }

    /** Manajer struktural (di atas atasan langsung). */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'direct_supervisor_id');
    }

    public function assignmentsAsEvaluatee(): HasMany
    {
        return $this->hasMany(KpiAssignment::class, 'evaluatee_id');
    }

    public function assignmentsAsEvaluator(): HasMany
    {
        return $this->hasMany(KpiAssignment::class, 'evaluator_id');
    }

    public function kpiResults(): HasMany
    {
        return $this->hasMany(KpiResult::class, 'evaluatee_id');
    }
}
