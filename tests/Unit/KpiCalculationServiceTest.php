<?php

namespace Tests\Unit;

use App\Domains\Evaluation\Services\KpiCalculationService;
use PHPUnit\Framework\TestCase;

class KpiCalculationServiceTest extends TestCase
{
    public function test_determine_predicate_correctly(): void
    {
        $service = new KpiCalculationService;

        $this->assertEquals('Sangat Baik', $service->determinePredicate(9.6));
        $this->assertEquals('Baik', $service->determinePredicate(8.0));
        $this->assertEquals('Cukup Baik', $service->determinePredicate(6.0));
        $this->assertEquals('Buruk', $service->determinePredicate(4.0));
        $this->assertEquals('Sangat Buruk', $service->determinePredicate(3.0));
    }
}
