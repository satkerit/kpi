<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Models;

use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiResult extends Model
{
    use HasFactory;

    protected $table = 'kpi_results';

    protected $fillable = [
        'period_id',
        'evaluatee_id',
        'score_p1',
        'score_p2',
        'score_p3',
        'score_self',
        'final_score',
        'predicate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'score_p1' => 'decimal:2',
            'score_p2' => 'decimal:2',
            'score_p3' => 'decimal:2',
            'score_self' => 'decimal:2',
            'final_score' => 'decimal:2',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluatee_id');
    }
}
