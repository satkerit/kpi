<?php

declare(strict_types=1);

namespace App\Domains\MasterKpi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiCriteria extends Model
{
    use HasFactory;

    protected $table = 'kpi_criteria';

    protected $fillable = [
        'name',
        'category',
        'description',
    ];

    public function subcriteria(): HasMany
    {
        return $this->hasMany(KpiSubcriteria::class, 'criteria_id');
    }
}
