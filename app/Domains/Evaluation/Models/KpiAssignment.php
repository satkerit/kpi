<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Models;

use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiAssignment extends Model
{
    use HasFactory;

    protected $table = 'kpi_assignments';

    protected $fillable = [
        'period_id',
        'evaluatee_id',
        'evaluator_id',
        'evaluator_type',
        'status',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluatee_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(KpiScore::class, 'assignment_id');
    }

    public function getFormKeyAttribute(): string
    {
        return $this->exists ? (string) $this->id : "target_{$this->evaluatee_id}";
    }
}
