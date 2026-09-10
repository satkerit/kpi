<?php

declare(strict_types=1);

namespace App\Domains\MasterKpi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiEvaluatorWeight extends Model
{
    use HasFactory;

    protected $table = 'kpi_evaluator_weights';

    protected $fillable = [
        'period_id',
        'evaluator_type',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }
}
