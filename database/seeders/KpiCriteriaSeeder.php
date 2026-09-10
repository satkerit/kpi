<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\MasterKpi\Models\KpiCriteria;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use Illuminate\Database\Seeder;

final class KpiCriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Criteria
        $kedisiplinan = KpiCriteria::firstOrCreate(
            ['name' => 'Kedisiplinan'],
            ['category' => 'P1', 'description' => 'Kriteria kedisiplinan karyawan']
        );

        $keterampilanTeknis = KpiCriteria::firstOrCreate(
            ['name' => 'Keterampilan Teknis'],
            ['category' => 'P1,P2', 'description' => 'Kriteria keterampilan teknis karyawan']
        );

        $kepribadian = KpiCriteria::firstOrCreate(
            ['name' => 'Kepribadian'],
            ['category' => 'P1,P2,P3', 'description' => 'Kriteria kepribadian karyawan']
        );

        // Subcriteria for P1 (Atasan) - 10 subcriteria
        $p1Subcriteria = [
            // Kedisiplinan (3 subcriteria)
            ['criteria_id' => $kedisiplinan->id, 'name' => 'Kehadiran Pegawai', 'description' => 'Kehadiran pegawai', 'weight' => 10.00, 'evaluator_type' => 'P1'],
            ['criteria_id' => $kedisiplinan->id, 'name' => 'Ketepatan Waktu', 'description' => 'Ketepatan waktu', 'weight' => 10.00, 'evaluator_type' => 'P1'],
            ['criteria_id' => $kedisiplinan->id, 'name' => 'Sanksi', 'description' => 'Sanksi', 'weight' => 10.00, 'evaluator_type' => 'P1'],

            // Keterampilan Teknis (4 subcriteria)
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Kemantapan dan kematangan bertugas', 'description' => 'Kemantapan dan kematangan bertugas', 'weight' => 10.00, 'evaluator_type' => 'P1'],
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Kecepatan penyelesaian tugas', 'description' => 'Kecepatan penyelesaian tugas', 'weight' => 10.00, 'evaluator_type' => 'P1'],
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Kerajinan', 'description' => 'Kerajinan', 'weight' => 10.00, 'evaluator_type' => 'P1'],
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Kualitas hasil kerja', 'description' => 'Kualitas hasil kerja', 'weight' => 10.00, 'evaluator_type' => 'P1'],

            // Kepribadian (3 subcriteria)
            ['criteria_id' => $kepribadian->id, 'name' => 'Kejujuran', 'description' => 'Kejujuran', 'weight' => 15.00, 'evaluator_type' => 'P1'],
            ['criteria_id' => $kepribadian->id, 'name' => 'Kedisiplinan dan loyalitas', 'description' => 'Kedisiplinan dan loyalitas', 'weight' => 10.00, 'evaluator_type' => 'P1'],
            ['criteria_id' => $kepribadian->id, 'name' => 'Penampilan', 'description' => 'Penampilan', 'weight' => 5.00, 'evaluator_type' => 'P1'],
        ];

        // Subcriteria for P2 (Rekan) - 7 subcriteria
        $p2Subcriteria = [
            // Keterampilan Teknis (4 subcriteria)
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Kemantapan dan kematangan bertugas', 'description' => 'Kemantapan dan kematangan bertugas', 'weight' => 10.00, 'evaluator_type' => 'P2'],
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Kecepatan penyelesaian tugas', 'description' => 'Kecepatan penyelesaian tugas', 'weight' => 10.00, 'evaluator_type' => 'P2'],
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Kerajinan', 'description' => 'Kerajinan', 'weight' => 10.00, 'evaluator_type' => 'P2'],
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Kualitas hasil kerja', 'description' => 'Kualitas hasil kerja', 'weight' => 20.00, 'evaluator_type' => 'P2'],

            // Kepribadian (3 subcriteria)
            ['criteria_id' => $kepribadian->id, 'name' => 'Kejujuran', 'description' => 'Kejujuran', 'weight' => 20.00, 'evaluator_type' => 'P2'],
            ['criteria_id' => $kepribadian->id, 'name' => 'Kedisiplinan dan loyalitas', 'description' => 'Kedisiplinan dan loyalitas', 'weight' => 15.00, 'evaluator_type' => 'P2'],
            ['criteria_id' => $kepribadian->id, 'name' => 'Penampilan', 'description' => 'Penampilan', 'weight' => 15.00, 'evaluator_type' => 'P2'],
        ];

        // Subcriteria for P3 (Bawahan) - 3 subcriteria
        $p3Subcriteria = [
            // Kepribadian (3 subcriteria)
            ['criteria_id' => $kepribadian->id, 'name' => 'Kejujuran', 'description' => 'Kejujuran', 'weight' => 40.00, 'evaluator_type' => 'P3'],
            ['criteria_id' => $kepribadian->id, 'name' => 'Kedisiplinan dan loyalitas', 'description' => 'Kedisiplinan dan loyalitas', 'weight' => 30.00, 'evaluator_type' => 'P3'],
            ['criteria_id' => $kepribadian->id, 'name' => 'Penampilan', 'description' => 'Penampilan', 'weight' => 30.00, 'evaluator_type' => 'P3'],
        ];

        // Subcriteria for SELF (Penilaian Diri Sendiri) - 5 subcriteria
        $selfSubcriteria = [
            ['criteria_id' => $kepribadian->id, 'name' => 'Penilaian diri — Kejujuran', 'description' => 'Refleksi diri aspek kejujuran', 'weight' => 25.00, 'evaluator_type' => 'SELF'],
            ['criteria_id' => $kepribadian->id, 'name' => 'Penilaian diri — Kedisiplinan', 'description' => 'Refleksi diri aspek kedisiplinan', 'weight' => 20.00, 'evaluator_type' => 'SELF'],
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Penilaian diri — Kualitas kerja', 'description' => 'Refleksi diri kualitas hasil kerja', 'weight' => 25.00, 'evaluator_type' => 'SELF'],
            ['criteria_id' => $keterampilanTeknis->id, 'name' => 'Penilaian diri — Kerajinan', 'description' => 'Refleksi diri aspek kerajinan', 'weight' => 20.00, 'evaluator_type' => 'SELF'],
            ['criteria_id' => $kepribadian->id, 'name' => 'Penilaian diri — Penampilan', 'description' => 'Refleksi diri penampilan profesional', 'weight' => 10.00, 'evaluator_type' => 'SELF'],
        ];

        // Insert all subcriteria
        foreach ([...$p1Subcriteria, ...$p2Subcriteria, ...$p3Subcriteria, ...$selfSubcriteria] as $subcriteria) {
            KpiSubcriteria::firstOrCreate(
                [
                    'criteria_id' => $subcriteria['criteria_id'],
                    'name' => $subcriteria['name'],
                    'evaluator_type' => $subcriteria['evaluator_type'],
                ],
                [
                    'description' => $subcriteria['description'],
                    'weight' => $subcriteria['weight'],
                ]
            );
        }

        $this->command->info('KPI Criteria and Subcriteria seeded successfully.');
        $this->command->info('P1 (Atasan): '.count($p1Subcriteria).' subcriteria');
        $this->command->info('P2 (Rekan): '.count($p2Subcriteria).' subcriteria');
        $this->command->info('P3 (Bawahan): '.count($p3Subcriteria).' subcriteria');
        $this->command->info('SELF (Diri Sendiri): '.count($selfSubcriteria).' subcriteria');
    }
}
