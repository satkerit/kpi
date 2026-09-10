<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Models;

use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Office;
use App\Domains\HumanResource\Models\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationRule extends Model
{
    protected $table = 'evaluation_rules';

    protected $fillable = [
        'name',
        'evaluator_type',
        'evaluator_position_id',
        'evaluatee_position_id',
        'scope_office_id',
        'scope_division_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function evaluatorPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'evaluator_position_id');
    }

    public function evaluateePosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'evaluatee_position_id');
    }

    public function scopeOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'scope_office_id');
    }

    public function scopeDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'scope_division_id');
    }
}
