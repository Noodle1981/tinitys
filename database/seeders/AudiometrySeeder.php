<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AudiometrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = \App\Models\Patient::all();

        foreach ($patients as $patient) {
            // 1. Crear Audiometrías de ejemplo (Sesiones)
            \App\Models\PatientSession::create([
                'patient_id' => $patient->id,
                'type' => 'Evaluación Inicial',
                'audiometry_data' => [
                    'right' => ['500' => 10, '1000' => 15, '2000' => 20, '4000' => 30, '8000' => 45],
                    'left' => ['500' => 15, '1000' => 20, '2000' => 25, '4000' => 35, '8000' => 50]
                ],
                'created_at' => now()->subMonths(6),
            ]);

            \App\Models\PatientSession::create([
                'patient_id' => $patient->id,
                'type' => 'Seguimiento',
                'audiometry_data' => [
                    'right' => ['500' => 15, '1000' => 20, '2000' => 25, '4000' => 35, '8000' => 50],
                    'left' => ['500' => 20, '1000' => 25, '2000' => 30, '4000' => 40, '8000' => 55]
                ],
                'created_at' => now()->subMonths(1),
            ]);

            // 2. Crear Audífonos de ejemplo
            if ($patient->id % 2 === 0) {
                \App\Models\HearingAid::create([
                    'patient_id' => $patient->id,
                    'ear' => 'OD',
                    'brand' => 'Phonak',
                    'model' => 'Audéo Lumity',
                    'type' => 'RIC',
                    'technology_level' => 'L90 (Premium)',
                    'battery' => 'Recargable',
                    'fitting_date' => now()->subMonths(2),
                    'settings' => [
                        'gain_control' => 85,
                        'channels' => 20,
                        'tinnitus_masker_active' => true
                    ]
                ]);
            }
        }
    }
}
