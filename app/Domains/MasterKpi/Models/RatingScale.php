<?php

declare(strict_types=1);

namespace App\Domains\MasterKpi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingScale extends Model
{
    use HasFactory;

    protected $table = 'rating_scales';

    protected $fillable = [
        'period_id',
        'label',
        'min_value',
        'max_value',
        'color',
        'predicate',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }
}
