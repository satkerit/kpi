<?php

declare(strict_types=1);

namespace App\Domains\MasterKpi\Models;

use App\Domains\Evaluation\Models\KpiScore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiSubcriteria extends Model
{
    use HasFactory;

    protected $table = 'kpi_subcriteria';

    protected $fillable = [
        'criteria_id',
        'name',
        'description',
        'weight',
        'evaluator_type',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(KpiCriteria::class, 'criteria_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(KpiScore::class, 'subcriteria_id');
    }
}
