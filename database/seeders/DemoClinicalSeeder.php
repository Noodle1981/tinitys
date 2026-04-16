<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\TinnitusMapping;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DemoClinicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Crear Usuario Doctor (Dr. House)
            $doctorUser = User::updateOrCreate(
                ['email' => 'doctor@example.com'],
                [
                    'name' => 'Dr. House',
                    'password' => Hash::make('password'),
                    'role' => 'doctor',
                    'email_verified_at' => now(),
                ]
            );

            // 2. Crear Usuario Paciente (Omar Olivera)
            $patientUser = User::updateOrCreate(
                ['email' => 'paciente@example.com'],
                [
                    'name' => 'Omar Olivera',
                    'password' => Hash::make('password'),
                    'role' => 'patient',
                    'email_verified_at' => now(),
                ]
            );

            // 3. Crear Ficha Clínica Base (Identidad)
            $patient = Patient::updateOrCreate(
                ['dni' => '28891983'],
                [
                    'name' => 'Omar Olivera',
                    'user_id' => $patientUser->id,
                    'doctor_id' => $doctorUser->id,
                    'birth_date' => '1981-09-01',
                    'gender' => 'Masculino',
                    'occupation' => 'Administrativo',
                    'laterality' => 'Bilateral',
                    'sound_type' => ["Pitido", "Rugido", "Zumbido", "Siseo", "Pulsátil", "Otros"],
                    'address' => 'Carlos Gardel Casa 27 B° Enoe Bravo',
                    'city' => 'Santa Lucía',
                    'province' => 'San Juan',
                    'phone' => '2644533704',
                    'civil_status' => 'Casado',
                    'has_children' => true,
                    'work_status' => 'Activo',
                ]
            );

            // --- DATOS RELACIONALES (LO QUE NO ESTABA EN LA SEMILLA) ---

            // 4. Hábitos (Cuantificados)
            $patient->habits()->updateOrCreate([], [
                'smoking_level' => 2, // Bajo
                'alcohol_level' => 3, // Ocasional
                'coffee_level' => 8,  // Elevado (8 tazas/día demo)
            ]);

            // 5. Exposiciones
            $patient->exposure()->updateOrCreate([], [
                'occupational_noise_level' => 3, 
                'leisure_noise_level' => 5, 
                'noise_duration_years' => 10,
                'protection_used' => true,
                'recent_exposure' => false,
            ]);

            // 6. Causas de Inicio (El Origen)
            $patient->onsetCause()->updateOrCreate([], [
                'onset_date' => '2001-01-15',
                'onset_type' => 'Gradual',
                'initial_intensity_eva' => 4,
                'initial_sound_types' => ["Pitido", "Siseo"],
                'has_vertigo' => true,
                'has_head_trauma' => false,
            ]);

            // 7. Historia Clínica (Ototóxicos, Proxies, Sueño)
            $patient->clinicalHistory()->updateOrCreate([], [
                'uses_salicylates' => true, // Demo
                'has_rheumatism' => true,   // Proxy para aspirinas
                'has_hypertension' => true, 
                'has_otalgia' => true,
                'has_otorrhea' => false,
                'family_hearing_loss' => true,
                'intensity_eva' => 8,  // Intensidad Actual
                'distress_eva' => 7,   // Molestia Actual
                'sleep_quality' => 4,  // Duerme mal
                'avg_sleep_hours' => 6,
                'has_night_shifts' => false,
                'mental_health_context' => 'Estrés laboral moderado, ansiedad por acúfenos crónicos.',
                'medications' => 'Aspirina ocasional para cefaleas.',
            ]);

            // 8. Sesión de Audiometría y Mapeo (Legacy data preservada)
            $this->seedLegacySessions($patient, $doctorUser);
        });
    }

    private function seedLegacySessions($patient, $doctorUser)
    {
        $audiometryData = [
            "oido_derecho" => [
                ["frecuencia_hz" => 250, "umbral_db" => 80],
                ["frecuencia_hz" => 500, "umbral_db" => 90],
                ["frecuencia_hz" => 750, "umbral_db" => 90],
                ["frecuencia_hz" => 1000, "umbral_db" => 90],
                ["frecuencia_hz" => 1500, "umbral_db" => 90],
                ["frecuencia_hz" => 2000, "umbral_db" => 100],
                ["frecuencia_hz" => 3000, "umbral_db" => 110],
                ["frecuencia_hz" => 4000, "umbral_db" => 110]
            ],
            "oido_izquierdo" => [
                ["frecuencia_hz" => 250, "umbral_db" => 65],
                ["frecuencia_hz" => 500, "umbral_db" => 70],
                ["frecuencia_hz" => 750, "umbral_db" => 70],
                ["frecuencia_hz" => 1500, "umbral_db" => 75],
                ["frecuencia_hz" => 2000, "umbral_db" => 80],
                ["frecuencia_hz" => 3000, "umbral_db" => 85],
                ["frecuencia_hz" => 4000, "umbral_db" => 90]
            ]
        ];

        PatientSession::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'type' => 'doctor',
                'audiometry_data' => $audiometryData,
                'metadata' => [
                    "initiated_by" => $doctorUser->id,
                    "interface" => "audiogram-canvas-v1",
                    "timestamp" => "2026-04-15 16:01:27"
                ]
            ]
        );

        $leftLayers = [
            ["id"=>"ranita","name"=>"Tono Agudo","desc"=>"pulsante rítmico","type"=>"pulse","freq"=>"56","vol"=>"100","speed"=>52,"color"=>"#1D9E75","clinical_hz"=>1283,"clinical_hz_str"=>"1.3 kHz","clinical_speed_hz"=>2],
            ["id"=>"viento","name"=>"Ruido de Banda Ancha","desc"=>"ruido continuo","type"=>"noise","freq"=>"1","vol"=>"100","speed"=>null,"color"=>"#378ADD","clinical_hz"=>130,"clinical_hz_str"=>"130 Hz","clinical_speed_hz"=>null]
        ];

        TinnitusMapping::updateOrCreate(
            ['patient_id' => $patient->id, 'ear' => 'ambos'],
            [
                'initiated_by' => $doctorUser->id,
                'left_layers_config' => $leftLayers,
                'right_layers_config' => $leftLayers,
                'master_volume' => 1.0,
                'config_version' => 1,
            ]
        );
    }
}
