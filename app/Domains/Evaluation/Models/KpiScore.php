<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Models;

use App\Domains\MasterKpi\Models\KpiSubcriteria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiScore extends Model
{
    use HasFactory;

    protected $table = 'kpi_scores';

    protected $fillable = [
        'assignment_id',
        'subcriteria_id',
        'raw_score',
        'weighted_score',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'raw_score' => 'decimal:2',
            'weighted_score' => 'decimal:2',
            'comment' => 'string',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(KpiAssignment::class, 'assignment_id');
    }

    public function subcriteria(): BelongsTo
    {
        return $this->belongsTo(KpiSubcriteria::class, 'subcriteria_id');
    }
}
