<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\TinnitusMapping;
use Illuminate\Support\Facades\Hash;

class DemoClinicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        // 3. Crear Ficha Clínica del Paciente
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
                'evolution_time' => 'Crónico',
                'comorbidities' => ["Hipoacusia", "Vértigo", "Obesidad"],
                'address' => 'Carlos Gardel Casa 27 B° Enoe Bravo',
                'city' => 'Santa Lucía',
                'province' => 'San Juan',
                'phone' => '2644533704',
                'noise_exposure' => false,
                'tinnitus_symptom' => true,
                'vertigo_symptom' => false,
            ]
        );

        // 4. Crear Sesión de Audiometría (Datos extraídos de la sesión real)
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
            [
                'patient_id' => $patient->id,
                'created_at' => '2026-04-15 16:01:27'
            ],
            [
                'type' => 'doctor',
                'audiometry_data' => $audiometryData,
                'metadata' => [
                    "initiated_by" => $doctorUser->id,
                    "interface" => "audiogram-canvas-v1",
                    "timestamp" => "2026-04-15 16:01:27"
                ],
                'updated_at' => '2026-04-15 16:01:27'
            ]
        );

        // 5. Crear Mapeo de Tinnitus (Capas analíticas)
        $leftLayers = [
            ["id"=>"ranita","name"=>"Tono Agudo","desc"=>"pulsante rítmico","type"=>"pulse","freq"=>"56","vol"=>"100","speed"=>52,"color"=>"#1D9E75","clinical_hz"=>1283,"clinical_hz_str"=>"1.3 kHz","clinical_speed_hz"=>2],
            ["id"=>"viento","name"=>"Ruido de Banda Ancha","desc"=>"ruido continuo","type"=>"noise","freq"=>"1","vol"=>"100","speed"=>null,"color"=>"#378ADD","clinical_hz"=>130,"clinical_hz_str"=>"130 Hz","clinical_speed_hz"=>null],
            ["id"=>"tono","name"=>"Tono Grave","desc"=>"tono puro constante","type"=>"pure","freq"=>"51","vol"=>"100","speed"=>null,"color"=>"#7F77DD","clinical_hz"=>1042,"clinical_hz_str"=>"1.0 kHz","clinical_speed_hz"=>null],
            ["id"=>"sube","name"=>"Tono Oscilante","desc"=>"oscilante lento","type"=>"sweep","freq"=>"72","vol"=>"100","speed"=>"100","color"=>"#BA7517","clinical_hz"=>2497,"clinical_hz_str"=>"2.5 kHz","clinical_speed_hz"=>12]
        ];

        $rightLayers = [
            ["id"=>"ranita","name"=>"Tono Agudo","desc"=>"pulsante rítmico","type"=>"pulse","freq"=>"74","vol"=>"95","speed"=>52,"color"=>"#1D9E75","clinical_hz"=>2713,"clinical_hz_str"=>"2.7 kHz","clinical_speed_hz"=>2],
            ["id"=>"viento","name"=>"Ruido de Banda Ancha","desc"=>"ruido continuo","type"=>"noise","freq"=>"71","vol"=>"100","speed"=>null,"color"=>"#378ADD","clinical_hz"=>2395,"clinical_hz_str"=>"2.4 kHz","clinical_speed_hz"=>null],
            ["id"=>"tono","name"=>"Tono Grave","desc"=>"tono puro constante","type"=>"pure","freq"=>"69","vol"=>"100","speed"=>null,"color"=>"#7F77DD","clinical_hz"=>2204,"clinical_hz_str"=>"2.2 kHz","clinical_speed_hz"=>null],
            ["id"=>"sube","name"=>"Tono Oscilante","desc"=>"oscilante lento","type"=>"sweep","freq"=>"95","vol"=>"100","speed"=>"100","color"=>"#BA7517","clinical_hz"=>6498,"clinical_hz_str"=>"6.5 kHz","clinical_speed_hz"=>12]
        ];

        TinnitusMapping::updateOrCreate(
            [
                'patient_id' => $patient->id,
                'ear' => 'ambos'
            ],
            [
                'initiated_by' => $doctorUser->id,
                'left_layers_config' => $leftLayers,
                'right_layers_config' => $rightLayers,
                'master_volume' => 1.0,
                'config_version' => 1,
                'created_at' => '2026-04-15 18:11:51',
                'updated_at' => '2026-04-15 18:11:51'
            ]
        );
    }
}
