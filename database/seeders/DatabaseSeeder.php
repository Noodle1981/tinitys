<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Doctor
        $doctor = User::factory()->create([
            'name' => 'Dr. House',
            'email' => 'doctor@example.com',
            'role' => 'doctor',
            'password' => bcrypt('password'),
        ]);

        // 2. Create a Patient User (for refinement)
        $patientUser = User::factory()->create([
            'name' => 'Omar Olivera',
            'email' => 'paciente@example.com',
            'role' => 'patient',
            'password' => bcrypt('password'),
        ]);

        // 3. Create the Patient Profile linking both
        \App\Models\Patient::create([
            'name' => 'Omar Olivera',
            'doctor_id' => $doctor->id,
            'user_id' => $patientUser->id,
        ]);

        // Standard test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
