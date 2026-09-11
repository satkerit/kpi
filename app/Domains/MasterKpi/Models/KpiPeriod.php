<?php

declare(strict_types=1);

namespace App\Domains\MasterKpi\Models;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\Evaluation\Models\KpiResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiPeriod extends Model
{
    use HasFactory;

    /**
     * Pengaturan 360 default bila periode belum dikonfigurasi.
     *
     * @var array<string, mixed>
     */
    public const DEFAULT_SETTINGS = [
        'min_raters_per_group' => 3,     // ambang anonim: rata-rata grup dihitung jika >= N penilai
        'max_peers_per_evaluatee' => 6,  // batas jumlah penilai P2 per pegawai (mencegah ledakan O(n^2))
        'allow_not_observed' => true,    // izinkan item "Tidak Diamati" pada form penilaian
    ];

    protected $table = 'kpi_periods';

    protected $fillable = [
        'name',
        'year',
        'start_date',
        'end_date',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'settings' => 'array',
        ];
    }

    /**
     * Ambil satu nilai pengaturan periode dengan fallback ke default.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key]
            ?? $default
            ?? self::DEFAULT_SETTINGS[$key]
            ?? null;
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(KpiAssignment::class, 'period_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(KpiResult::class, 'period_id');
    }

    public function evaluatorWeights(): HasMany
    {
        return $this->hasMany(KpiEvaluatorWeight::class, 'period_id');
    }

    public function ratingScales(): HasMany
    {
        return $this->hasMany(RatingScale::class, 'period_id');
    }
}
